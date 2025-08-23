document.addEventListener('DOMContentLoaded', () => {
    const planTableBody = document.getElementById('planListBody');
    const filterButtonGroup = document.getElementById('plan-filter-buttons');
    let allPlans = [];

    if (!planTableBody || !filterButtonGroup) {
        return;
    }

  // Inject dynamic CSS styles for stat bars and thumbnails
    const style = document.createElement('style');
    style.textContent = `
    .table - vcenter td { vertical - align: middle; }
    .plan - list - thumbnail - container {
        width: 50px;
        height: 50px;
        border - radius: .375rem;
        overflow: hidden;
        flex - shrink: 0;
        display: flex;
        align - items: center;
        justify - content: center;
    }
    .plan - list - thumbnail {
        width: 100 % ;
        height: 100 % ;
        object - fit: cover;
    }
    .plan - list - placeholder { display:flex; align - items:center; justify - content:center; width:100 % ; height:100 % ; }
    /* Dark mode adjustments for placeholder */
    body.dark - mode .plan - list - placeholder {
        background - color: var(--color - input - bg - dark);
        color: var(--color - text - muted - dark);
    }
    .plan - stat - bars { display: flex; gap: 2px; margin - top: 4px; height: 6px; }
    .stat - bar { width: 100 % ; height: 6px; background: #dee2e6; border - radius: 3px; overflow: hidden; }
    body.dark - mode .stat - bar { background - color: rgba(255, 255, 255, 0.18); }
    `;
    document.head.appendChild(style);

    function createMiniStatBar(value, statName)
    {
        const percent = Math.min((value / 1200) * 100, 100);
        const statColors = {
            speed: 'var(--color-stat-speed)',
            stamina: 'var(--color-stat-stamina)',
            power: 'var(--color-stat-power)',
            guts: 'var(--color-stat-guts)',
            wit: 'var(--color-stat-wit)'
        };
        return `
        < div class = "stat-bar" title = "${statName.charAt(0).toUpperCase() + statName.slice(1)}: ${value}" >
        < div style = "width: ${percent}%; height: 100%; background-color: ${statColors[statName]};" > < / div >
        <  / div >
        `;
    }

    function escapeHtml(unsafe)
    {
        if (unsafe === null || unsafe === undefined) {
            return '';
        }
        return String(unsafe).replace(/[&<>"'`=\/]/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                '=': '&#x3D;'
            }[s];
        });
    }

    function setAttributeSliders(attrs, isInline)
    {
        const containerId = isInline ? 'attributeSlidersContainerInline' : 'attributeSlidersContainer';
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }
        container.innerHTML = '';
        (attrs || []).forEach(attr => {
            const name = (attr.attribute_name || '').toUpperCase();
            const value = Number(attr.value) || 0;
            const grade = attr.grade || '';
            const row = document.createElement('div');
            row.className = 'mb-3';
            row.innerHTML = `
            < label class = "form-label" > ${name} < / label >
            < input type = "number" class = "form-control mb-1" value = "${value}" data - attribute - name = "${name}" /  >
            < div class = "small text-muted" > Grade: ${grade} < / div >
            `;
            container.appendChild(row);
        });
    }

    function setAptitudeGrades(grades, isInline, containerIdDefault = 'aptitudeGradesContainer')
    {
        const containerId = isInline ? (containerIdDefault + 'Inline') : containerIdDefault;
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }
        container.innerHTML = '';
        (grades || []).forEach(g => {
            const el = document.createElement('div');
            el.className = 'col-md-3 mb-2';
            el.innerHTML = ` < div class = "p-2 border rounded" > < strong > ${escapeHtml(g.style || g.distance || g.terrain || '')} < / strong > < div class = "text-muted small" > Grade: ${escapeHtml(g.grade)} < / div > < / div > `;
            container.appendChild(el);
        });
    }

    function populateTable(tbodyId, rows, rowRenderer, isInline)
    {
        const finalId = isInline ? (tbodyId + 'Inline') : tbodyId;
        const tbody = document.getElementById(finalId);
        if (!tbody) {
            return;
        }
        tbody.innerHTML = '';
        (rows || []).forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = rowRenderer(r);
            tbody.appendChild(tr);
        });
    }

    async function hydratePlanSections(planId, isInline = false, planData = {})
    {
        const sectionsToFetch = ['attributes','skills','predictions','goals','terrain_grades','distance_grades','style_grades','turns'];
        const sectionPromises = sectionsToFetch.map(type => {
            return fetch(`${window.APP_API_BASE} / plan_section.php ? type = ${encodeURIComponent(type)} & id = ${encodeURIComponent(planId)}`)
            .then(r => r.json())
            .then(json => ({ type, json }))
            .catch(err => ({ type, error: err }));
        });

        const fetchedSections = await Promise.all(sectionPromises);

        fetchedSections.forEach(section => {
            if (section.error || !section.json) {
                return;
            }
            const type = section.type;
            const payload = section.json;
            if (!payload.success) {
                return;
            }

            switch (type) {
                case 'attributes':
                    setAttributeSliders(payload.attributes || [], isInline);
                  break;
                case 'skills':
                    populateTable('skillsTable', payload.skills || [], (s) => `
                    < td > ${escapeHtml(s.skill_name || s.skill_name)} < / td >
                    < td > ${escapeHtml(s.sp_cost || '')} < / td >
                    < td class = "text-center" > ${(s.acquired === 'yes') ? '✓' : ''} < / td >
                    < td > ${escapeHtml(s.skill_tag || s.tag || '')} < / td >
                    < td > ${escapeHtml(s.notes || '')} < / td >
                    < td > < / td >
                    `, isInline);
                  break;
                case 'predictions':
                    populateTable('predictionsTable', payload.predictions || [], (p) => `
                    < td > ${escapeHtml(p.race_name || '')} < / td >
                    < td > ${escapeHtml(p.venue || '')} < / td >
                    < td > ${escapeHtml(p.ground || '')} < / td >
                    < td > ${escapeHtml(p.distance || '')} < / td >
                    < td > ${escapeHtml(p.track_condition || '')} < / td >
                    < td > ${escapeHtml(p.direction || '')} < / td >
                    < td > ${escapeHtml(p.speed || '')} < / td >
                    < td > ${escapeHtml(p.stamina || '')} < / td >
                    < td > ${escapeHtml(p.power || '')} < / td >
                    < td > ${escapeHtml(p.guts || '')} < / td >
                    < td > ${escapeHtml(p.wit || '')} < / td >
                    < td > ${escapeHtml(p.comment || '')} < / td >
                    < td > < / td >
                    `, isInline);
                  break;
                case 'goals':
                    populateTable('goalsTable', payload.goals || [], (g) => `
                    < td > ${escapeHtml(g.goal || '')} < / td >
                    < td > ${escapeHtml(g.result || '')} < / td >
                    < td > < / td >
                    `, isInline);
                  break;
                case 'terrain_grades':
                    setAptitudeGrades(payload.terrain_grades || [], isInline);
                  break;
                case 'distance_grades':
                    setAptitudeGrades(payload.distance_grades || [], isInline);
                  break;
                case 'style_grades':
                    setAptitudeGrades(payload.style_grades || [], isInline);
                  break;
                case 'turns':
                  // progress chart handles itself when its tab is opened
                  break;
                default:
                  break;
            }
        });

      // Hydrate trainee image preview for modal if planData includes trainee_image_path
        if (!isInline && planData && planData.trainee_image_path) {
            const existingPath = document.getElementById('existingTraineeImagePath');
            const previewImg = document.getElementById('traineeImagePreview');
            const previewContainer = document.getElementById('traineeImagePreviewContainer');
            const clearBtn = document.getElementById('clearTraineeImageBtn');
            if (existingPath) {
                existingPath.value = planData.trainee_image_path || '';
            }
            if (previewImg && planData.trainee_image_path) {
                previewImg.src = planData.trainee_image_path;
                if (previewContainer) {
                    previewContainer.style.display = 'block';
                }
                if (clearBtn) {
                    clearBtn.style.display = 'inline-block';
                }
            }
        }
    }

    function renderPlanTable(plansToRender)
    {
        planTableBody.innerHTML = '';

        if (!plansToRender.length) {
            planTableBody.innerHTML = `
            < tr >
            < td colspan = "5" class = "text-center text-muted p-4" >
            No matching plans found.
            < / td >
            <  / tr > `;
            return;
        }

        plansToRender.forEach(plan => {
            const row = document.createElement('tr');
            const stats = plan.stats || { speed: 0, stamina: 0, power: 0, guts: 0, wit: 0 };
            const statusClass = `bg - ${(plan.status || '').toLowerCase()}`;
            const imageHtml = plan.trainee_image_path
            ? (
            '<div class="plan-list-thumbnail-container">' +
            '<img src="' + plan.trainee_image_path + '" class="plan-list-thumbnail" alt="Trainee">' +
            '</div>'
        )
        : (
          '<div class="plan-list-thumbnail-container">' +
          '<div class="plan-list-placeholder"><i class="bi bi-person-square"></i></div>' +
          '</div>'
        );

        row.innerHTML = `
        < td > ${imageHtml} < / td >
        < td >
          < strong > ${plan.plan_title || 'Untitled Plan'} < / strong >
          < div class = "text-muted small" > ${plan.name || ''} < / div >
          < div class = "plan-stat-bars" >
            < span class = "d-inline-block fw-bold text-muted" style = "color: var(--color-stat-speed) !important;" > SPD < / span >
            ${createMiniStatBar(stats.speed, 'speed')}
            < span class = "d-inline-block fw-bold text-muted" style = "color: var(--color-stat-stamina) !important;" > STM < / span >
            ${createMiniStatBar(stats.stamina, 'stamina')}
            < span class = "d-inline-block fw-bold text-muted" style = "color: var(--color-stat-power) !important;" > PWR < / span >
            ${createMiniStatBar(stats.power, 'power')}
            < span class = "d-inline-block fw-bold text-muted" style = "color: var(--color-stat-guts) !important;" > GTS < / span >
            ${createMiniStatBar(stats.guts, 'guts')}
            < span class = "d-inline-block fw-bold text-muted" style = "color: var(--color-stat-wit) !important;" > WIT < / span >
            ${createMiniStatBar(stats.wit, 'wit')}
          <  / div >
        <  / td >
        < td >
          < span class = "badge ${statusClass} rounded-pill" > ${plan.status || ''} < / span >
        <  / td >
        < td > ${plan.race_name || ''} < / td >
        < td >
          < button class = "btn btn-sm btn-primary edit-btn" data - id = "${plan.id}" aria - label = "${plan.plan_title ? `Edit ${plan.plan_title}` : 'Edit plan'}" title = "Edit" >
            < i class = "bi bi-pencil-square" aria - hidden = "true" > < / i >
            < span class = "visually-hidden" > Edit < / span >
          <  / button >
          < button class = "btn btn-sm btn-secondary view-inline-btn" data - id = "${plan.id}" aria - label = "${plan.plan_title ? `View ${plan.plan_title}` : 'View plan'}" title = "View" >
            < i class = "bi bi-eye" aria - hidden = "true" > < / i >
            < span class = "visually-hidden" > View < / span >
          <  / button >
          < button class = "btn btn-sm btn-danger delete-btn" data - id = "${plan.id}" aria - label = "${plan.plan_title ? `Delete ${plan.plan_title}` : 'Delete plan'}" title = "Delete" >
            < i class = "bi bi-trash" aria - hidden = "true" > < / i >
            < span class = "visually-hidden" > Delete < / span >
          <  / button >
        <  / td >
        `;

        planTableBody.appendChild(row);
        });
    }

    async function loadPlans()
    {
        try {
            const res = await fetch(`${window.APP_API_BASE}plan.php?action=list`);
            const result = await res.json();

            if (result.success) {
                allPlans = result.plans;
                renderPlanTable(allPlans);
            } else {
                throw new Error(result.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Failed to load plans:', error);
            planTableBody.innerHTML = `
            < tr >
            < td colspan = "5" class = "text-center text-danger p-4" >
            Error loading plans.
            < / td >
            <  / tr > `;
        }
    }

    filterButtonGroup.addEventListener('click', e => {
        if (e.target.matches('button[data-filter]')) {
            const filter = e.target.dataset.filter;
            filterButtonGroup.querySelector('.active')?.classList.remove('active');
            e.target.classList.add('active');

            const filtered = filter === 'all'
            ? allPlans
            : allPlans.filter(p => p.status === filter);

            renderPlanTable(filtered);
        }
    });

  document.addEventListener('planUpdated', loadPlans);

  // Delegated handlers for plan action buttons (Edit, View, Delete)
  planTableBody.addEventListener('click', async(e) => {
        const editBtn = e.target.closest('.edit-btn');
        const viewBtn = e.target.closest('.view-inline-btn');
        const deleteBtn = e.target.closest('.delete-btn');

        const appRoot = (window.APP_API_BASE || '').replace(/\/api\/?$/, '') || '';

        if (editBtn || viewBtn) {
            const btn = editBtn || viewBtn;
            const planId = btn.dataset.id;
            if (!planId) {
                return;
            }

            const modalEl = document.getElementById('planDetailsModal');
            const loadingOverlay = document.getElementById('planDetailsLoadingOverlay');
            if (!modalEl) {
                console.warn('planDetailsModal not found in DOM.');
                alert('Plan details modal is not loaded on this page.');
                return;
            }
            const saveBtn = document.getElementById('savePlanBtn');

          // Spinner
            const origBtnHtml = btn.innerHTML;
            try {
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'flex';
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                const res = await fetch(`${window.APP_API_BASE}plan.php?action=get&id=${encodeURIComponent(planId)}`);
                const result = await res.json();
                if (!result.success) {
                    throw new Error(result.error || 'Failed to fetch plan');
                }

                const plan = result.plan || {};

                if (viewBtn) {
                    const inlineEl = document.getElementById('planInlineDetails');
                    if (inlineEl) {
                        (document.getElementById('plan_title_inline') || {}).value = plan.plan_title || '';
                        (document.getElementById('planIdInline') || {}).value = plan.id || '';
                        (document.getElementById('modalName_inline') || {}).value = plan.name || '';
                        (document.getElementById('modalRaceName_inline') || {}).value = plan.race_name || '';
                        if (document.getElementById('modalCareerStage_inline')) {
                            document.getElementById('modalCareerStage_inline').value = plan.career_stage || '';
                        }
                        if (document.getElementById('modalClass_inline')) {
                            document.getElementById('modalClass_inline').value = plan.class || '';
                        }
                        if (document.getElementById('modalStatus_inline')) {
                            document.getElementById('modalStatus_inline').value = plan.status || '';
                        }
                        if (document.getElementById('modalStrategy_inline')) {
                            document.getElementById('modalStrategy_inline').value = plan.strategy_id || '';
                        }
                        if (document.getElementById('modalMood_inline')) {
                            document.getElementById('modalMood_inline').value = plan.mood_id || '';
                        }
                        if (document.getElementById('modalCondition_inline')) {
                            document.getElementById('modalCondition_inline').value = plan.condition_id || '';
                        }
                        if (document.getElementById('modalGoal_inline')) {
                            document.getElementById('modalGoal_inline').value = plan.goal || '';
                        }
                        if (document.getElementById('modalSource_inline')) {
                            document.getElementById('modalSource_inline').value = plan.source || '';
                        }
                        if (document.getElementById('skillPoints_inline')) {
                            document.getElementById('skillPoints_inline').value = plan.total_available_skill_points || 0;
                        }
                        if (document.getElementById('acquireSkillSwitch_inline')) {
                            document.getElementById('acquireSkillSwitch_inline').checked = (plan.acquire_skill === 'YES');
                        }
                        if (document.getElementById('raceDaySwitch_inline')) {
                            document.getElementById('raceDaySwitch_inline').checked = (plan.race_day === 'yes');
                        }

                        const saveInline = document.getElementById('savePlanInlineBtn');
                        if (saveInline) {
                            saveInline.disabled = true;
                        }

                    // Hydrate trainee image preview for inline
                        const existingPathInline = document.getElementById('existingTraineeImagePath_inline');
                        const previewImgInline = document.getElementById('traineeImagePreview_inline');
                        const previewContainerInline = document.getElementById('traineeImagePreviewContainer_inline');
                        const clearBtnInline = document.getElementById('clearTraineeImageBtn_inline');
                        if (existingPathInline) {
                            existingPathInline.value = plan.trainee_image_path || '';
                        }
                        if (previewImgInline && plan.trainee_image_path) {
                            previewImgInline.src = plan.trainee_image_path;
                            if (previewContainerInline) {
                                previewContainerInline.style.display = 'block';
                            }
                            if (clearBtnInline) {
                                clearBtnInline.style.display = 'inline-block';
                            }
                        }

                        inlineEl.style.display = 'block';
                        inlineEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

                        await hydratePlanSections(planId, true, plan);
                        return;
                    }
                }

          // Populate primary modal fields
                (document.getElementById('plan_title') || {}).value = plan.plan_title || '';
                (document.getElementById('planId') || {}).value = plan.id || '';
                (document.getElementById('modalName') || {}).value = plan.name || '';
                (document.getElementById('modalRaceName') || {}).value = plan.race_name || '';
                if (document.getElementById('modalCareerStage')) {
                    document.getElementById('modalCareerStage').value = plan.career_stage || '';
                }
                if (document.getElementById('modalClass')) {
                    document.getElementById('modalClass').value = plan.class || '';
                }
                if (document.getElementById('modalStatus')) {
                    document.getElementById('modalStatus').value = plan.status || '';
                }
                if (document.getElementById('modalStrategy')) {
                    document.getElementById('modalStrategy').value = plan.strategy_id || '';
                }
                if (document.getElementById('modalMood')) {
                    document.getElementById('modalMood').value = plan.mood_id || '';
                }
                if (document.getElementById('modalCondition')) {
                    document.getElementById('modalCondition').value = plan.condition_id || '';
                }
                if (document.getElementById('modalGoal')) {
                    document.getElementById('modalGoal').value = plan.goal || '';
                }
                if (document.getElementById('modalSource')) {
                    document.getElementById('modalSource').value = plan.source || '';
                }
                if (document.getElementById('modalMonth')) {
                    document.getElementById('modalMonth').value = plan.month || '';
                }
                if (document.getElementById('modalTimeOfDay')) {
                    document.getElementById('modalTimeOfDay').value = plan.time_of_day || '';
                }
                if (document.getElementById('skillPoints')) {
                    document.getElementById('skillPoints').value = plan.total_available_skill_points || 0;
                }
                if (document.getElementById('acquireSkillSwitch')) {
                    document.getElementById('acquireSkillSwitch').checked = (plan.acquire_skill === 'YES');
                }
                if (document.getElementById('raceDaySwitch')) {
                    document.getElementById('raceDaySwitch').checked = (plan.race_day === 'yes');
                }
                if (document.getElementById('energyRange')) {
                    document.getElementById('energyRange').value = plan.energy || 0;
                    const ev = document.getElementById('energyValue'); if (ev) {
                        ev.textContent = plan.energy || 0;
                    }
                }

          // hydrate child sections
                await hydratePlanSections(planId, false, plan);

          // If this was a View action, make modal read-only
                if (viewBtn) {
                    if (saveBtn) {
                        saveBtn.disabled = true;
                    }
                } else {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                    }
                }

          // Hydrate trainee image preview for modal (if present)
                if (plan.trainee_image_path) {
                    const existingPath = document.getElementById('existingTraineeImagePath');
                    const previewImg = document.getElementById('traineeImagePreview');
                    const previewContainer = document.getElementById('traineeImagePreviewContainer');
                    const clearBtn = document.getElementById('clearTraineeImageBtn');
                    if (existingPath) {
                        existingPath.value = plan.trainee_image_path || '';
                    }
                    if (previewImg) {
                        previewImg.src = plan.trainee_image_path;
                        if (previewContainer) {
                            previewContainer.style.display = 'block';
                        }
                        if (clearBtn) {
                            clearBtn.style.display = 'inline-block';
                        }
                    }
                }

          // Show modal via Bootstrap's modal API
                try {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } catch (err) {
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                }
            } catch (err) {
                console.error('Could not open plan details:', err);
                alert('Failed to load plan details. See console for details.');
            } finally {
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'none';
                }
                btn.disabled = false;
                btn.innerHTML = origBtnHtml;
            }
        }

        if (deleteBtn) {
            const planId = deleteBtn.dataset.id;
            if (!planId) {
                return;
            }
            const confirmed = confirm('Delete this plan? This action can be undone from the activity log.');
            if (!confirmed) {
                return;
            }

            try {
                const form = new FormData();
                form.append('delete_id', planId);

                const crudUrl = (appRoot || '') + '/handle_plan_crud.php';
                const res = await fetch(crudUrl, { method: 'POST', body: form });
                const result = await res.json();
                if (result.success) {
                    document.dispatchEvent(new CustomEvent('planUpdated'));
                    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                        let toastWrap = document.getElementById('toastContainer');
                        if (!toastWrap) {
                              toastWrap = document.createElement('div');
                              toastWrap.id = 'toastContainer';
                              toastWrap.style.position = 'fixed';
                              toastWrap.style.right = '1rem';
                              toastWrap.style.top = '1rem';
                              document.body.appendChild(toastWrap);
                        }
                        const toastEl = document.createElement('div');
                        toastEl.className = 'toast align-items-center text-bg-success border-0';
                        toastEl.setAttribute('role', 'status');
                        toastEl.setAttribute('aria-live', 'polite');
                        toastEl.innerHTML = ` < div class = "d-flex" > < div class = "toast-body" > Plan deleted.< / div > < button type = "button" class = "btn-close btn-close-white me-2 m-auto" data - bs - dismiss = "toast" aria - label = "Close" > < / button > < / div > `;
                        toastWrap.appendChild(toastEl);
                        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
                        toast.show();
                    } else {
                        alert('Plan deleted.');
                    }
                } else {
                    throw new Error(result.error || 'Delete failed');
                }
            } catch (err) {
                console.error('Failed to delete plan:', err);
                alert('Could not delete plan. See console for details.');
            }
            return;
        }

    // Save plan handler (modal)
        const saveBtn = e.target.closest('#savePlanBtn');
        if (saveBtn) {
            const planForm = document.getElementById('planDetailsForm');
            if (!planForm) {
                return;
            }
            const formData = new FormData(planForm);
          // Add planId explicitly if not present
            if (!formData.has('planId')) {
                const planIdInput = document.getElementById('planId');
                if (planIdInput) {
                    formData.append('planId', planIdInput.value);
                }
            }
          // Add image file if present
            const imageInput = document.getElementById('traineeImageUpload');
            if (imageInput && imageInput.files.length > 0) {
                formData.append('traineeImageUpload', imageInput.files[0]);
            }
            fetch(`${window.APP_API_BASE}plan.php?action=update`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    document.dispatchEvent(new CustomEvent('planUpdated'));
                    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                        let toastWrap = document.getElementById('toastContainer');
                        if (!toastWrap) {
                            toastWrap = document.createElement('div');
                            toastWrap.id = 'toastContainer';
                            toastWrap.style.position = 'fixed';
                            toastWrap.style.right = '1rem';
                            toastWrap.style.top = '1rem';
                            document.body.appendChild(toastWrap);
                        }
                        const toastEl = document.createElement('div');
                        toastEl.className = 'toast align-items-center text-bg-success border-0';
                        toastEl.setAttribute('role', 'status');
                        toastEl.setAttribute('aria-live', 'polite');
                        toastEl.innerHTML = ` < div class = "d-flex" > < div class = "toast-body" > Plan updated.< / div > < button type = "button" class = "btn-close btn-close-white me-2 m-auto" data - bs - dismiss = "toast" aria - label = "Close" > < / button > < / div > `;
                        toastWrap.appendChild(toastEl);
                        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
                        toast.show();
                    } else {
                        alert('Plan updated.');
                    }
                // Hide modal
                    const modalEl = document.getElementById('planDetailsModal');
                    if (modalEl) {
                        try {
                            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            modal.hide();
                        } catch (err) {
                            modalEl.classList.remove('show');
                            modalEl.style.display = 'none';
                        }
                    }
                } else {
                    throw new Error(result.error || 'Update failed');
                }
            })
            .catch(err => {
                console.error('Failed to update plan:', err);
                alert('Could not update plan. See console for details.');
            });
            return;
        }
    });

  loadPlans();
});