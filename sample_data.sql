-- sample_data.sql for Laravel/Seeder compatible structure
-- Author: IzzatFirdaus
-- Last Updated: 2025-08-04

USE `uma_musume_planner`;

SET FOREIGN_KEY_CHECKS = 0;

-- Truncate and reset the main lookup tables and logs for fresh seed
TRUNCATE TABLE `activity_log`;
TRUNCATE TABLE `moods`;
TRUNCATE TABLE `conditions`;
TRUNCATE TABLE `strategies`;

-- Insert lookup values for moods
INSERT INTO `moods` (`id`, `label`) VALUES
(1, 'AWFUL'), (2, 'BAD'), (3, 'GOOD'), (4, 'GREAT'), (5, 'NORMAL'), (6, 'N/A');

-- Insert lookup values for conditions
INSERT INTO `conditions` (`id`, `label`) VALUES
(1, 'RAINY'), (2, 'SUNNY'), (3, 'WINDY'), (4, 'COLD'), (5, 'N/A'), (6, 'HOT TOPIC'), (7, 'CHARMING');

-- Insert lookup values for strategies
INSERT INTO `strategies` (`id`, `label`) VALUES
(1, 'FRONT'), (2, 'PACE'), (3, 'LATE'), (4, 'END'), (5, 'N/A');

-- Insert activity log sample records
INSERT INTO `activity_log` (`description`, `icon_class`) VALUES
('New sample plan created: [Bestest Prize 𝆕] Haru Urara Plan', 'bi-person-plus'),
('New sample plan created: [El☆Número 1] El Condor Pasa Plan', 'bi-person-plus'),
('New sample plan created: [Beyond the Horizon] Tokai Teio Plan', 'bi-person-plus'),
('New sample plan created: [Peak Blue] Daiwa Scarlet Plan', 'bi-person-plus'),
('New sample plan created: [Wild Top Gear] Vodka Plan (1st)', 'bi-person-plus'),
('New sample plan created: [pf. Winning Equation…] Biwa Hayahide Plan', 'bi-person-plus');

SET FOREIGN_KEY_CHECKS = 1;

-- Note: All further sample data such as plans, attributes, skills, goals, etc.
-- should be loaded via Laravel Seeder classes (PlanSeeder, SkillReferenceSeeder, etc.)
-- This script provides only lookup and demo records for quick verification.
