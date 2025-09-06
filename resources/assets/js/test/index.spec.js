// --- Test Suite Refactor ---
// Real API tests use node-fetch for HTTP requests
// Mock API tests use axios (imported below)
// Comments added for maintainability
// Use node-fetch for all real API tests
const fetch = require('node-fetch');
// Use axios only for mock API tests
const axios = require('axios');
jest.mock('axios');

// --- Real API Tests ---
test('planner: fetch plans from real API', async () => {
  const response = await fetch('http://localhost:8000/api/v1/plans');
  expect(response.status).toBe(200);
  const plans = await response.json();
  expect(Array.isArray(plans)).toBe(true);
  expect(plans.length).toBeGreaterThan(0);
});

test('dashboard: fetch stats from real API', async () => {
  const response = await fetch('http://localhost:8000/api/v1/dashboard/stats');
  expect(response.status).toBe(200);
  const stats = await response.json();
  expect(stats).toHaveProperty('total_plans');
});

test('autosuggest: fetch suggestions from real API (missing fields)', async () => {
  const response = await fetch('http://localhost:8000/api/v1/autosuggest');
  expect(response.status).toBe(400);
  const result = await response.json();
  expect(result).toHaveProperty('success');
  expect(result.success).toBe(false);
  expect(result).toHaveProperty('error');
});

// --- Mock API Tests (using axios) ---
test('planner: create, update, delete plan via mock API', async () => {
  // Mock create
  axios.post.mockResolvedValueOnce({ data: { id: 1, name: 'Test Plan', date: '2025-09-20' } });
  const createRes = await axios.post('/api/v1/plans', { name: 'Test Plan', date: '2025-09-20' });
  expect(createRes.data.name).toBe('Test Plan');

  // Mock update
  axios.put.mockResolvedValueOnce({ data: { id: 1, name: 'Updated Plan', date: '2025-09-21' } });
  const updateRes = await axios.put('/api/v1/plans/1', { name: 'Updated Plan', date: '2025-09-21' });
  expect(updateRes.data.name).toBe('Updated Plan');

  // Mock delete
  axios.delete.mockResolvedValueOnce({ data: { success: true } });
  const deleteRes = await axios.delete('/api/v1/plans/1');
  expect(deleteRes.data.success).toBe(true);
});

test('planner: handle mock API error on create', async () => {
  axios.post.mockRejectedValueOnce(new Error('API error'));
  try {
    await axios.post('/api/v1/plans', { name: 'Bad Plan' });
  } catch (e) {
    expect(e).toBeInstanceOf(Error);
    expect(e.message).toBe('API error');
  }
});

test('planner: fetch and filter plans by date (mock)', async () => {
  const plans = [
    { id: 1, name: 'A', date: '2025-09-10' },
    { id: 2, name: 'B', date: '2025-09-22' }
  ];
  axios.get.mockResolvedValueOnce({ data: plans });
  const res = await axios.get('/api/v1/plans');
  const upcoming = res.data.filter(p => p.date > '2025-09-15');
  expect(upcoming.length).toBe(1);
  expect(upcoming[0].name).toBe('B');
});

// --- Planner/Business Logic Tests ---
test('planner: validate plan before API call', () => {
  function validate(plan) {
    if (!plan.name || !plan.date) return false;
    if (isNaN(Date.parse(plan.date))) return false;
    return true;
  }
  expect(validate({ name: 'Valid', date: '2025-09-10' })).toBe(true);
  expect(validate({ name: '', date: '2025-09-10' })).toBe(false);
  expect(validate({ name: 'Valid', date: 'bad-date' })).toBe(false);
});

// Add more planner/business logic tests for robustness as needed below
// Example: Plan overlap detection
test('plan: detect overlapping race dates', () => {
  const plans = [
    { name: 'Plan A', date: '2025-09-10' },
    { name: 'Plan B', date: '2025-09-10' },
    { name: 'Plan C', date: '2025-09-12' }
  ];
  const dates = plans.map(p => p.date);
  expect(dates.length !== new Set(dates).size).toBe(true);
});

// Example: Activity log sorting
test('activity log: sort by timestamp', () => {
  const logs = [
    { id: 1, timestamp: 100 },
    { id: 2, timestamp: 50 },
    { id: 3, timestamp: 150 }
  ];
  const sorted = logs.sort((a, b) => a.timestamp - b.timestamp);
  expect(sorted[0].id).toBe(2);
  expect(sorted[2].id).toBe(3);
});

// Example: Skill reference validation
test('skill reference: validate skill IDs', () => {
  const validIds = [1, 2, 3];
  function isValidSkill(id) {
    return validIds.includes(id);
  }
  expect(isValidSkill(2)).toBe(true);
  expect(isValidSkill(99)).toBe(false);
});

// Example: Strategy assignment
test('strategy: assign to plan', () => {
  const strategies = [
    { id: 1, label: 'Aggressive' },
    { id: 2, label: 'Balanced' }
  ];
  const plan = { name: 'Plan X', strategyId: 2 };
  const assigned = strategies.find(s => s.id === plan.strategyId)?.label;
  expect(assigned).toBe('Balanced');
});

// Example: Race prediction calculation
test('race prediction: calculate win chance', () => {
  function predictWin(attrs) {
    return attrs.speed + attrs.stamina + attrs.power > 50 ? 'Win' : 'Lose';
  }
  expect(predictWin({ speed: 20, stamina: 20, power: 15 })).toBe('Win');
  expect(predictWin({ speed: 10, stamina: 20, power: 15 })).toBe('Lose');
});

// Example: Edge case for empty plans
test('plan: handle empty plans array', () => {
  const plans = [];
  expect(plans.length).toBe(0);
});

// Example: Missing required fields
test('plan: missing required fields', () => {
  function isValid(plan) {
    return plan.name && plan.date;
  }
  expect(isValid({ name: 'A' })).toBeFalsy();
  expect(isValid({ date: '2025-09-10' })).toBeFalsy();
});
// Real API call: fetch plans (requires backend running)
// Complex planner flow: create, update, delete plan via API (mocked)


test('planner: create, update, delete plan via API', async () => {
  // Mock create
  axios.post.mockResolvedValueOnce({ data: { id: 1, name: 'Test Plan', date: '2025-09-20' } });
  const createRes = await axios.post('/api/v1/plans', { name: 'Test Plan', date: '2025-09-20' });
  expect(createRes.data.name).toBe('Test Plan');

  // Mock update
  axios.put.mockResolvedValueOnce({ data: { id: 1, name: 'Updated Plan', date: '2025-09-21' } });
  const updateRes = await axios.put('/api/v1/plans/1', { name: 'Updated Plan', date: '2025-09-21' });
  expect(updateRes.data.name).toBe('Updated Plan');

  // Mock delete
  axios.delete.mockResolvedValueOnce({ data: { success: true } });
  const deleteRes = await axios.delete('/api/v1/plans/1');
  expect(deleteRes.data.success).toBe(true);
});

// API error scenario: fail to create plan
test('planner: handle API error on create', async () => {
  axios.post.mockRejectedValueOnce(new Error('API error'));
  try {
    await axios.post('/api/v1/plans', { name: 'Bad Plan' });
  } catch (e) {
    expect(e).toBeInstanceOf(Error);
    expect(e.message).toBe('API error');
  }
});

// Planner logic with real-world data: fetch plans and filter
test('planner: fetch and filter plans by date', async () => {
  const plans = [
    { id: 1, name: 'A', date: '2025-09-10' },
    { id: 2, name: 'B', date: '2025-09-22' }
  ];
  axios.get.mockResolvedValueOnce({ data: plans });
  const res = await axios.get('/api/v1/plans');
  const upcoming = res.data.filter(p => p.date > '2025-09-15');
  expect(upcoming.length).toBe(1);
  expect(upcoming[0].name).toBe('B');
});
// Plan overlap detection
test('plan: detect overlapping race dates', () => {
  const plans = [
    { name: 'Plan A', date: '2025-09-10' },
    { name: 'Plan B', date: '2025-09-10' },
    { name: 'Plan C', date: '2025-09-12' }
  ];
  function hasOverlap(plans) {
    const dates = plans.map(p => p.date);
    return dates.length !== new Set(dates).size;
  }
  expect(hasOverlap(plans)).toBe(true);
  expect(hasOverlap([plans[0], plans[2]])).toBe(false);
});

// Activity log sorting by timestamp
test('activity log: sort by timestamp', () => {
  const logs = [
    { id: 1, timestamp: 100 },
    { id: 2, timestamp: 50 },
    { id: 3, timestamp: 150 }
  ];
  const sorted = logs.sort((a, b) => a.timestamp - b.timestamp);
  expect(sorted[0].id).toBe(2);
  expect(sorted[2].id).toBe(3);
});

// Skill reference ID validation
test('skill reference: validate skill IDs', () => {
  const validIds = [1, 2, 3];
  function isValidSkill(id) {
    return validIds.includes(id);
  }
  expect(isValidSkill(2)).toBe(true);
  expect(isValidSkill(99)).toBe(false);
});

// Strategy assignment to plan
test('strategy: assign to plan', () => {
  const strategies = [
    { id: 1, label: 'Aggressive' },
    { id: 2, label: 'Balanced' }
  ];
  const plan = { name: 'Plan X', strategyId: 2 };
  const assigned = strategies.find(s => s.id === plan.strategyId)?.label;
  expect(assigned).toBe('Balanced');
});

// Race prediction calculation
test('race prediction: calculate win chance', () => {
  function predictWin(attrs) {
    // Simple mock: sum attributes, win if > 50
    return attrs.speed + attrs.stamina + attrs.power > 50 ? 'Win' : 'Lose';
  }
  expect(predictWin({ speed: 20, stamina: 20, power: 15 })).toBe('Win');
  expect(predictWin({ speed: 10, stamina: 20, power: 15 })).toBe('Lose');
  expect(predictWin({ speed: 30, stamina: 25, power: 10 })).toBe('Win');
});

// Edge case: empty plans
test('plan: handle empty plans array', () => {
  const plans = [];
  expect(plans.length).toBe(0);
});

// Edge case: missing fields
test('plan: missing required fields', () => {
  function isValid(plan) {
    return plan.name && plan.date;
  }
  expect(isValid({ name: 'A' })).toBeFalsy();
  expect(isValid({ date: '2025-09-10' })).toBeFalsy();
});

// Async planner API simulation
test('planner: async create plan', async () => {
  const api = {
    createPlan: jest.fn().mockResolvedValue({ success: true, plan: { name: 'New Plan', date: '2025-09-15' } })
  };
  const result = await api.createPlan({ name: 'New Plan', date: '2025-09-15' });
  expect(api.createPlan).toHaveBeenCalledWith({ name: 'New Plan', date: '2025-09-15' });
  expect(result.success).toBe(true);
  expect(result.plan.name).toBe('New Plan');
});
// Async test: simulate fetching plans from API
test('planner: async fetch plans', async () => {
  function fetchPlans() {
    return new Promise(resolve => {
      setTimeout(() => {
        resolve([
          { name: 'Spring Cup', date: '2025-03-01' },
          { name: 'Summer Derby', date: '2025-07-15' }
        ]);
      }, 50);
    });
  }
  const plans = await fetchPlans();
  expect(plans.length).toBe(2);
  expect(plans[0].name).toBe('Spring Cup');
});

// Mock API: update plan
test('planner: mock API update plan', async () => {
  const api = {
    updatePlan: jest.fn().mockResolvedValue({ success: true, updated: { name: 'Training', days: 10 } })
  };
  const result = await api.updatePlan({ name: 'Training', days: 10 });
  expect(api.updatePlan).toHaveBeenCalledWith({ name: 'Training', days: 10 });
  expect(result.success).toBe(true);
  expect(result.updated.days).toBe(10);
});

// Error handling: failed API call
test('planner: handle failed API call', async () => {
  const api = {
    fetchPlan: jest.fn().mockRejectedValue(new Error('Network error'))
  };
  try {
    await api.fetchPlan(1);
  } catch (e) {
    expect(e).toBeInstanceOf(Error);
    expect(e.message).toBe('Network error');
  }
});
// Planner validation: required fields and valid dates
test('planner: validate plan fields', () => {
  function isValidPlan(plan) {
    return Boolean(plan.name) && Boolean(plan.date) && !isNaN(Date.parse(plan.date));
  }
  expect(isValidPlan({ name: 'Race', date: '2025-09-10' })).toBe(true);
  expect(isValidPlan({ name: '', date: '2025-09-10' })).toBe(false);
  expect(isValidPlan({ name: 'Race', date: 'invalid-date' })).toBe(false);
});

// Activity log filtering
test('activity log: filter by type', () => {
  const logs = [
    { type: 'create', id: 1 },
    { type: 'update', id: 2 },
    { type: 'delete', id: 3 }
  ];
  const updates = logs.filter(l => l.type === 'update');
  expect(updates.length).toBe(1);
  expect(updates[0].id).toBe(2);
});

// Skill reference lookup
test('skill reference: find skill by id', () => {
  const skills = [
    { id: 1, name: 'Speed Up' },
    { id: 2, name: 'Stamina Boost' }
  ];
  function findSkill(id) {
    return skills.find(s => s.id === id)?.name || null;
  }
  expect(findSkill(2)).toBe('Stamina Boost');
  expect(findSkill(99)).toBeNull();
});

// Strategy selection logic
test('strategy: select best strategy', () => {
  const strategies = [
    { name: 'Aggressive', score: 80 },
    { name: 'Balanced', score: 90 },
    { name: 'Defensive', score: 70 }
  ];
  const best = strategies.reduce((a, b) => (a.score > b.score ? a : b));
  expect(best.name).toBe('Balanced');
});

// Edge case: handle empty plans
test('planner: handle empty plans array', () => {
  const plans = [];
  const nextPlan = plans[0] || null;
  expect(nextPlan).toBeNull();
});
test('it works', () => {
  expect(1 + 1).toBe(2);
});

test('math: negative numbers', () => {
  expect(-5 + 3).toBe(-2);
  expect(2 * -4).toBe(-8);
});

test('string: concatenation', () => {
  expect('Uma' + 'Musume').toBe('UmaMusume');
});

test('array: length and includes', () => {
  const arr = [1, 2, 3];
  expect(arr.length).toBe(3);
  expect(arr.includes(2)).toBe(true);
});

test('object: property access', () => {
  const obj = { name: 'Planner', year: 2025 };
  expect(obj.name).toBe('Planner');
  expect(obj.year).toBe(2025);
});

test('edge: null and undefined', () => {
  expect(null).toBeNull();
  expect(undefined).toBeUndefined();
});

// Domain-relevant tests for race planner logic
test('planner: filter upcoming races', () => {
  const races = [
    { name: 'Spring Cup', date: '2025-03-01' },
    { name: 'Summer Derby', date: '2025-07-15' },
    { name: 'Autumn Stakes', date: '2024-10-10' }
  ];
  const today = '2025-01-01';
  const upcoming = races.filter(r => r.date > today);
  expect(upcoming.length).toBe(2);
  expect(upcoming.map(r => r.name)).toContain('Spring Cup');
  expect(upcoming.map(r => r.name)).toContain('Summer Derby');
});

test('planner: sort plans by priority', () => {
  const plans = [
    { name: 'A', priority: 2 },
    { name: 'B', priority: 1 },
    { name: 'C', priority: 3 }
  ];
  const sorted = plans.sort((a, b) => a.priority - b.priority);
  expect(sorted[0].name).toBe('B');
  expect(sorted[2].name).toBe('C');
});

test('planner: update plan details', () => {
  const plan = { name: 'Training', days: 5 };
  const updated = { ...plan, days: 7 };
  expect(updated.days).toBe(7);
  expect(updated.name).toBe('Training');
});

test('planner: find max attribute', () => {
  const attrs = [12, 18, 7, 25, 10];
  const max = Math.max(...attrs);
  expect(max).toBe(25);
});

test('planner: calculate days until race', () => {
  const today = new Date('2025-01-01');
  const raceDay = new Date('2025-01-10');
  const diff = (raceDay - today) / (1000 * 60 * 60 * 24);
  expect(diff).toBe(9);
});
