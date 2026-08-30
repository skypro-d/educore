<div class="sa-top-bar">
    <div>
        <h1>Transport Management</h1>
        <p>Manage school transit routes, pickup coordinates, and monthly transport fees</p>
    </div>
    <div class="sa-top-actions">
        <button class="sa-btn sa-btn-primary" data-bs-toggle="modal" data-bs-target="#routeModal" onclick="clearRouteForm()"><i class="ti ti-plus"></i> Add New Route</button>
    </div>
</div>

<div class="sa-metrics" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px; display:grid; gap:16px;">
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-bus"></i> Active Buses</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">6</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Fleet units in use</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-map-pin"></i> Total Routes</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;"><?= count($routes) ?></div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Designated courses</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-users"></i> Commuters</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">182</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Subscribed students</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-activity"></i> Shift Status</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">Live</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Monitoring online</div></div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; align-items:start;">
    <!-- Routes list -->
    <div class="sa-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
        <div class="sa-card-title" style="font-size:16px; font-weight:700; margin-bottom:15px;"><i class="ti ti-map"></i> Active Routes</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Route Name</th>
                        <th>Stops / Pickup Points</th>
                        <th style="text-align:right;">Levy Amount</th>
                        <th style="text-align:right; width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($routes): foreach ($routes as $rt): ?>
                    <tr>
                        <td style="font-weight:600;"><?= e($rt['route_name']) ?></td>
                        <td style="color:#64748b; font-size:13px;"><?= e($rt['pickup_points'] ?: 'No stops configured') ?></td>
                        <td style="text-align:right;font-weight:700;color:#0b3d91;">₦<?= number_format((float)$rt['fee']) ?></td>
                        <td style="text-align:right;">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick='editRoute(<?= json_encode($rt) ?>)'>Edit</button>
                            <form method="POST" action="<?= url('admin/transport/delete/' . $rt['id']) ?>" onsubmit="return confirm('Delete this transit route?')" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:30px 0; color:#9ca3af;">No routes configured yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bus Fleet Card -->
    <div class="sa-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
        <div class="sa-card-title" style="font-size:16px; font-weight:700; margin-bottom:15px;"><i class="ti ti-truck"></i> Bus Fleet</div>
        <div style="font-size:12px;">
            <div style="border-bottom:1px solid #f1f5f9; padding:10px 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>Bus A (Toyota Coaster)</strong>
                    <div style="font-size:11px;color:#64748b;">Driver: Mr. Sunday (08012345678)</div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;background:#dcfce7;color:#15803d;">Active</span>
            </div>
            <div style="border-bottom:1px solid #f1f5f9; padding:10px 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>Bus B (Toyota Coaster)</strong>
                    <div style="font-size:11px;color:#64748b;">Driver: Mr. Ibrahim (08023456789)</div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;background:#dcfce7;color:#15803d;">Active</span>
            </div>
            <div style="border-bottom:1px solid #f1f5f9; padding:10px 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>Bus C (Nissan Civilian)</strong>
                    <div style="font-size:11px;color:#64748b;">Driver: Mr. Emeka (08034567890)</div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;background:#fee2e2;color:#b91c1c;">Servicing</span>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Route Modal -->
<div class="modal fade" id="routeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admin/transport') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="routeId">
            <div class="modal-header">
                <h5 class="modal-title" id="routeModalTitle">Add Transit Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Route Name</label>
                    <input type="text" name="route_name" id="routeName" class="form-control" required placeholder="e.g. Lekki-Ajah Express Route">
                </div>
                <div class="mb-3">
                    <label class="form-label">Stops / Waypoints (Comma separated)</label>
                    <textarea name="pickup_points" id="routePoints" class="form-control" rows="3" placeholder="Phase 1 Gate, Chevron, VGC, Ajah Roundabout"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Levy Fee Amount (₦)</label>
                    <input type="number" name="fee" id="routeFee" class="form-control" min="0" step="100" value="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Route</button>
            </div>
        </form>
    </div>
</div>

<script>
function clearRouteForm() {
    document.getElementById('routeId').value = '';
    document.getElementById('routeName').value = '';
    document.getElementById('routePoints').value = '';
    document.getElementById('routeFee').value = '0';
    document.getElementById('routeModalTitle').innerText = 'Add Transit Route';
}

function editRoute(rt) {
    document.getElementById('routeId').value = rt.id;
    document.getElementById('routeName').value = rt.route_name;
    document.getElementById('routePoints').value = rt.pickup_points || '';
    document.getElementById('routeFee').value = rt.fee;
    document.getElementById('routeModalTitle').innerText = 'Edit Transit Route Settings';
    
    var modal = new bootstrap.Modal(document.getElementById('routeModal'));
    modal.show();
}
</script>
