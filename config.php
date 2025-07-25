<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'uma_musume_planner');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create lookup tables if they don't exist and insert initial data
$conn->query("CREATE TABLE IF NOT EXISTS moods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL UNIQUE
)");
$conn->query("INSERT IGNORE INTO moods (label) VALUES
    ('AWFUL'), ('BAD'), ('GOOD'), ('GREAT'), ('NORMAL'), ('N/A')");

$conn->query("CREATE TABLE IF NOT EXISTS conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL UNIQUE
)");
$conn->query("INSERT IGNORE INTO conditions (label) VALUES
    ('RAINY'), ('SUNNY'), ('WINDY'), ('COLD'), ('N/A')");

$conn->query("CREATE TABLE IF NOT EXISTS strategies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL UNIQUE
)");
$conn->query("INSERT IGNORE INTO strategies (label) VALUES
    ('FRONT'), ('PACE'), ('LATE'), ('END'), ('N/A')");

// Create main plans table
$conn->query("CREATE TABLE IF NOT EXISTS plans (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_title` VARCHAR(255),
  `race_name` VARCHAR(255),
  `name` VARCHAR(255),
  `career_stage` ENUM('predebut','junior','classic','senior','finale') DEFAULT NULL,
  `class` ENUM('debut','maiden','beginner','bronze','silver','gold','platinum','star','legend') DEFAULT NULL,
  `time_of_day` VARCHAR(50),
  `month` VARCHAR(50),
  `total_available_skill_points` INT,
  `acquire_skill` ENUM('YES','NO') DEFAULT 'NO',
  `mood_id` INT,
  `condition_id` INT,
  `energy` TINYINT DEFAULT NULL,
  `race_day` ENUM('yes','no') DEFAULT 'no',
  `strategy_id` INT,
  `growth_rate_speed` INT DEFAULT 0,
  `growth_rate_stamina` INT DEFAULT 0,
  `growth_rate_power` INT DEFAULT 0,
  `growth_rate_guts` INT DEFAULT 0,
  `growth_rate_wit` INT DEFAULT 0,
  `status` ENUM('Planning','Active','Finished','Draft','Abandoned') DEFAULT 'Planning',
  `source` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_name` (`name`),
  KEY `idx_race_name` (`race_name`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`mood_id`) REFERENCES `moods` (`id`),
  FOREIGN KEY (`condition_id`) REFERENCES `conditions` (`id`),
  FOREIGN KEY (`strategy_id`) REFERENCES `strategies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create attributes table
$conn->query("CREATE TABLE IF NOT EXISTS attributes (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `attribute_name` VARCHAR(50) NOT NULL,
  `value` INT NOT NULL,
  `grade` VARCHAR(10),
  KEY `plan_id` (`plan_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create skills table
$conn->query("CREATE TABLE IF NOT EXISTS skills (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `skill_name` VARCHAR(255) NOT NULL,
  `sp_cost` VARCHAR(50),
  `acquired` ENUM('yes','no') DEFAULT 'no',
  `tag` VARCHAR(50),
  `notes` TEXT,
  KEY `plan_id` (`plan_id`),
  KEY `idx_skill_name` (`skill_name`),
  KEY `idx_tag` (`tag`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create skill_reference table BEFORE attempting to insert into it
$conn->query("CREATE TABLE IF NOT EXISTS skill_reference (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_name` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT,
  `stat_type` VARCHAR(20),
  `best_for` TEXT,
  `tag` VARCHAR(5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Insert Skills into skill_reference (only if not already there)
$skills_to_insert = [
    ['Triumphant Pulse', 'High-powered final‑stretch acceleration burst', 'Acceleration', 'End Closers', '🔺'],
    ['Red Shift/LP1211‑M', 'Final‑corner burst when leading', 'Acceleration', 'Daiwa Scarlet, Maruzensky', '🔺'],
    ['Corner Acceleration ○', 'Burst of speed during corners', 'Acceleration', 'Corner maneuverers', '🔺'],
    ['Corner Adept ○', 'Slight speed boost on corners', 'Speed', 'All‑rounders', '🔺'],
    ['Slipstream', 'Acceleration boost when following close behind another', 'Acceleration', 'Pacer / stalkers', '🔺'],
    ['Tail Held High', 'Speed boost on final straight', 'Speed', 'Late‑stage front/pacers', '🔺'],
    ['Straightaway Adept', 'Slight speed burst on straights', 'Speed', 'Front or pace strategies', '🔺'],
    ['Straightaway Acceleration', 'Acceleration on straights', 'Acceleration', 'Medium‑distance / pace runs', '🔺'],
    ['Straightaways ○', 'Speed up on straight segments', 'Speed', 'All track types', '🔺'],
    ['Corners ○', 'Speed boost during corner turns', 'Speed', 'All track types', '🔺'],
    ['Swinging Maestro', 'Recover stamina and improve navigation in corners', 'Recovery + Positioning', 'Long‑distance / corner‑heavy races', '🔋'],
    ['Fast & Furious', 'Mid‑race speed boost', 'Speed', 'Pace/front runners', '🔺'],
    ['Shifting Gears', 'Acceleration when passing mid‑race', 'Acceleration', 'Front‑runner builds', '🔺'],
    ['Speed Star', 'Easy‑to‑proc corner speed buff', 'Speed', 'All‑rounders', '🔺'],
    ['Inside Scoop', 'Corner boost when near inner rail', 'Acceleration', 'Corner‑savvy runners', '🔺'],
    ['Pressure', 'Slight accel. boost when passing another horse', 'Acceleration', 'Gold Ship, End‑Closer builds', '🔺'],
    ['Straightaway Spurt', 'Final straight acceleration burst', 'Acceleration', 'Vodka', '🔺'],
    ['Unrestrained', 'Hold the lead on the final corner', 'Speed', 'Front‑runners', '🔺'],
    ['Acceleration', 'General pass burst mid‑race', 'Acceleration', 'Front or passing builds', '🔺'],
    ['Moxie', 'Burst of late‑race acceleration when contested', 'Acceleration', 'Late‑surger / pace‑fallback builds', '🔺'],
    ['Concentration', 'Reduce time lost to slow starts', 'Speed', 'All runners', '🔺'],
    ['Hydrate', 'Recover stamina mid‑race', 'Recovery', 'All runners', '🔋'],
    ['Race Planner', 'Reduce early‑race stamina drain', 'Recovery', 'Mid‑long distance runs', '🔋'],
    ['Passing Pro', 'Recover stamina when passing', 'Recovery', 'Stalkers', '🔋'],
    ['Gourmand', 'Recover stamina upon triggering many skills', 'Recovery', 'Skill‑heavy builds', '🔋'],
    ['Shake It Out', 'Recover fatigue after multiple skills', 'Recovery', 'Combo‑skill builds', '🔋'],
    ['Lone Wolf', 'Speed boost if only one of your style', 'Passive', 'Niche/style‑split strategies', '📊'],
    ['Right‑Handed ○', 'Performance boost on right‑turn tracks', 'Passive', 'Track‑specific races', '📊'],
    ['Standard Distance ○', 'Boost on standard‑distance races', 'Passive', 'Mile/medium specialists', '📊'],
    ['Firm Conditions ○', 'Performance boost in firm (dry) conditions', 'Passive', 'Stable weather races', '📊'],
    ['Savvy (Style‑based) ○', 'Passive boost tied to your running style', 'Passive', 'Depends on run style', '📊'],
    ['Dominator', 'Debuff nearby opponents’ power mid‑race', 'Debuff', 'Lead‑protect builds', '⛔'],
    ['Intimidate', 'Lower stamina of surrounding foes', 'Debuff', 'Pack‑thin suppression', '⛔'],
    ['Mystifying Murmur', 'Confuse surrounding enemies, lowering their effectiveness', 'Debuff', 'High‑Wit PvP builds', '⛔'],
    ['All‑Seeing Eyes', 'Late‑race debuff against nearby opponents', 'Debuff', 'End‑battle setups', '⛔'],
    ['Stamina Eater', 'Reduce stamina of nearby rivals', 'Debuff', 'Long‑distance lead‑holding', '⛔'],
    ['Speed Eater', 'Reduce speed of opponents around you', 'Debuff', 'Competitive pacing suppression', '⛔'],
    ['Second Wind', 'Regain a burst of stamina mid‑race when fatigued', 'Recovery', 'Endurance‑hybrid runners', '🔋'],
    ['Iron Will', 'Early‑race recovery in crowded tracks', 'Recovery', 'Pack‑runner builds', '🔋'],
    ['Vanguard Spirit', 'Maintain speed when leading by a big margin', 'Speed', 'Long‑distance front‑running', '🔺'],
    ['Taking the Lead', 'Burst when surging to the front early‑race', 'Speed', 'Front‑runner builds', '🔺'],
    ['Turbo Sprint', 'Massive accel. boost in opening phase', 'Acceleration', 'Sprinters', '🔺'],
    ['Homestretch Haste', 'Small boost at start of final straight', 'Speed', 'Late rallies', '🔺'],
    ['Ramp Up', 'Gradual speed increase after mid‑race', 'Speed', 'Medium‑distance', '🔺'],
    ['Behold Thine Emperor', 'Massive corner acceleration when leading', 'Acceleration', 'Elite corner specialists', '🔺'],
    ['The Duty of Dignity Calls', 'Speed boost when leading late in race', 'Speed', 'Regal‑pacing builds', '🔺'],
];

foreach ($skills_to_insert as $skill) {
    $skill_name = $conn->real_escape_string($skill[0]);
    $description = $conn->real_escape_string($skill[1]);
    $stat_type = $conn->real_escape_string($skill[2]);
    $best_for = $conn->real_escape_string($skill[3]);
    $tag = $conn->real_escape_string($skill[4]);

    $conn->query("INSERT IGNORE INTO skill_reference
        (skill_name, description, stat_type, best_for, tag)
        VALUES ('$skill_name', '$description', '$stat_type', '$best_for', '$tag')");
}

// Create new tables from the SQL schema
$conn->query("CREATE TABLE IF NOT EXISTS terrain_grades (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `terrain` VARCHAR(50) NOT NULL,
  `grade` VARCHAR(10),
  KEY `plan_id` (`plan_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS distance_grades (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `distance` VARCHAR(50) NOT NULL,
  `grade` VARCHAR(10),
  KEY `plan_id` (`plan_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS style_grades (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `style` VARCHAR(50) NOT NULL,
  `grade` VARCHAR(10),
  KEY `plan_id` (`plan_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS race_predictions (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `race_name` VARCHAR(255),
  `venue` VARCHAR(255),
  `ground` VARCHAR(50),
  `distance` VARCHAR(50),
  `track_condition` VARCHAR(50),
  `direction` VARCHAR(50),
  `speed` VARCHAR(10) DEFAULT '○',
  `stamina` VARCHAR(10) DEFAULT '○',
  `power` VARCHAR(10) DEFAULT '○',
  `guts` VARCHAR(10) DEFAULT '○',
  `wit` VARCHAR(10) DEFAULT '○',
  `comment` TEXT,
  KEY `plan_id` (`plan_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS goals (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `goal` VARCHAR(255),
  `result` VARCHAR(255) DEFAULT 'Pending',
  KEY `plan_id` (`plan_id`),
  KEY `idx_result` (`result`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS turns (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `turn_number` INT NOT NULL,
  `speed` INT DEFAULT 0,
  `stamina` INT DEFAULT 0,
  `power` INT DEFAULT 0,
  `guts` INT DEFAULT 0,
  `wit` INT DEFAULT 0,
  KEY `plan_id` (`plan_id`),
  KEY `idx_turn_number` (`turn_number`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create activity_log table (added explicitly as it was used in index.php but missing from previous SQL schema)
$conn->query("CREATE TABLE IF NOT EXISTS activity_log (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `description` TEXT NOT NULL,
  `icon_class` VARCHAR(50) DEFAULT 'bi-info-circle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


// Insert sample data for "Haru Urara" if it doesn't already exist
$haru_urara_name = '[Bestest Prize] Haru Urara';
$check_haru_urara = $conn->prepare("SELECT id FROM plans WHERE name = ? LIMIT 1");
$check_haru_urara->bind_param("s", $haru_urara_name);
$check_haru_urara->execute();
$haru_urara_result = $check_haru_urara->get_result();

if ($haru_urara_result->num_rows === 0) {
    // Get mood, strategy, and condition IDs
    $moods = [];
    $result = $conn->query("SELECT id, label FROM moods");
    while ($row = $result->fetch_assoc()) {
        $moods[$row['label']] = $row['id'];
    }

    $strategies = [];
    $result = $conn->query("SELECT id, label FROM strategies");
    while ($row = $result->fetch_assoc()) {
        $strategies[$row['label']] = $row['id'];
    }

    $conditions = [];
    $result = $conn->query("SELECT id, label FROM conditions");
    while ($row = $result->fetch_assoc()) {
        $conditions[$row['label']] = $row['id'];
    }

    // Insert plans
    $conn->query("INSERT INTO plans (
        plan_title, race_name, name, career_stage, class, time_of_day,
        month, total_available_skill_points, acquire_skill, mood_id, condition_id,
        energy, race_day, strategy_id, growth_rate_speed, growth_rate_stamina,
        growth_rate_power, growth_rate_guts, growth_rate_wit, status, source
    ) VALUES (
        '[Bestest Prize] Haru Urara Plan', 'URA Finale Qualifier', '{$haru_urara_name}', 'finale', 'silver', 'Day',
        'January', 4, 'yes', {$moods['GOOD']}, {$conditions['SUNNY']},
        20, 'yes', {$strategies['LATE']}, 0, 0,
        0, 0, 0, 'Active', 'Sample Data'
    )");

    $last_id = $conn->insert_id;

    // Insert attributes
    $attributes = [
        ['SPEED', 423, 'C'],
        ['STAMINA', 276, 'E'],
        ['POWER', 461, 'C'],
        ['GUTS', 448, 'C'],
        ['WIT', 264, 'E']
    ];

    foreach ($attributes as $attr) {
        $name = $conn->real_escape_string($attr[0]);
        $value = $attr[1];
        $grade = $conn->real_escape_string($attr[2]);
        $conn->query("INSERT INTO attributes (plan_id, attribute_name, value, grade)
                     VALUES ($last_id, '$name', $value, '$grade')");
    }

    // Insert skills
    $skills_sample = [
        ['Triumphant Pulse', '500', 'yes', '🔺', 'Acquired automatically'],
        ['Corner Acceleration ○', '200', 'no', '🔺', 'Target for next turns']
    ];
    foreach ($skills_sample as $skill_s) {
        $skill_name_s = $conn->real_escape_string($skill_s[0]);
        $sp_cost_s = $conn->real_escape_string($skill_s[1]);
        $acquired_s = $conn->real_escape_string($skill_s[2]);
        $tag_s = $conn->real_escape_string($skill_s[3]);
        $notes_s = $conn->real_escape_string($skill_s[4]);
        $conn->query("INSERT INTO skills (plan_id, skill_name, sp_cost, acquired, tag, notes)
                     VALUES ($last_id, '$skill_name_s', '$sp_cost_s', '$acquired_s', '$tag_s', '$notes_s')");
    }

    // Insert race predictions
    $predictions_sample = [
        ['URA Finale Qualifier', 'Chuo', 'Turf', '2400m', 'Firm', 'Right', '◎', '○', '◎', '△', '○', 'Good chance to win'],
    ];
    foreach ($predictions_sample as $pred_s) {
        $conn->query("INSERT INTO race_predictions (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment)
                     VALUES ($last_id,
                     '{$conn->real_escape_string($pred_s[0])}',
                     '{$conn->real_escape_string($pred_s[1])}',
                     '{$conn->real_escape_string($pred_s[2])}',
                     '{$conn->real_escape_string($pred_s[3])}',
                     '{$conn->real_escape_string($pred_s[4])}',
                     '{$conn->real_escape_string($pred_s[5])}',
                     '{$conn->real_escape_string($pred_s[6])}',
                     '{$conn->real_escape_string($pred_s[7])}',
                     '{$conn->real_escape_string($pred_s[8])}',
                     '{$conn->real_escape_string($pred_s[9])}',
                     '{$conn->real_escape_string($pred_s[10])}',
                     '{$conn->real_escape_string($pred_s[11])}')");
    }

    // Insert terrain grades
    $terrain_grades_sample = [
        ['Turf', 'A'], ['Dirt', 'G']
    ];
    foreach ($terrain_grades_sample as $tg_s) {
        $conn->query("INSERT INTO terrain_grades (plan_id, terrain, grade) VALUES ($last_id, '{$conn->real_escape_string($tg_s[0])}', '{$conn->real_escape_string($tg_s[1])}')");
    }

    // Insert distance grades
    $distance_grades_sample = [
        ['Short', 'G'], ['Mile', 'G'], ['Medium', 'G'], ['Long', 'A']
    ];
    foreach ($distance_grades_sample as $dg_s) {
        $conn->query("INSERT INTO distance_grades (plan_id, distance, grade) VALUES ($last_id, '{$conn->real_escape_string($dg_s[0])}', '{$conn->real_escape_string($dg_s[1])}')");
    }

    // Insert style grades
    $style_grades_sample = [
        ['Runner', 'G'], ['Leader', 'G'], ['Betweener', 'G'], ['Chaser', 'A']
    ];
    foreach ($style_grades_sample as $sg_s) {
        $conn->query("INSERT INTO style_grades (plan_id, style, grade) VALUES ($last_id, '{$conn->real_escape_string($sg_s[0])}', '{$conn->real_escape_string($sg_s[1])}')");
    }

    // Insert goals
    $goals_sample = [
        ['Win URA Finale Qualifier', 'Pending'],
        ['Reach A-rank', 'Pending']
    ];
    foreach ($goals_sample as $goal_s) {
        $conn->query("INSERT INTO goals (plan_id, goal, result) VALUES ($last_id, '{$conn->real_escape_string($goal_s[0])}', '{$conn->real_escape_string($goal_s[1])}')");
    }

    // Insert turns
    $turns_sample = [
        ['1', 50, 40, 60, 30, 20],
        ['2', 55, 42, 65, 33, 22]
    ];
    foreach ($turns_sample as $turn_s) {
        $conn->query("INSERT INTO turns (plan_id, turn_number, speed, stamina, power, guts, wit) VALUES ($last_id, {$turn_s[0]}, {$turn_s[1]}, {$turn_s[2]}, {$turn_s[3]}, {$turn_s[4]}, {$turn_s[5]})");
    }

    // Insert activity log
    $conn->query("INSERT INTO activity_log (description, icon_class)
                 VALUES ('New sample plan created: [Bestest Prize] Haru Urara', 'bi-person-plus')");
}
?>