const BASE_PATH = '/uma_musume_race_planner/';

export const API = {
  GET_PLANS: `${BASE_PATH}get_plans.php`,
  SAVE_PLAN: `${BASE_PATH}save_plan.php`,
  DELETE_PLAN: `${BASE_PATH}delete_plan.php`,
  GET_PLAN_DETAILS: `${BASE_PATH}get_plan_details.php`,
  GET_STATS: `${BASE_PATH}get_stats.php`,
  GET_RECENT: `${BASE_PATH}get_recent_activity.php`,
  EXPORT_PLAN: `${BASE_PATH}export_plan.php`,
};

export const DATATYPES = {
  SKILL: 'skill',
  NAME: 'name',
  RACE_NAME: 'race_name',
};

export const attributeGradeOptions = ['F', 'E', 'D', 'C', 'B', 'A', 'S', 'SS', 'SS+'];
