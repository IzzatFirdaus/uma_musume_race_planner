<?php
// plan-list.php
?>
<div class="card shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
        <i class="bi bi-card-checklist me-2"></i>
        Your Race Plans
    </h5>
    <button class="btn btn-sm btn-uma" id="createPlanBtn">
      <i class="bi bi-plus-circle me-1"></i> Create New
    </button>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="planTable">
        <thead class="table-light">
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Career Stage</th>
            <th scope="col">Class</th>
            <th scope="col">Next Race</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody id="planListBody">
          <?php if ($plans->rowCount() > 0) : ?>
                <?php foreach ($plans as $plan) : ?>
                    <?php
                    $statusClass = '';
                    switch ($plan['status']) {
                        case 'Planning':
                            $statusClass = 'bg-planning';
                            break;
                        case 'Active':
                            $statusClass = 'bg-active';
                            break;
                        case 'Finished':
                            $statusClass = 'bg-finished';
                            break;
                        case 'Draft':
                            $statusClass = 'bg-draft';
                            break;
                        case 'Abandoned':
                            $statusClass = 'bg-abandoned';
                            break;
                        default:
                            $statusClass = 'bg-secondary';
                            break;
                    }
                    ?>
              <tr>
                <td>
                    <strong><?= htmlspecialchars((string) $plan['plan_title'] ?: 'Untitled Plan') ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars((string) $plan['name']) ?></small>
                </td>
                <td><?= htmlspecialchars(ucfirst((string) $plan['career_stage'] ?: '')) ?></td>
                <td><?= htmlspecialchars(ucfirst((string) $plan['class'] ?: '')) ?></td>
                <td><?= htmlspecialchars((string) $plan['race_name'] ?: '') ?></td>
                <td>
                    <span class="badge <?= $statusClass ?> rounded-pill">
                        <?= htmlspecialchars((string) $plan['status']) ?>
                    </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?= $plan['id'] ?>">
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-info view-inline-btn me-1" data-id="<?= $plan['id'] ?>">
                    <i class="bi bi-eye"></i> View Details
                  </button>
                  <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $plan['id'] ?>">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
                <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="6" class="text-center text-muted p-4">
                No plans found. Click "Create New" to get started!
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
