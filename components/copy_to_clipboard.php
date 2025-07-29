<?php
// components/copy_to_clipboard.php (Refactored for Better Formatting and Robustness)
?>

<script>
/**
 * A set of helper functions to build nicely formatted plain-text tables.
 */
const TxtBuilder = {
    /**
     * Pads a string with spaces to a certain length.
     * @param {string} str The string to pad.
     * @param {number} length The target length.
     * @param {string} align Alignment ('left', 'right', or 'center').
     * @returns {string} The padded string.
     */
    pad: function(str, length, align = 'left') {
        str = String(str);
        const diff = length - str.length;
        if (diff <= 0) return str;

        if (align === 'right') {
            return ' '.repeat(diff) + str;
        }
        if (align === 'center') {
            const left = Math.floor(diff / 2);
            const right = diff - left;
            return ' '.repeat(left) + str + ' '.repeat(right);
        }
        // Default to left alignment
        return str + ' '.repeat(diff);
    },

    /**
     * Builds a complete plain-text table from headers and rows.
     * @param {string[]} headers - Array of header titles.
     * @param {string[][]} rows - Array of arrays, where each inner array is a row.
     * @param {object[]} columnConfigs - Array of config objects for each column, e.g., [{align: 'left'}, {align: 'right'}].
     * @returns {string} The formatted table as a string.
     */
    buildTable: function(headers, rows, columnConfigs = []) {
        // If no rows, just return headers and divider
        if (rows.length === 0) {
            const headerRow = `| ${headers.map(h => this.pad(h, h.length)).join(' | ')} |`; // Pad headers to their own length
            const dividerRow = `|${headers.map(h => '-'.repeat(h.length + 2)).join('|')}|`; // Divider based on header length
            return [headerRow, dividerRow, '| ' + this.pad('No data.', headerRow.length - 4, 'center') + ' |\n'].join('\n'); // Add "No data" message
        }

        const colWidths = headers.map((h, i) => {
            const rowLengths = rows.map(r => String(r[i] || '').length);
            return Math.max(h.length, ...rowLengths);
        });

        const buildRow = (rowItems) => {
            const paddedItems = rowItems.map((item, i) => {
                const align = columnConfigs[i]?.align || 'left';
                return this.pad(item, colWidths[i], align);
            });
            return `| ${paddedItems.join(' | ')} |`;
        };
        
        const headerRow = buildRow(headers);
        const dividerRow = `|${colWidths.map(w => '-'.repeat(w + 2)).join('|')}|`;
        const dataRows = rows.map(r => buildRow(r)).join('\n');

        return [headerRow, dividerRow, dataRows].join('\n');
    }
};


/**
 * Copies a complete, formatted plan to the clipboard.
 * This function is now independent of the DOM and gets all data from the provided object.
 *
 * @param {object} allFetchedData - The aggregated data object containing the plan, attributes, skills, etc.
 */
function copyPlanDetailsToClipboard(allFetchedData) {
    // --- DATA GATHERING ---
    const plan = allFetchedData.plan || {};
    const attributesData = allFetchedData.attributes || [];
    const skillsData = allFetchedData.skills || [];
    const predictionsData = allFetchedData.predictions || [];
    const goalsData = allFetchedData.goals || [];
    const terrainGradesData = allFetchedData.terrain_grades || [];
    const distanceGradesData = allFetchedData.distance_grades || [];
    const styleGradesData = allFetchedData.style_grades || [];

    // console.log("Starting copyPlanDetailsToClipboard function."); // Debug log
    // console.log("allFetchedData:", allFetchedData); // Debug log
    // console.log("attributesData (after || []):", attributesData); // Debug log
    // console.log("SkillsData (after || []):", skillsData); // Debug log

    const moodOptions = <?php echo json_encode($moodOptions); ?>;
    const strategyOptions = <?php echo json_encode($strategyOptions); ?>;
    const conditionOptions = <?php echo json_encode($conditionOptions); ?>;

    const moodLabel = moodOptions.find(opt => opt.id == plan.mood_id)?.label || 'N/A';
    const strategyLabel = strategyOptions.find(opt => opt.id == plan.strategy_id)?.label || 'N/A';
    const conditionLabel = conditionOptions.find(opt => opt.id == plan.condition_id)?.label || 'N/A';

    let output = '';
    const sectionDivider = '\n' + '='.repeat(80) + '\n\n';

    // --- GENERAL SECTION ---
    output += `## PLAN: ${plan.plan_title || 'Untitled Plan'} ##\n\n`;
    const generalInfo = [
        ['Trainee Name:', `${plan.name || ''}`],
        ['Career Stage:', `${(plan.career_stage || '').toUpperCase()} (${(plan.month || '').toUpperCase()} ${(plan.time_of_day || '').toUpperCase()})`],
        ['Class:', `${(plan.class || '').toUpperCase()}`],
        ['Status:', `${plan.status || 'Planning'}`],
        ['Next Race:', `${plan.race_name || 'N/A'}`],
        ['Turn Before:', `${plan.turn_before || 0}`],
    ];
    // Simple key-value pair output, padded for alignment
    const maxKeyLength = Math.max(...generalInfo.map(row => row[0].length));
    generalInfo.forEach(row => {
        output += `${TxtBuilder.pad(row[0], maxKeyLength)} ${row[1]}\n`;
    });
    
    // --- ATTRIBUTES & GRADES SECTION ---
    output += sectionDivider;

    // Attributes Table
    // Corrected: attributesData.map expects attribute_name to be lowercase, but DB returns uppercase.
    // Ensure display is consistent by converting to Title Case.
    const formattedAttributesData = attributesData.map(attr => [
        attr.attribute_name ? attr.attribute_name.charAt(0).toUpperCase() + attr.attribute_name.slice(1).toLowerCase() : '',
        attr.value,
        attr.grade
    ]);
    const attributesTable = TxtBuilder.buildTable(
        ['Attribute', 'Value', 'Grade'],
        formattedAttributesData,
        [{ align: 'left' }, { align: 'right' }, { align: 'center' }]
    );
    output += 'ATTRIBUTES\n' + attributesTable + '\n\n';

    // Grades Table
    const maxGradeRows = Math.max(terrainGradesData.length, distanceGradesData.length, styleGradesData.length);
    const gradeRows = [];
    // Ensure all grade data is available even if one type is missing data
    const allGradeItems = new Set();
    terrainGradesData.forEach(item => allGradeItems.add(item.terrain));
    distanceGradesData.forEach(item => allGradeItems.add(item.distance));
    styleGradesData.forEach(item => allGradeItems.add(item.style));

    const terrainMap = new Map(terrainGradesData.map(item => [item.terrain, item.grade]));
    const distanceMap = new Map(distanceGradesData.map(item => [item.distance, item.grade]));
    const styleMap = new Map(styleGradesData.map(item => [item.style, item.grade]));
    
    const defaultGradeKeys = {
        terrain: ['Turf', 'Dirt'],
        distance: ['Sprint', 'Mile', 'Medium', 'Long'],
        style: ['Front', 'Pace', 'Late', 'End']
    };

    const combinedGradeRows = [];
    // Iterate over expected types for consistent order and completeness
    for (const key of defaultGradeKeys.distance) {
        combinedGradeRows.push([
            key, distanceMap.get(key) || 'G',
            defaultGradeKeys.style[defaultGradeKeys.distance.indexOf(key)] || '', // Attempt to align style conceptually
            styleMap.get(defaultGradeKeys.style[defaultGradeKeys.distance.indexOf(key)]) || 'G',
            defaultGradeKeys.terrain[defaultGradeKeys.distance.indexOf(key)] || '', // Attempt to align terrain conceptually
            terrainMap.get(defaultGradeKeys.terrain[defaultGradeKeys.distance.indexOf(key)]) || 'G',
        ]);
    }

    const gradesTable = TxtBuilder.buildTable(
        ['Distance', 'G', 'Style', 'G', 'Terrain', 'G'],
        combinedGradeRows.filter(row => row.some(cell => cell !== '' && cell !== 'G')), // Filter out completely empty rows
        [{align: 'left'}, {align: 'center'}, {align: 'left'}, {align: 'center'}, {align: 'left'}, {align: 'center'}]
    );
    output += 'APTITUDE GRADES\n' + gradesTable + '\n';


    // --- SUMMARY & GROWTH ---
    output += sectionDivider;
    const summaryRows = [
        ['Total SP', plan.total_available_skill_points || 0],
        ['Acquire Skill?', (plan.acquire_skill || 'NO').toUpperCase()],
        ['Conditions', conditionLabel],
        ['Mood', moodLabel],
        ['Energy', `${plan.energy || 0} / 100`],
        ['Race Day?', (plan.race_day || 'no').toUpperCase()],
        ['Goal', plan.goal || ''],
        ['Strategy', strategyLabel],
        ['---', '---'], // Divider
        ['Growth: Speed', `+${plan.growth_rate_speed || 0}%`],
        ['Growth: Stamina', `+${plan.growth_rate_stamina || 0}%`],
        ['Growth: Power', `+${plan.growth_rate_power || 0}%`],
        ['Growth: Guts', `+${plan.growth_rate_guts || 0}%`],
        ['Growth: Wit', `+${plan.growth_rate_wit || 0}%`],
    ];
    const summaryTable = TxtBuilder.buildTable(
        ['Item', 'Value'],
        summaryRows,
        [{align: 'left'}, {align: 'right'}]
    );
    output += 'SUMMARY & GROWTH\n' + summaryTable + '\n';


    // --- SKILLS SECTION ---
    output += sectionDivider;
    const skillsTable = TxtBuilder.buildTable(
        ['Skill Name', 'SP Cost', 'Acquired', 'Notes'],
        skillsData.map(skill => [
            skill.skill_name || '',
            skill.sp_cost || 'N/A',
            (skill.acquired || '').toLowerCase() === 'yes' ? '✅' : '❌',
            skill.notes || ''
        ]),
        [{align: 'left'}, {align: 'right'}, {align: 'center'}, {align: 'left'}]
    );
    output += 'ACQUIRED SKILLS\n' + skillsTable + '\n';
    
    // --- GOALS SECTION ---
    output += sectionDivider;
    output += 'CAREER GOALS\n' + '-'.repeat(80) + '\n';
    if (goalsData.length > 0) {
        goalsData.forEach(goal => {
            const result = (goal.result && goal.result !== 'Pending') ? ` (Result: ${goal.result})` : '';
            output += `• ${goal.goal}${result}\n`;
        });
    } else {
        output += 'No goals specified.\n';
    }
    
    // --- RACE PREDICTIONS ---
    output += sectionDivider;
    output += 'RACE DAY PREDICTIONS\n' + '-'.repeat(80) + '\n';
    if (predictionsData.length > 0) {
        predictionsData.forEach((pred, index) => {
            output += `Prediction #${index + 1}: ${pred.race_name || 'N/A'}\n`;
            output += `  Venue: ${pred.venue}, ${pred.ground}, ${pred.distance}, ${pred.direction}, Track: ${pred.track_condition}\n`;
            const stats = `  SPEED[${pred.speed}] STAMINA[${pred.stamina}] POWER[${pred.power}] GUTS[${pred.guts}] WIT[${pred.wit}]`;
            output += stats + '\n';
            output += `  Comment: ${pred.comment || 'N/A'}\n\n`;
        });
    } else {
        output += 'No race predictions available.\n';
    }


    // --- FINAL ACTION: Copy to clipboard ---
    try {
        navigator.clipboard.writeText(output.trim()).then(() => {
            showMessageBox('Formatted plan copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Failed to copy text (clipboard API error): ', err); // More specific error
            showMessageBox('Failed to copy plan details to clipboard. Browser permission denied or API error.', 'danger');
        });
    } catch (e) {
        console.error("Error before clipboard API call:", e); // Catch immediate errors
        showMessageBox('An unexpected error occurred before attempting to copy.', 'danger');
    }
}
</script>