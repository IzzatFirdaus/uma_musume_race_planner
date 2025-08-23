/* eslint-env browser */
(() => {
    'use strict';

    const injected = (function () {
        try {
            const el = document.getElementById('copy-to-clipboard-options');
            return el ? JSON.parse(el.textContent || '{}') : {};
        } catch (_) {
            return {}; }
    })();

  const TxtBuilder = {
        pad(str, length, align = 'left') {
            str = String(str ?? '');
            const diff = length - str.length;
            if (diff <= 0) {
                return str;
            }
            if (align === 'right') {
                return ' '.repeat(diff) + str;
            }
            if (align === 'center') {
                const left = Math.floor(diff / 2);
                const right = diff - left;
                return ' '.repeat(left) + str + ' '.repeat(right);
            }
            return str + ' '.repeat(diff);
        },
        buildTable(headers, rows, columnConfigs = []) {
            const safeHeaders = Array.isArray(headers) ? headers : [];
            const safeRows = Array.isArray(rows) ? rows : [];
            if (safeRows.length === 0) {
                const headerRow = ` | ${safeHeaders.map(h => this.pad(String(h), String(h).length)).join(' | ')} | `;
                const dividerRow = ` | ${safeHeaders.map(h => '-'.repeat(String(h).length + 2)).join('|')} | `;
                const noDataRow = '| ' + this.pad('No data.', Math.max(0, headerRow.length - 4), 'center') + ' |';
                return [headerRow, dividerRow, noDataRow].join('\n');
            }
            const colWidths = safeHeaders.map((h, i) => Math.max(
                String(h ?? '').length,
                ...safeRows.map(r => String((r?.[i]) ?? '').length)
            ));
          const buildRow = (items) => {
                const cells = safeHeaders.map((_, i) => {
                    const cfg = columnConfigs?.[i] || {};
                    const align = cfg.align || 'left';
                    const cell = items?.[i] ?? '';
                    return this.pad(String(cell), colWidths[i], align);
                });
            return ` | ${cells.join(' | ')} | `;
            };
            const headerRow = buildRow(safeHeaders);
            const dividerRow = ` | ${colWidths.map(w => '-'.repeat(w + 2)).join('|')} | `;
            const dataRows = safeRows.map(r => buildRow(r)).join('\n');
            return [headerRow, dividerRow, dataRows].join('\n');
        }
    };

    function copyPlanDetailsToClipboard(allFetchedData)
    {
    const plan = allFetchedData?.plan || {};
    const attributesData = Array.isArray(allFetchedData?.attributes) ? allFetchedData.attributes : [];
    const skillsData = Array.isArray(allFetchedData?.skills) ? allFetchedData.skills : [];
    const predictionsData = Array.isArray(allFetchedData?.predictions) ? allFetchedData.predictions : [];
    const goalsData = Array.isArray(allFetchedData?.goals) ? allFetchedData.goals : [];
    const terrainGradesData = Array.isArray(allFetchedData?.terrain_grades) ? allFetchedData.terrain_grades : [];
    const distanceGradesData = Array.isArray(allFetchedData?.distance_grades) ? allFetchedData.distance_grades : [];
    const styleGradesData = Array.isArray(allFetchedData?.style_grades) ? allFetchedData.style_grades : [];

        const moodOptions = injected.moodOptions || [];
        const strategyOptions = injected.strategyOptions || [];
        const conditionOptions = injected.conditionOptions || [];

        const moodLabel = (moodOptions.find(opt => String(opt.id) === String(plan.mood_id)) || {}).label || 'N/A';
        const strategyLabel = (strategyOptions.find(opt => String(opt.id) === String(plan.strategy_id)) || {}).label || 'N/A';
        const conditionLabel = (conditionOptions.find(opt => String(opt.id) === String(plan.condition_id)) || {}).label || 'N/A';

        let output = '';
        const sectionDivider = '\n' + '='.repeat(80) + '\n\n';

        const planTitle = String(plan.plan_title || 'Untitled Plan').trim();
        output += `## PLAN: ${planTitle} ##\n\n`;

        const upper = (v) => String(v || '').toUpperCase();
        const generalInfo = [
        ['Trainee Name:', plan.name || ''],
        ['Career Stage:', `${upper(plan.career_stage)} (${upper(plan.month)} ${upper(plan.time_of_day)})`.trim()],
        ['Class:', upper(plan.class)],
        ['Status:', plan.status || 'Planning'],
        ['Next Race:', plan.race_name || 'N/A'],
        ['Turn Before:', Number.isFinite(Number(plan.turn_before)) ? Number(plan.turn_before) : 0],
        ];
        const maxKeyLength = Math.max(...generalInfo.map(row => String(row[0]).length));
        generalInfo.forEach(row => { output += `${TxtBuilder.pad(row[0], maxKeyLength)} ${row[1]}\n`; });

      // Attributes
        output += sectionDivider;
    const formattedAttributes = attributesData.map(attr => [
    attr?.attribute_name ? (String(attr.attribute_name).charAt(0).toUpperCase() + String(attr.attribute_name).slice(1).toLowerCase()) : '',
    attr?.value ?? '',
    attr?.grade ?? ''
    ]);
        output += 'ATTRIBUTES\n' + TxtBuilder.buildTable(
            ['Attribute', 'Value', 'Grade'],
            formattedAttributes,
            [{ align: 'left' }, { align: 'right' }, { align: 'center' }]
        ) + '\n\n';

      // Grades
        const defaultGradeKeys = {
            terrain: ['Turf', 'Dirt'],
            distance: ['Sprint', 'Mile', 'Medium', 'Long'],
            style: ['Front', 'Pace', 'Late', 'End']
        };
    const terrainMap = new Map(terrainGradesData.map(item => [item?.terrain, item?.grade]));
    const distanceMap = new Map(distanceGradesData.map(item => [item?.distance, item?.grade]));
    const styleMap = new Map(styleGradesData.map(item => [item?.style, item?.grade]));
        const gradeRows = defaultGradeKeys.distance.map((key, i) => [
        key, distanceMap.get(key) || 'G',
        defaultGradeKeys.style[i], styleMap.get(defaultGradeKeys.style[i]) || 'G',
        defaultGradeKeys.terrain[i] || '', terrainMap.get(defaultGradeKeys.terrain[i]) || 'G'
        ]);
        output += 'APTITUDE GRADES\n' + TxtBuilder.buildTable(
            ['Distance', 'G', 'Style', 'G', 'Terrain', 'G'],
            gradeRows,
            [{ align: 'left' }, { align: 'center' }, { align: 'left' }, { align: 'center' }, { align: 'left' }, { align: 'center' }]
        ) + '\n';

      // Summary
        output += sectionDivider;
        const summary = [
        ['Total SP', Number(plan.total_available_skill_points) || 0],
        ['Acquire Skill?', upper(plan.acquire_skill || 'NO')],
        ['Conditions', conditionLabel],
        ['Mood', moodLabel],
        ['Energy', `${Number(plan.energy) || 0} / 100`],
        ['Race Day?', upper(plan.race_day || 'no')],
        ['Goal', plan.goal || ''],
        ['Strategy', strategyLabel],
        ['---', '---'],
        ['Growth: Speed', ` + ${Number(plan.growth_rate_speed) || 0} % `],
        ['Growth: Stamina', ` + ${Number(plan.growth_rate_stamina) || 0} % `],
        ['Growth: Power', ` + ${Number(plan.growth_rate_power) || 0} % `],
        ['Growth: Guts', ` + ${Number(plan.growth_rate_guts) || 0} % `],
        ['Growth: Wit', ` + ${Number(plan.growth_rate_wit) || 0} % `],
        ];
        output += 'SUMMARY & GROWTH\n' + TxtBuilder.buildTable(
            ['Item', 'Value'],
            summary,
            [{ align: 'left' }, { align: 'right' }]
        ) + '\n';

      // Skills
        output += sectionDivider;
        output += 'ACQUIRED SKILLS\n';
        output += TxtBuilder.buildTable(
            ['Skill Name', 'SP Cost', 'Acquired', 'Notes'],
            skillsData.map(skill => {
                const sp = (typeof skill?.sp_cost === 'number' || /^\d+$/.test(String(skill?.sp_cost))) ? skill.sp_cost : 'N/A';
                const acquired = (String(skill?.acquired || '').toLowerCase() === 'yes' || skill?.acquired === true) ? '✅' : '❌';
                return [
                skill?.skill_name || '',
                sp,
                acquired,
                skill?.notes || ''
                ];
            }),
            [{ align: 'left' }, { align: 'right' }, { align: 'center' }, { align: 'left' }]
        ) + '\n';

      // Goals
        output += sectionDivider + 'CAREER GOALS\n' + '-'.repeat(80) + '\n';
    output += goalsData.length > 0
    ? goalsData.map(g => ` ${g?.goal || ''}${(g?.result && g?.result !== 'Pending') ? `(Result : ${g.result})` : ''}`).join('\n') + '\n'
    : 'No goals specified.\n';

      // Predictions
        output += sectionDivider + 'RACE DAY PREDICTIONS\n' + '-'.repeat(80) + '\n';
        if (predictionsData.length > 0) {
            predictionsData.forEach((p, i) => {
                output += `Prediction #${i + 1}: ${p?.race_name || 'N/A'}\n`;
                output += `  Venue: ${p?.venue || 'N/A'}, ${p?.ground || 'N/A'}, ${p?.distance || 'N/A'}, ${p?.direction || 'N/A'}, Track : ${p?.track_condition || 'N/A'}\n`;
                output += `  SPEED[${p?.speed ?? '0'}] STAMINA[${p?.stamina ?? '0'}] POWER[${p?.power ?? '0'}] GUTS[${p?.guts ?? '0'}] WIT[${p?.wit ?? '0'}]\n`;
                output += `  Comment: ${p?.comment || 'N/A'}\n\n`;
            });
        } else {
            output += 'No race predictions available.\n';
        }

        const message = (text, type = 'info') => {
            if (typeof window.showMessageBox === 'function') {
                window.showMessageBox(text, type);
            } else {
                try {
                    console.log(`[${type}] ${text}`); } catch (_) {
                    }
                    try {
                        alert(text); } catch (_) {
                        }
            }
        };

        try {
            const content = output.trim();
            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function' && window.isSecureContext) {
                navigator.clipboard.writeText(content)
                .then(() => message('Formatted plan copied to clipboard!', 'success'))
                .catch(err => { console.error('Clipboard API Error:', err); message('Failed to copy to clipboard. Check browser permissions.', 'danger'); });
            } else {
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = content;
                tempTextArea.setAttribute('readonly', '');
                tempTextArea.style.position = 'fixed';
                tempTextArea.style.insetInlineStart = '-9999px';
                tempTextArea.style.top = '0';
                document.body.appendChild(tempTextArea);
                tempTextArea.select();
                try {
                    const ok = document.execCommand && document.execCommand('copy');
                    if (ok) {
                        message('Formatted plan copied to clipboard!', 'success');
                    } else {
                        message('Copy command not supported in this browser.', 'warning');
                    }
                } catch (e) {
                    console.error('Unexpected copy error:', e);
                    message('Unexpected error occurred during copy.', 'danger');
                } finally {
                    document.body.removeChild(tempTextArea);
                }
            }
        } catch (e) {
            console.error('Unexpected copy error:', e);
            message('Unexpected error occurred during copy.', 'danger');
        }
    }

    window.copyPlanDetailsToClipboard = copyPlanDetailsToClipboard;
})();
