<?php

/**
 * components/copy_to_clipboard.php
 *
 * 📋 Generates a formatted plain-text version of a training plan and copies it to clipboard.
 * Includes general info, attributes, grades, summary, skills, goals, and predictions.
 *
 * Uses:
 * - TxtBuilder: modular utility for text-based tables
 * - Clipboard API with fallback error handling
 *
 * Injected server-side:
 * - $moodOptions, $strategyOptions, $conditionOptions
 *
 * Author: extremerazr
 * Updated: August 16, 2025
 */

?>

<script>
// TxtBuilder: Utility for creating formatted text tables.
// pad: Pads a string to a specific length, with alignment options.
// buildTable: Builds a text table from headers and rows, with optional per-column alignment.
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

    // Returns a formatted table as plain text.
    buildTable: function(headers, rows, columnConfigs = []) {
        if (rows.length === 0) {
            const headerRow = `| ${headers.map(h => this.pad(h, h.length)).join(' | ')} |`;
            const dividerRow = `|${headers.map(h => '-'.repeat(h.length + 2)).join('|')}|`;
            return [headerRow, dividerRow, '| ' + this.pad('No data.', headerRow.length - 4, 'center') + ' |\n'].join('\n');
        }

        // Calculate column widths (max of header/row cell for each column)
        const colWidths = headers.map((h, i) => Math.max(h.length, ...rows.map(r => String(r[i] || '').length)));

        // Build each row with proper padding/alignment
        const buildRow = (items) => {
            return `| ${items.map((item, i) => this.pad(item, colWidths[i], columnConfigs[i]?.align || 'left')).join(' | ')} |`;
        };

        const headerRow = buildRow(headers);
        const dividerRow = `|${colWidths.map(w => '-'.repeat(w + 2)).join('|')}|`;
        const dataRows = rows.map(r => buildRow(r)).join('\n');

        return [headerRow, dividerRow, dataRows].join('\n');
    }
};

// Main function to copy plan details to clipboard.
function copyPlanDetailsToClipboard(allFetchedData) {
    const plan = allFetchedData.plan || {};
    const attributesData = allFetchedData.attributes || [];
    const skillsData = allFetchedData.skills || [];
    const predictionsData = allFetchedData.predictions || [];
    const goalsData = allFetchedData.goals || [];
    const terrainGradesData = allFetchedData.terrain_grades || [];
    const distanceGradesData = allFetchedData.distance_grades || [];
    const styleGradesData = allFetchedData.style_grades || [];

    // Server-side injected options for human-readable labels
    const moodOptions = <?= json_encode($moodOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const strategyOptions = <?= json_encode($strategyOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const conditionOptions = <?= json_encode($conditionOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // Lookup labels from options arrays
    const moodLabel = moodOptions.find(opt => opt.id == plan.mood_id)?.label || 'N/A';
    const strategyLabel = strategyOptions.find(opt => opt.id == plan.strategy_id)?.label || 'N/A';
    const conditionLabel = conditionOptions.find(opt => opt.id == plan.condition_id)?.label || 'N/A';

    let output = '';
    const sectionDivider = '\n' + '='.repeat(80) + '\n\n';

    // --- GENERAL INFO ---
    output += `## PLAN: ${plan.plan_title || 'Untitled Plan'} ##\n\n`;
    const generalInfo = [
        ['Trainee Name:', plan.name || ''],
        ['Career Stage:', `${(plan.career_stage || '').toUpperCase()} (${(plan.month || '').toUpperCase()} ${(plan.time_of_day || '').toUpperCase()})`],
        ['Class:', (plan.class || '').toUpperCase()],
        ['Status:', plan.status || 'Planning'],
        ['Next Race:', plan.race_name || 'N/A'],
        ['Turn Before:', plan.turn_before || 0],
    ];
    const maxKeyLength = Math.max(...generalInfo.map(row => row[0].length));
    generalInfo.forEach(row => {
        output += `${TxtBuilder.pad(row[0], maxKeyLength)} ${row[1]}\n`;
    });

    // --- ATTRIBUTES ---
    output += sectionDivider;
    const formattedAttributes = attributesData.map(attr => [
        attr.attribute_name
            ? attr.attribute_name.charAt(0).toUpperCase() + attr.attribute_name.slice(1).toLowerCase()
            : '',
        attr.value,
        attr.grade
    ]);
    output += 'ATTRIBUTES\n' + TxtBuilder.buildTable(
        ['Attribute', 'Value', 'Grade'],
        formattedAttributes,
        [{ align: 'left' }, { align: 'right' }, { align: 'center' }]
    ) + '\n\n';

    // --- GRADES ---
    const defaultGradeKeys = {
        terrain: ['Turf', 'Dirt'],
        distance: ['Sprint', 'Mile', 'Medium', 'Long'],
        style: ['Front', 'Pace', 'Late', 'End']
    };
    const terrainMap = new Map(terrainGradesData.map(item => [item.terrain, item.grade]));
    const distanceMap = new Map(distanceGradesData.map(item => [item.distance, item.grade]));
    const styleMap = new Map(styleGradesData.map(item => [item.style, item.grade]));

    // Build rows for grades table. If key is missing, fallback to 'G'.
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

    // --- SUMMARY & GROWTH ---
    output += sectionDivider;
    const summary = [
        ['Total SP', plan.total_available_skill_points || 0],
        ['Acquire Skill?', (plan.acquire_skill || 'NO').toUpperCase()],
        ['Conditions', conditionLabel],
        ['Mood', moodLabel],
        ['Energy', `${plan.energy || 0} / 100`],
        ['Race Day?', (plan.race_day || 'no').toUpperCase()],
        ['Goal', plan.goal || ''],
        ['Strategy', strategyLabel],
        ['---', '---'],
        ['Growth: Speed', `+${plan.growth_rate_speed || 0}%`],
        ['Growth: Stamina', `+${plan.growth_rate_stamina || 0}%`],
        ['Growth: Power', `+${plan.growth_rate_power || 0}%`],
        ['Growth: Guts', `+${plan.growth_rate_guts || 0}%`],
        ['Growth: Wit', `+${plan.growth_rate_wit || 0}%`],
    ];
    output += 'SUMMARY & GROWTH\n' + TxtBuilder.buildTable(
        ['Item', 'Value'],
        summary,
        [{ align: 'left' }, { align: 'right' }]
    ) + '\n';

    // --- SKILLS ---
    output += sectionDivider;
    output += 'ACQUIRED SKILLS\n';
    output += TxtBuilder.buildTable(
        ['Skill Name', 'SP Cost', 'Acquired', 'Notes'],
        skillsData.map(skill => [
            skill.skill_name || '',
            typeof skill.sp_cost === 'number' || /^\d+$/.test(skill.sp_cost) ? skill.sp_cost : 'N/A',
            (skill.acquired || '').toLowerCase() === 'yes' ? '✅' : '❌',
            skill.notes || ''
        ]),
        [{ align: 'left' }, { align: 'right' }, { align: 'center' }, { align: 'left' }]
    ) + '\n';

    // --- GOALS ---
    output += sectionDivider + 'CAREER GOALS\n' + '-'.repeat(80) + '\n';
    output += goalsData.length > 0
        ? goalsData.map(g => `• ${g.goal}${g.result && g.result !== 'Pending' ? ` (Result: ${g.result})` : ''}`).join('\n') + '\n'
        : 'No goals specified.\n';

    // --- RACE PREDICTIONS ---
    output += sectionDivider + 'RACE DAY PREDICTIONS\n' + '-'.repeat(80) + '\n';
    if (predictionsData.length > 0) {
        predictionsData.forEach((p, i) => {
            output += `Prediction #${i + 1}: ${p.race_name || 'N/A'}\n`;
            output += `  Venue: ${p.venue}, ${p.ground}, ${p.distance}, ${p.direction}, Track: ${p.track_condition}\n`;
            output += `  SPEED[${p.speed}] STAMINA[${p.stamina}] POWER[${p.power}] GUTS[${p.guts}] WIT[${p.wit}]\n`;
            output += `  Comment: ${p.comment || 'N/A'}\n\n`;
        });
    } else {
        output += 'No race predictions available.\n';
    }

    // --- COPY TO CLIPBOARD ---
    // Uses Clipboard API if available, otherwise fallback to execCommand method for older browsers.
    try {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(output.trim()).then(() => {
                showMessageBox('Formatted plan copied to clipboard!', 'success');
            }).catch(err => {
                console.error('Clipboard API Error:', err);
                showMessageBox('Failed to copy to clipboard. Check browser permissions.', 'danger');
            });
        } else {
            // Fallback for older browsers (not recommended, but available)
            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = output.trim();
            tempTextArea.setAttribute('readonly', '');
            tempTextArea.style.position = 'absolute';
            tempTextArea.style.left = '-9999px';
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            try {
                document.execCommand('copy');
                showMessageBox('Formatted plan copied to clipboard!', 'success');
            } catch (e) {
                console.error('Unexpected copy error:', e);
                showMessageBox('Unexpected error occurred during copy.', 'danger');
            }
            document.body.removeChild(tempTextArea);
        }
    } catch (e) {
        console.error('Unexpected copy error:', e);
        showMessageBox('Unexpected error occurred during copy.', 'danger');
    }
}
</script>
