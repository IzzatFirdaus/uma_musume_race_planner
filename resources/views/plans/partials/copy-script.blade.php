{{--
    Converted from components/copy_to_clipboard.php.
    This partial provides the client-side functionality to generate a formatted
    plain-text summary and copy it to the user's clipboard.
--}}
<script>
    const TxtBuilder = {
        pad: function(str, length, align = 'left') {
            str = String(str);
            const diff = length - str.length;
            if (diff <= 0) return str;
            if (align === 'right') return ' '.repeat(diff) + str;
            if (align === 'center') {
                const left = Math.floor(diff / 2);
                const right = diff - left;
                return ' '.repeat(left) + str + ' '.repeat(right);
            }
            return str + ' '.repeat(diff);
        },
        buildTable: function(headers, rows, columnConfigs = []) {
            if (rows.length === 0) {
                const headerRow = `| ${headers.map(h => this.pad(h, h.length)).join(' | ')} |`;
                const dividerRow = `|${headers.map(h => '-'.repeat(h.length + 2)).join('|')}|`;
                return [headerRow, dividerRow, '| ' + this.pad('No data.', headerRow.length - 4, 'center') + ' |\n'].join('\n');
            }
            const colWidths = headers.map((h, i) => Math.max(h.length, ...rows.map(r => String(r[i] || '').length)));
            const buildRow = (items) => `| ${items.map((item, i) => this.pad(item, colWidths[i], columnConfigs[i]?.align || 'left')).join(' | ')} |`;
            const headerRow = buildRow(headers);
            const dividerRow = `|${colWidths.map(w => '-'.repeat(w + 2)).join('|')}|`;
            const dataRows = rows.map(r => buildRow(r)).join('\n');
            return [headerRow, dividerRow, dataRows].join('\n');
        }
    };

    function copyPlanDetailsToClipboard(allFetchedData) {
        const plan = allFetchedData.plan || {};
        const attributesData = allFetchedData.attributes || [];
        const skillsData = allFetchedData.skills || [];
        const predictionsData = allFetchedData.racePredictions || []; // Corrected key
        const goalsData = allFetchedData.goals || [];
        const terrainGradesData = allFetchedData.terrainGrades || [];
        const distanceGradesData = allFetchedData.distanceGrades || [];
        const styleGradesData = allFetchedData.styleGrades || [];

        // Data is now sourced from the global plannerData object for consistency
        const moodOptions = window.plannerData.moodOptions || [];
        const strategyOptions = window.plannerData.strategyOptions || [];
        const conditionOptions = window.plannerData.conditionOptions || [];

        const moodLabel = moodOptions.find(opt => opt.id == plan.mood_id)?.label || 'N/A';
        const strategyLabel = strategyOptions.find(opt => opt.id == plan.strategy_id)?.label || 'N/A';
        const conditionLabel = conditionOptions.find(opt => opt.id == plan.condition_id)?.label || 'N/A';

        let output = `## PLAN: ${plan.plan_title || 'Untitled Plan'} ##\n\n`;
        const sectionDivider = '\n' + '='.repeat(80) + '\n\n';
        const generalInfo = [['Trainee Name:', plan.name || ''],['Career Stage:', `${(plan.career_stage || '').toUpperCase()} (${(plan.month || '').toUpperCase()} ${(plan.time_of_day || '').toUpperCase()})`],['Class:', (plan.class || '').toUpperCase()],['Status:', plan.status || 'Planning'],['Next Race:', plan.race_name || 'N/A'],['Turn Before:', plan.turn_before || 0]];
        const maxKeyLength = Math.max(...generalInfo.map(row => row[0].length));
        generalInfo.forEach(row => { output += `${TxtBuilder.pad(row[0], maxKeyLength)} ${row[1]}\n`; });

        output += sectionDivider + 'ATTRIBUTES\n' + TxtBuilder.buildTable(['Attribute', 'Value', 'Grade'], attributesData.map(attr => [attr.attribute_name ? attr.attribute_name.charAt(0).toUpperCase() + attr.attribute_name.slice(1).toLowerCase() : '', attr.value, attr.grade]), [{ align: 'left' }, { align: 'right' }, { align: 'center' }]) + '\n\n';

        const defaultGradeKeys = { terrain: ['Turf', 'Dirt'], distance: ['Sprint', 'Mile', 'Medium', 'Long'], style: ['Front', 'Pace', 'Late', 'End'] };
        const terrainMap = new Map(terrainGradesData.map(item => [item.terrain, item.grade]));
        const distanceMap = new Map(distanceGradesData.map(item => [item.distance, item.grade]));
        const styleMap = new Map(styleGradesData.map(item => [item.style, item.grade]));
        const gradeRows = defaultGradeKeys.distance.map((key, i) => [key, distanceMap.get(key) || 'G', defaultGradeKeys.style[i], styleMap.get(defaultGradeKeys.style[i]) || 'G', defaultGradeKeys.terrain[i] || '', terrainMap.get(defaultGradeKeys.terrain[i]) || 'G']);
        output += 'APTITUDE GRADES\n' + TxtBuilder.buildTable(['Distance', 'G', 'Style', 'G', 'Terrain', 'G'], gradeRows, [{ align: 'left' }, { align: 'center' }, { align: 'left' }, { align: 'center' }, { align: 'left' }, { align: 'center' }]) + '\n';

        output += sectionDivider;
        const summary = [['Total SP', plan.total_available_skill_points || 0], ['Acquire Skill?', (plan.acquire_skill || 'NO').toUpperCase()], ['Conditions', conditionLabel], ['Mood', moodLabel], ['Energy', `${plan.energy || 0} / 100`], ['Race Day?', (plan.race_day || 'no').toUpperCase()], ['Goal', plan.goal || ''], ['Strategy', strategyLabel], ['---', '---'], ['Growth: Speed', `+${plan.growth_rate_speed || 0}%`], ['Growth: Stamina', `+${plan.growth_rate_stamina || 0}%`], ['Growth: Power', `+${plan.growth_rate_power || 0}%`], ['Growth: Guts', `+${plan.growth_rate_guts || 0}%`], ['Growth: Wit', `+${plan.growth_rate_wit || 0}%`]];
        output += 'SUMMARY & GROWTH\n' + TxtBuilder.buildTable(['Item', 'Value'], summary, [{ align: 'left' }, { align: 'right' }]) + '\n';

        output += sectionDivider + 'ACQUIRED SKILLS\n';
        output += TxtBuilder.buildTable(['Skill Name', 'SP Cost', 'Acquired', 'Notes'], skillsData.map(skill => [skill.skill_reference?.skill_name || '', typeof skill.sp_cost === 'number' ? skill.sp_cost : 'N/A', (skill.acquired || '').toLowerCase() === 'yes' ? '✅' : '❌', skill.notes || '']), [{ align: 'left' }, { align: 'right' }, { align: 'center' }, { align: 'left' }]) + '\n';

        output += sectionDivider + 'CAREER GOALS\n' + '-'.repeat(80) + '\n';
        output += goalsData.length > 0 ? goalsData.map(g => `• ${g.goal}${g.result && g.result !== 'Pending' ? ` (Result: ${g.result})` : ''}`).join('\n') + '\n' : 'No goals specified.\n';

        output += sectionDivider + 'RACE DAY PREDICTIONS\n' + '-'.repeat(80) + '\n';
        if (predictionsData.length > 0) {
            predictionsData.forEach((p, i) => { output += `Prediction #${i + 1}: ${p.race_name || 'N/A'}\n  Venue: ${p.venue}, ${p.ground}, ${p.distance}, ${p.direction}, Track: ${p.track_condition}\n  SPEED[${p.speed}] STAMINA[${p.stamina}] POWER[${p.power}] GUTS[${p.guts}] WIT[${p.wit}]\n  Comment: ${p.comment || 'N/A'}\n\n`; });
        } else { output += 'No race predictions available.\n'; }

        if (navigator.clipboard) {
            navigator.clipboard.writeText(output.trim()).then(() => showMessageBox('Formatted plan copied to clipboard!', 'success')).catch(err => {
                console.error('Clipboard API Error:', err);
                showMessageBox('Failed to copy. Check browser permissions.', 'danger');
            });
        } else { showMessageBox('Clipboard API not available in this browser.', 'danger'); }
    }
</script>
