<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Reports & Analytics</h1>
    <button class="btn btn-outline-primary" onclick="window.print()">Print / PDF Report</button>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric"><span>Total Applications</span><strong><?= e((string) $stats['total']) ?></strong></div></div>
    <div class="col-md-3"><div class="metric"><span>Approved</span><strong><?= e((string) $stats['approved']) ?></strong></div></div>
    <div class="col-md-3"><div class="metric"><span>Enrolled</span><strong><?= e((string) $stats['enrolled']) ?></strong></div></div>
    <div class="col-md-3"><div class="metric"><span>Revenue</span><strong>NGN <?= number_format((float) $stats['revenue']) ?></strong></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-6"><div class="panel"><h2>Application Statistics</h2><canvas id="classChart" data-labels='<?= e(json_encode(array_column($byClass, 'label'))) ?>' data-values='<?= e(json_encode(array_column($byClass, 'total'))) ?>'></canvas></div></div>
    <div class="col-lg-6"><div class="panel"><h2>Monthly Applications</h2><canvas id="monthChart" data-labels='<?= e(json_encode(array_column($byMonth, 'label'))) ?>' data-values='<?= e(json_encode(array_column($byMonth, 'total'))) ?>'></canvas></div></div>
</div>

