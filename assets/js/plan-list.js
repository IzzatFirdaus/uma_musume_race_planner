/* eslint-env browser */
/*
 * assets/js/plan-list.js
 * Clean, canonical implementation mirroring assets/js/plan_list.js behavior.
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const planTableBody = document.getElementById('planListBody');
        const filterButtonGroup = document.getElementById('plan-filter-buttons');
        let allPlans = [];

        if (!planTableBody || !filterButtonGroup) return;

        // Inject minimal styles used by this component
        const style = document.createElement('style');
        style.textContent = '\n' +
            '.table-vcenter td { vertical-align: middle; }\n' +
            '.plan-list-thumbnail-container { width:50px; height:50px; border-radius:.375rem; overflow:hidden; display:flex; align-items:center; justify-content:center; }\n' +
            '.plan-list-thumbnail { width:100%; height:100%; object-fit:cover; display:block; }\n' +
            '.plan-list-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background-color:var(--color-surface-muted); border-radius:.375rem; font-size:1.5rem; color:var(--color-muted); }\n' +
            '.plan-stat-bars { display:flex; gap:6px; margin-top:4px; height:6px; align-items:center; }\n' +
            '.stat-bar { width:100%; height:6px; background:#dee2e6; border-radius:3px; overflow:hidden; }\n';
        document.head.appendChild(style);

        const escapeHTML = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

        function createMiniStatBar(value, statName) {
            const v = Number.isFinite(Number(value)) ? Number(value) : 0;
            const percent = Math.min((v / 1200) * 100, 100);
            const statColors = {
                speed: 'var(--stat-speed-color)',
                stamina: 'var(--stat-stamina-color)',
                power: 'var(--stat-power-color)',
                guts: 'var(--stat-guts-color)',
                wit: 'var(--stat-wit-color)'
            };
            const label = statName.charAt(0).toUpperCase() + statName.slice(1);
            return '<span class="d-inline-block fw-bold text-muted" style="color:' + statColors[statName] + ' !important;">' + label.slice(0, 3).toUpperCase() + '</span>' +
                '<div class="stat-bar" title="' + label + ': ' + v + '"><div style="width:' + percent + '%;height:100%;background-color:' + statColors[statName] + ';"></div></div>';
        }

        function renderPlanTable(plans) {
                        planTableBody.innerHTML = '';
                        if (!plans || !plans.length) {
                                planTableBody.innerHTML = `
                                    <tr>
                                        <td colspan="5" class="text-center p-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-clipboard-x" style="font-size: 3rem; color: var(--color-muted);" aria-hidden="true"></i>
                                                <div class="mt-3 mb-2 fs-4 text-muted">No plans found</div>
                                                <div class="mb-3 text-muted">Get started by creating your first training plan!</div>
                                                <button type="button" class="btn btn-primary" id="emptyStateCreateBtn">Create Plan</button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                // Add event listener for CTA button
                                setTimeout(() => {
                                    const btn = document.getElementById('emptyStateCreateBtn');
                                    if (btn) {
                                        btn.addEventListener('click', () => {
                                            const modalEl = document.getElementById('createPlanModal');
                                            if (modalEl) {
                                                try { new bootstrap.Modal(modalEl).show(); } catch (e) { modalEl.classList.add('show'); modalEl.style.display = 'block'; }
                                            }
                                        });
                                    }
                                }, 0);
                                return;
                        }

            plans.forEach(plan => {
                const tr = document.createElement('tr');
                const title = escapeHTML(plan?.plan_title ?? 'Untitled Plan');
                const name = escapeHTML(plan?.name ?? '');
                const race = escapeHTML(plan?.race_name ?? '');
                const status = escapeHTML(plan?.status ?? '');
                const stats = plan?.stats ?? { speed: 0, stamina: 0, power: 0, guts: 0, wit: 0 };
                const img = plan?.trainee_image_path ? '<div class="plan-list-thumbnail-container"><img src="' + escapeHTML(plan.trainee_image_path) + '" class="plan-list-thumbnail" alt="Trainee"></div>' : '<div class="plan-list-thumbnail-container"><div class="plan-list-placeholder"><i class="bi bi-person-square" aria-hidden="true"></i></div></div>';

                tr.innerHTML = '\n' +
                    '<td>' + img + '</td>' +
                    '<td><strong>' + title + '</strong><div class="text-muted small">' + name + '</div><div class="plan-stat-bars">' +
                    createMiniStatBar(stats.speed, 'speed') + createMiniStatBar(stats.stamina, 'stamina') + createMiniStatBar(stats.power, 'power') + createMiniStatBar(stats.guts, 'guts') + createMiniStatBar(stats.wit, 'wit') +
                    '</div></td>' +
                    '<td><span class="badge bg-' + status.toLowerCase() + ' rounded-pill">' + status + '</span></td>' +
                    '<td>' + race + '</td>' +
                    '<td><button class="btn btn-sm btn-outline-primary edit-btn" data-id="' + escapeHTML(plan.id) + '"><i class="bi bi-pencil-square"></i></button> <button class="btn btn-sm btn-outline-info view-inline-btn" data-id="' + escapeHTML(plan.id) + '"><i class="bi bi-eye"></i></button> <button class="btn btn-sm btn-outline-danger delete-btn" data-id="' + escapeHTML(plan.id) + '"><i class="bi bi-trash"></i></button></td>';

                planTableBody.appendChild(tr);
            });
        }

        async function loadPlans() {
            try {
                const base = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';
                const res = await fetch(base + '/plan.php?action=list', { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json?.success) {
                    allPlans = Array.isArray(json.plans) ? json.plans : [];
                    renderPlanTable(allPlans);
                } else {
                    throw new Error(json?.message || 'Unknown error');
                }
            } catch (err) {
                console.error('Failed to load plans:', err);
                planTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger p-4">Error loading plans.</td></tr>';
            }
        }

        filterButtonGroup.addEventListener('click', e => {
            const btn = e.target.closest('button[data-filter]');
            if (!btn) return;
            const filter = btn.dataset.filter;
            filterButtonGroup.querySelector('.active')?.classList.remove('active');
            btn.classList.add('active');
            const filtered = filter === 'all' ? allPlans : allPlans.filter(p => p?.status === filter);
            renderPlanTable(filtered);
        });

        planTableBody.addEventListener('click', e => {
            const editBtn = e.target.closest('.edit-btn');
            const viewBtn = e.target.closest('.view-inline-btn');
            const deleteBtn = e.target.closest('.delete-btn');

            import('sweetalert2').then(Swal => {
                if (editBtn) {
                    const planId = editBtn.dataset.id;
                    if (planId) {
                        const modalEl = document.getElementById('planDetailsModal');
                        if (modalEl) {
                            loadPlanForEdit(planId).then(() => {
                                try { new bootstrap.Modal(modalEl).show(); } catch (e) { modalEl.classList.add('show'); modalEl.style.display = 'block'; }
                            });
                        }
                    }
                } else if (viewBtn) {
                    const planId = viewBtn.dataset.id;
                    if (planId) document.dispatchEvent(new CustomEvent('showPlanInline', { detail: { planId } }));
                } else if (deleteBtn) {
                    const planId = deleteBtn.dataset.id;
                    if (planId) {
                        Swal.default.fire({
                            title: 'Delete Plan?',
                            text: 'Are you sure you want to delete this plan?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then(result => {
                            if (result.isConfirmed) deletePlan(planId);
                        });
                    }
                }
            });
        });

        async function loadPlanForEdit(planId) {
            try {
                const base = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';
                const response = await fetch(base + '/plan.php?action=get&id=' + encodeURIComponent(planId));
                const result = await response.json();
                if (result.success) {
                    const plan = result.plan || {};
                    const planIdEl = document.getElementById('planId');
                    const titleEl = document.getElementById('plan_title');
                    if (planIdEl) planIdEl.value = plan.id || '';
                    if (titleEl) titleEl.value = plan.plan_title || '';
                    document.dispatchEvent(new CustomEvent('planDataLoaded', { detail: plan }));
                }
            } catch (error) {
                console.error('Failed to load plan for editing:', error);
            }
        }

        async function deletePlan(planId) {
            import('sweetalert2').then(Swal => {
                fetch((window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '' + '/plan.php?action=delete&id=' + encodeURIComponent(planId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        loadPlans();
                        Swal.default.fire({
                            title: 'Deleted!',
                            text: 'Plan deleted successfully.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.default.fire({
                            title: 'Error',
                            text: 'Failed to delete plan: ' + (result.message || 'Unknown error'),
                            icon: 'error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to delete plan:', error);
                    Swal.default.fire({
                        title: 'Error',
                        text: 'Failed to delete plan. Please try again.',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });
        }

        document.addEventListener('planUpdated', loadPlans);

        const createPlanBtn = document.getElementById('createPlanBtn');
        if (createPlanBtn) createPlanBtn.addEventListener('click', () => {
            const modal = document.getElementById('createPlanModal');
            if (modal) try { new bootstrap.Modal(modal).show(); } catch (e) { modal.classList.add('show'); modal.style.display = 'block'; }
        });

        loadPlans();
    });
})();