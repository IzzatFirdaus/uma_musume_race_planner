<div class="card mb-4">
  <div class="card-header">Quick Create Plan</div>
  <div class="card-body">
    <form id="quickPlanForm">
      <!-- Trainee Name -->
      <div class="mb-3">
        <label class="form-label" for="name">Trainee Name</label>
        <input
          type="text"
          class="form-control"
          id="name"
          name="name"
          list="nameSuggestions"
          placeholder="e.g. [Bestest Prize 𝆕] Haru Urara"
          required
        />
      </div>

      <!-- Career Stage -->
      <div class="mb-3">
        <label class="form-label" for="career_stage">Career Stage</label>
        <select class="form-select" id="career_stage" name="career_stage" required>
          <option value="predebut">Pre-Debut</option>
          <option value="junior">Junior</option>
          <option value="classic">Classic</option>
          <option value="senior">Senior</option>
          <option value="finale">Finale</option>
        </select>
      </div>

      <!-- Class -->
      <div class="mb-3">
        <label class="form-label" for="class">Class</label>
        <select class="form-select" id="class" name="class" required>
          <option value="debut">Debut</option>
          <option value="maiden">Maiden</option>
          <option value="beginner">Beginner</option>
          <option value="bronze">Bronze</option>
          <option value="silver">Silver</option>
          <option value="gold">Gold</option>
          <option value="platinum">Platinum</option>
          <option value="star">Star</option>
          <option value="legend">Legend</option>
        </select>
      </div>

      <!-- Race Name -->
      <div class="mb-3">
        <label class="form-label" for="race_name">Race Name</label>
        <input
          type="text"
          class="form-control"
          id="race_name"
          name="race_name"
          list="raceNameSuggestions"
          placeholder="e.g. URA Finals"
        />
      </div>

      <!-- Default Plan Status -->
      <input type="hidden" name="status" value="Planning" />

      <!-- Submit -->
      <button type="submit" class="btn btn-uma w-100">
        <i class="bi bi-plus-circle me-1"></i> Create Plan
      </button>
    </form>
  </div>
</div>

<!-- Autosuggest Lists -->
<datalist id="nameSuggestions"></datalist>
<datalist id="raceNameSuggestions"></datalist>
