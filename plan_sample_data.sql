-- plan_sample_data.sql
-- Sample Uma Musume plans, attributes, skills, goals, terrain, distance, style, and predictions
-- For schema: uma_musume_planner (see main schema for table definitions)

-- Ensure lookup tables are pre-populated (moods, conditions, strategies, skill_reference)
-- These inserts assume those tables contain appropriate values and will error if not present.

-- ==============================================
-- PLAN 1: [pf. Winning Equation…] Biwa Hayahide
-- ==============================================
INSERT INTO plans (
    plan_title, turn_before, race_name, name, career_stage, class,
    total_available_skill_points, acquire_skill,
    mood_id, condition_id, energy, race_day, goal, strategy_id,
    growth_rate_speed, growth_rate_power, growth_rate_wit, growth_rate_stamina, growth_rate_guts,
    status
) VALUES (
    '[pf. Winning Equation…] Biwa Hayahide Plan', 0, 'Tenno Sho (Spring)', '[pf. Winning Equation…] Biwa Hayahide',
    'senior', 'silver', 17, 'NO',
    (SELECT id FROM moods WHERE label='GOOD'),
    (SELECT id FROM conditions WHERE label='HOT TOPIC'), 20, 'yes', 'TOP 3',
    (SELECT id FROM strategies WHERE label='PACE'),
    0, 0, 20, 0, 10, 'Planning'
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 396, 'D+'),
(@plan_id, 'STAMINA', 340, 'D'),
(@plan_id, 'POWER', 368, 'D+'),
(@plan_id, 'GUTS', 400, 'C'),
(@plan_id, 'WIT', 254, 'E+');

-- Skills (reference must exist)
-- Robust skill inserts: only insert if skill exists
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, NULL, 'yes', '(Unique) Final-corner burst' FROM skill_reference WHERE skill_name='∴ Win Q.E.D. Lvl.2';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, NULL, 'yes', 'Situational — only if forecast wet' FROM skill_reference WHERE skill_name='Wet Conditions ()';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '110', 'no', 'Situational — only if forecast wet' FROM skill_reference WHERE skill_name='Wet Conditions (())';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '90', 'no', 'Position-based skill' FROM skill_reference WHERE skill_name='Outer Post Proficiency ()';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, NULL, 'yes', 'Boosts acceleration on straights' FROM skill_reference WHERE skill_name='Straightaway Acceleration';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '119', 'no', 'Slight stamina & mentality buff' FROM skill_reference WHERE skill_name='In Body and Mind';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, NULL, 'yes', 'Late-spurt speed boost' FROM skill_reference WHERE skill_name='Homestretch Haste';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '342', 'no', 'Final-corner lead hold (front-runner)' FROM skill_reference WHERE skill_name='Unrestrained';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '180', 'no', 'Slight acceleration on final corner' FROM skill_reference WHERE skill_name='Final Push';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, NULL, 'yes', 'High-cost, stamina recovery' FROM skill_reference WHERE skill_name='Stamina to Spare';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '162', 'no', 'Power burst in mid-race corners' FROM skill_reference WHERE skill_name='Masterful Gambit';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '144', 'no', 'Mid‑race positioning boost' FROM skill_reference WHERE skill_name='Up-Tempo';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '160', 'no', 'Mid-race stamina sustain' FROM skill_reference WHERE skill_name='Deep Breaths';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '91', 'no', 'Debuffs late chasers' FROM skill_reference WHERE skill_name='Flustered End Closers';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, '91', 'no', 'Debuffs tired late runners' FROM skill_reference WHERE skill_name='Hesitant End Closers';
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes)
SELECT @plan_id, id, NULL, 'yes', 'Positioning tweak mid-race' FROM skill_reference WHERE skill_name='Tactical Tweak';

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'A'),
(@plan_id, 'Dirt', 'F');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'F'),
(@plan_id, 'Mile', 'B'),
(@plan_id, 'Medium', 'A'),
(@plan_id, 'Long', 'A');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'E'),
(@plan_id, 'Pace', 'A'),
(@plan_id, 'Late', 'B'),
(@plan_id, 'End', 'E');

-- ==============================================
-- PLAN 2: [Wild Top Gear] Vodka (Finale Season, STAR)
-- ==============================================
INSERT INTO plans (
    plan_title, race_name, name, career_stage, class, status, goal,
    growth_rate_speed, growth_rate_power, growth_rate_wit, growth_rate_stamina, growth_rate_guts
) VALUES (
    '[Wild Top Gear] Vodka Plan', 'URA Finale Finals', '[Wild Top Gear] Vodka', 'finale', 'star', 'Finished', 'VODKA IS 1ST PLACE',
    10, 20, 0, 0, 0
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 767, 'B+'),
(@plan_id, 'STAMINA', 410, 'C'),
(@plan_id, 'POWER', 769, 'B+'),
(@plan_id, 'GUTS', 324, 'D'),
(@plan_id, 'WIT', 253, 'E+');

-- Skills
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Xceleration Lvl. 2'), NULL, 'yes', '(Unique Bursts)'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='∴ Win Q.E.D.'), '180', 'no', 'Final-corner burst'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Standard Distance ○'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Standard Distance ⦾'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Remove Wet Conditions x'), NULL, 'yes', 'Removes the skill debuff: DONE'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Acceleration'), '170', 'no', 'Boosts acceleration on straights'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Recovery'), '170', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Iron Will'), '304', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Lay Low'), '160', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Nimble Navigator'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Homestretch Haste'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Early Lead'), '108', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Unrestrained'), '162', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Final Push'), '162', 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Slick Surge'), '180', 'no', 'Late-race acceleration'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Masterful Gambit'), '162', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Updrafters'), NULL, 'yes', 'Late surger burst'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Steadfast'), '112', 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Hesitant End Closers'), '117', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Shifting Gears'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Front Runner Straightaways ○'), '117', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Front Runner Corners ○'), '117', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Front Runner Savvy○'), '99', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Tail Held High'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Wet Conditions ○'), '90', 'no', 'Moderately increase performance on good, soft, and heavy ground.');

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'A'),
(@plan_id, 'Dirt', 'G');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'F'),
(@plan_id, 'Mile', 'A'),
(@plan_id, 'Medium', 'A'),
(@plan_id, 'Long', 'F');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'C'),
(@plan_id, 'Pace', 'B'),
(@plan_id, 'Late', 'A'),
(@plan_id, 'End', 'F');

-- ==============================================
-- PLAN 3: [Wild Top Gear] Vodka (Finale Season, PLATINUM)
-- ==============================================
INSERT INTO plans (
    plan_title, race_name, name, career_stage, class, total_available_skill_points, acquire_skill, status, goal,
    growth_rate_speed, growth_rate_power, growth_rate_wit, growth_rate_stamina, growth_rate_guts
) VALUES (
    '[Wild Top Gear] Vodka Plan', 'URA Finale Finals', '[Wild Top Gear] Vodka', 'finale', 'platinum', 347, 'YES', 'Finished', 'SHE WON 1ST',
    10, 20, 0, 0, 0
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 646, 'B'),
(@plan_id, 'STAMINA', 474, 'C'),
(@plan_id, 'POWER', 765, 'B+'),
(@plan_id, 'GUTS', 284, 'E+'),
(@plan_id, 'WIT', 279, 'E+');

-- Skills (see above for full set, abbreviated here)
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Cut and Drive! Lvl. 1'), NULL, 'yes', 'Unique front-run burst in final 200 m – Vodka’s innate skill'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='∴ Win Q.E.D.'), '180', 'no', 'Unique: speed boost when passing at final corner'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Acceleration'), NULL, 'yes', 'Boosts acceleration on straights'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Recovery'), NULL, 'yes', 'Normal stamina recovery skill on straights'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Focus'), NULL, 'yes', 'Improves early positioning/cornering'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Nimble Navigator'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Homestretch Haste'), NULL, 'yes', 'Speed boost near race end'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Unrestrained'), '342', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Final Push'), '180', 'no', 'Late acceleration buffer'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Slick Surge'), '180', 'no', 'Normal late‑race acceleration boost'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Masterful Gambit'), '162', 'no', 'Late burst skill with riskier pacing, better for mid-pack builds'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Updrafted'), '160', 'no', 'Normal passing aid skill in late race'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Front Runner Straightaways ○'), '117', 'no', 'Boosts straight speed for front-style'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Hydrate'), '126', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Late Surger Savvy ○'), NULL, 'yes', 'Enhances burst in late/mid-race segments'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Late Surger Savvy ⦾'), NULL, 'yes', NULL);

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'A'),
(@plan_id, 'Dirt', 'G');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'F'),
(@plan_id, 'Mile', 'A'),
(@plan_id, 'Medium', 'A'),
(@plan_id, 'Long', 'F');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'C'),
(@plan_id, 'Pace', 'B'),
(@plan_id, 'Late', 'A'),
(@plan_id, 'End', 'F');

-- ================================
-- PLAN 4: [Peak Blue] Daiwa Scarlet
-- ================================
INSERT INTO plans (
    plan_title, race_name, name, career_stage, class, total_available_skill_points, acquire_skill, status, goal, strategy_id,
    growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit
) VALUES (
    '[Peak Blue] Daiwa Scarlet Plan', 'URA Finale Finals', '[Peak Blue] Daiwa Scarlet', 'finale', 'star', 75, 'YES', 'Finished', 'SHE’S 2ND PLACE',
    (SELECT id FROM strategies WHERE label='FRONT'),
    10, 0, 0, 20, 0
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 663, 'B'),
(@plan_id, 'STAMINA', 437, 'C'),
(@plan_id, 'POWER', 543, 'C+'),
(@plan_id, 'GUTS', 408, 'C'),
(@plan_id, 'WIT', 303, 'D');

-- Skills (see above for full set, abbreviated here)
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Resplendent Red Ace Lvl. 1'), NULL, 'yes', '(Unique Burst) Boosts speed when maintaining front in latter half'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Standard Distance ○'), '63', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Standard Distance ⦾'), '77', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Wet Conditions ○'), '63', 'no', 'Moderately boosts performance on soft or heavy turf'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Fall Runner ○'), '81', 'no', 'Improves start performance in Autumn races'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Maverick ○'), '54', 'no', 'Increases performance when running a unique strategy'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Competitive Spirit ○'), NULL, 'yes', 'Boosts power when leading with several others in same strategy'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Competitive Spirit ⦾'), '110', 'no', 'Stronger tier of Competitive Spirit’s power boost'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Corner Connoisseur'), '342', 'no', 'Acceleration through corners—best for cornering specialists'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Corner Acceleration ○'), '180', 'no', 'Burst of speed when taking corners'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Acceleration'), NULL, 'yes', 'Burst speed on long straight sections'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Iron Will'), '304', 'no', 'Recovery if trapped mid-pack early'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Lay Low'), '160', 'no', 'Better stamina in back pack'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Homestretch Haste'), NULL, 'yes', 'Speed burst in final spurt; good for overtaking finish'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Fast-Paced'), NULL, 'yes', 'Mid-race speed boost; great for maintaining early pace'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Final Push'), '162', 'no', 'Helps maintain corner speed in late race'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Stamina to Spare'), '180', 'no', 'Early-race stamina recovery'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Preferred Position'), '180', 'no', 'Reduces mid-race stamina loss'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Speed Star'), '306', 'no', 'Enhanced speed on straight paths'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Prepared to Pass'), '180', 'no', 'Bonus when challenging others'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Up-Tempo'), NULL, 'yes', 'Boosts positioning when running own pace'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Steadfast'), '144', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Inside Scoop'), '144', 'no', 'Lane awareness boost'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Unyielding Spirit'), NULL, 'yes', 'Recovery when losing lead'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Pressure'), '144', 'no', 'Speed boost when overtaking another runner'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Front Runner Corners ○'), NULL, 'yes', 'Speed bonus on corners when leading'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Front Runner Corners ⦾'), NULL, 'yes', 'Stronger version'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Late Surger Straightaways ○'), '91', 'no', 'Acceleration at late straight for closing runners'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='After-School Stroll'), '153', 'no', 'Small recovery + stamina balance');

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'A'),
(@plan_id, 'Dirt', 'G');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'F'),
(@plan_id, 'Mile', 'A'),
(@plan_id, 'Medium', 'A'),
(@plan_id, 'Long', 'A');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'A'),
(@plan_id, 'Pace', 'A'),
(@plan_id, 'Late', 'E'),
(@plan_id, 'End', 'G');

-- ================================
-- PLAN 5: [Beyond the Horizon] Tokai Teio
-- ================================
INSERT INTO plans (
    plan_title, race_name, name, career_stage, class, total_available_skill_points, acquire_skill, mood_id, energy, race_day, goal, strategy_id,
    growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit,
    status
) VALUES (
    '[Beyond the Horizon] Tokai Teio Plan', 'Tenno Sho (Spring)', '[Beyond the Horizon] Tokai Teio', 'classic', 'gold', 38, 'NO',
    (SELECT id FROM moods WHERE label='NORMAL'), 30, 'yes', 'TOP 3',
    (SELECT id FROM strategies WHERE label='PACE'),
    10, 10, 0, 10, 0, 'Planning'
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 345, 'D'),
(@plan_id, 'STAMINA', 395, 'D+'),
(@plan_id, 'POWER', 256, 'E+'),
(@plan_id, 'GUTS', 252, 'E+'),
(@plan_id, 'WIT', 303, 'D');

-- Skills (see above for full set, abbreviated here)
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Certain Victory Lvl. 2'), NULL, 'yes', '(Unique Burst)– triggers when overtaking in the front of final straight.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Resplendent Red Ace'), NULL, 'yes', 'Unique Speed Boost: activates in the second half of the race, improving top‐end velocity.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Corner Connoiseur'), '342', 'no', 'Grants an acceleration burst when navigating corners—ideal for tracks with tight turns.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Corner Acceleration ○'), '180', 'no', 'Provides a one-time speed burst on corners, helping pull ahead through bends.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Iron Will'), '304', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Lay Low'), '160', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Prudent Positioning'), NULL, 'yes', 'Boosts lane navigation early in the race—great for avoiding traffic'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Nimble Navigator'), '135', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Stamina to Spare'), NULL, 'yes', 'Recovers a small amount of stamina when fatigue sets in early, useful for longer races.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Preferred Position'), NULL, 'yes', 'Reduces mid-race stamina loss when held mid-pack/front.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Prepared to Pass'), '162', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Up-Tempo'), '144', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Deep Breaths'), '144', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Inside Scoop'), '144', 'no', 'Enhances acceleration when moving laterally toward the inner rail, avoiding crowding.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Frenzied End Closers'), '91', 'no', 'Debuffs late-racing opponents in the homestretch, making it easier to hold a lead.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Soft Step'), '160', 'no', 'Ideal for ground condition agility; early race aid.'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Pressure'), '144', 'no', NULL);

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'A'),
(@plan_id, 'Dirt', 'G');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'F'),
(@plan_id, 'Mile', 'D'),
(@plan_id, 'Medium', 'A'),
(@plan_id, 'Long', 'A');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'C'),
(@plan_id, 'Pace', 'A'),
(@plan_id, 'Late', 'C'),
(@plan_id, 'End', 'E');

-- ================================
-- PLAN 6: [Bestest Prize 𝆕] Haru Urara (Senior Year, Early November)
-- ================================
INSERT INTO plans (
    plan_title, race_name, name, career_stage, class, total_available_skill_points, acquire_skill, mood_id, energy, race_day, goal, strategy_id,
    growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit,
    status
) VALUES (
    '[Bestest Prize 𝆕] Haru Urara Plan', 'JBC SPRINT', '[Bestest Prize 𝆕] Haru Urara', 'senior', 'silver', 174, 'YES',
    (SELECT id FROM moods WHERE label='GOOD'), 20, 'yes', 'MUST 1ST',
    (SELECT id FROM strategies WHERE label='LATE'),
    0, 0, 0, 20, 0, 'Planning'
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 485, 'C'),
(@plan_id, 'STAMINA', 305, 'D'),
(@plan_id, 'POWER', 404, 'C'),
(@plan_id, 'GUTS', 314, 'D'),
(@plan_id, 'WIT', 264, 'E+');

-- Skills (see above for full set, abbreviated here)
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Super Duper Stoked Lvl.1'), NULL, 'yes', '(Unique Burst)— huge late-race power for close finishes'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='∴ Win Q.E.D.'), '180', 'no', 'Received from legacy. — powerful finishing burst'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Wet Conditions ○'), NULL, 'yes', 'Track Softness bonus — boosts speed/power on wet dirt'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Wet Conditions ⦾'), '77', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Acceleration'), NULL, 'yes', 'Boosts mid-race–great for long straight sections'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Lay Low'), '160', 'no', 'Late positioning — helps close the pack when behind'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Homestretch Haste'), NULL, 'yes', 'Good burst entering the final stretch'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Unrestrained'), '342', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Final Push'), '180', 'no', 'Guaranteed burst in final push'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Stamina To Spare'), '162', 'no', 'Minor recovery during race — useful for longer distances'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Masterful Gambit'), NULL, 'yes', 'Big burst if she stays far back'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Sprinting Gear'), NULL, 'yes', 'Early burst — helps build momentum around stretch'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Trick (Front)'), '98', 'no', 'Sabotage Front runners'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Flustered End Corners'), '117', 'no', 'Debuffs close competitors in turn exit'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Hydrate'), '126', 'no', 'Minor performance stabilization'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='1,500,000 CC'), NULL, 'yes', NULL);

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'D'),
(@plan_id, 'Dirt', 'A');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'A'),
(@plan_id, 'Mile', 'A'),
(@plan_id, 'Medium', 'G'),
(@plan_id, 'Long', 'G');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'G'),
(@plan_id, 'Pace', 'G'),
(@plan_id, 'Late', 'A'),
(@plan_id, 'End', 'B');

-- ================================
-- PLAN 7: [Bestest Prize 𝆕] Haru Urara (Finale Season, Silver)
-- ================================
INSERT INTO plans (
    plan_title, race_name, name, career_stage, class, total_available_skill_points, acquire_skill, mood_id, condition_id, energy, race_day, goal, strategy_id,
    growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit,
    status
) VALUES (
    '[Bestest Prize 𝆕] Haru Urara Plan', 'URA Finale Qualifier', '[Bestest Prize 𝆕] Haru Urara', 'finale', 'silver', 4, 'NO',
    (SELECT id FROM moods WHERE label='GOOD'),
    (SELECT id FROM conditions WHERE label='CHARMING'), 20, 'yes', '1ST',
    (SELECT id FROM strategies WHERE label='LATE'),
    0, 0, 10, 20, 0, 'Planning'
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 423, 'C'),
(@plan_id, 'STAMINA', 276, 'E+'),
(@plan_id, 'POWER', 461, 'C'),
(@plan_id, 'GUTS', 448, 'C'),
(@plan_id, 'WIT', 264, 'E+');

-- Skills (see above for full set, abbreviated here)
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Super Duper Stoked Lvl.1'), NULL, 'yes', '(Unique Burst)'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='∴ Win Q.E.D.'), NULL, 'yes', 'Received from legacy. — powerful finishing burst'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Summer Runner ○'), '63', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Rainy Days ○'), '63', 'no', 'Minor boost in rain — useful if track conditions change'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Beeline Burst'), '323', 'no', 'Strong mid-stretch boost for overtaking'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Adept'), '170', 'no', 'Enhances straight-line speed on dirt'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Lay Low'), NULL, 'yes', 'Late positioning — helps close the pack when behind'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Pace Strategy'), '170', 'no', 'Improves race rhythm and stamina efficiency'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Calm in a Crowd'), NULL, 'yes', 'Boosts mental focus and late-game consistency'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Homestretch Haste'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Unrestrained'), '342', 'no', 'Powerful burst'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Final Push'), '180', 'no', 'Ideal for late-stage sprint — complements existing bursts'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Masterful Gambit'), NULL, 'yes', 'Tactical speed/damage buff affecting positioning'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Sprinting Gear'), NULL, 'yes', 'Early burst — helps build momentum around stretch'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Rosy Outlook'), '144', 'no', 'Passive boost in morale → slight stat edge'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Subdued Front Runners'), '117', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Flustered End Closers'), '117', 'no', 'Lowest-cost late sprint burst'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Meticulous Measures'), '126', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Second Wind'), '162', 'no', 'Recovers stamina mid-race'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Hydrate'), '126', 'no', 'Minor stamina regen mid-race'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Tactical Tweak'), '108', 'no', 'Small boost to decision-making speed'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='1,500,000 CC'), NULL, 'yes', 'Slightly increase velocity on an uphill. (Late Surger)');

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'C'),
(@plan_id, 'Dirt', 'A');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'A'),
(@plan_id, 'Mile', 'A'),
(@plan_id, 'Medium', 'G'),
(@plan_id, 'Long', 'G');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'G'),
(@plan_id, 'Pace', 'G'),
(@plan_id, 'Late', 'A'),
(@plan_id, 'End', 'B');

-- ================================
-- PLAN 8: [El☆Número 1] El Condor Pasa (Junior Year, Early July)
-- ================================
INSERT INTO plans (
    plan_title, turn_before, race_name, name, career_stage, class, total_available_skill_points, acquire_skill, mood_id, energy, race_day, goal, strategy_id,
    growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit,
    status
) VALUES (
    '[El☆Número 1] El Condor Pasa Plan', 12, 'Kyodo News Hai', '[El☆Número 1] El Condor Pasa', 'junior', 'beginner', 38, 'NO',
    (SELECT id FROM moods WHERE label='GOOD'), 50, 'no', 'TOP 5',
    (SELECT id FROM strategies WHERE label='PACE'),
    20, 0, 0, 0, 10, 'Planning'
);
SET @plan_id = LAST_INSERT_ID();

-- Attributes
INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES
(@plan_id, 'SPEED', 194, 'F+'),
(@plan_id, 'STAMINA', 154, 'F+'),
(@plan_id, 'POWER', 133, 'F'),
(@plan_id, 'GUTS', 97, 'G+'),
(@plan_id, 'WIT', 156, 'F+');

-- Skills (see above for full set, abbreviated here)
INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, notes) VALUES
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Corazón ☆ Ardiente'), NULL, 'yes', '(Unique Burst)'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Resplendent Red Ace'), '180', 'no', 'Received from Legacy'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Certain Victory'), '180', 'no', 'Received from Legacy'),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Straightaway Adept'), '170', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Stamina to Spare'), '180', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Hawkeye'), '110', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Soft Step'), '144', 'no', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Pace Chaser Straightaways ○'), NULL, 'yes', NULL),
(@plan_id, (SELECT id FROM skill_reference WHERE skill_name='Pace Chaser Straightaways ⦾'), '140', 'no', NULL);

-- Terrain
INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES
(@plan_id, 'Turf', 'A'),
(@plan_id, 'Dirt', 'B');

-- Distance
INSERT INTO distance_grades (plan_id, distance, grade) VALUES
(@plan_id, 'Sprint', 'F'),
(@plan_id, 'Mile', 'A'),
(@plan_id, 'Medium', 'A'),
(@plan_id, 'Long', 'B');

-- Style
INSERT INTO style_grades (plan_id, style, grade) VALUES
(@plan_id, 'Front', 'C'),
(@plan_id, 'Pace', 'A'),
(@plan_id, 'Late', 'A'),
(@plan_id, 'End', 'C');