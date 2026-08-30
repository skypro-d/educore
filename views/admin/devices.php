<?php
/**
 * views/admin/devices.php
 * School Administrator POS Device Dashboard
 */
?>

<div class="sa-top-bar">
    <div>
        <h1>POS Scanner Devices</h1>
        <p>Register, monitor, and revoke Android POS scanning terminals and QR gate devices</p>
    </div>
    <div class="sa-top-actions">
        <button class="sa-btn sa-btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
            <i class="ti ti-plus"></i> Pre-Register Device
        </button>
    </div>
</div>

<div class="sa-card">
    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Device Details</th>
                    <th>Hardware Telemetry</th>
                    <th>Location / Gate</th>
                    <th>Activation Code</th>
                    <th>Last Active</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($devices)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 40px; color:#9ca3af;">
                            <i class="ti ti-device-nfc" style="font-size:36px; display:block; margin-bottom:10px;"></i>
                            No POS scanning devices registered. Click "Pre-Register Device" to provision an activation key.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($devices as $dev): ?>
                        <?php 
                            $lastSeen = $dev['last_seen'] ? strtotime($dev['last_seen']) : 0;
                            $isOnline = (time() - $lastSeen) <= 300; // Online in last 5 mins
                            $statusCls = $dev['status'] === 'active' ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700; font-size:14px; color:#1f2937;"><?= e($dev['device_name']) ?></div>
                                <?php if ($dev['device_token']): ?>
                                    <div style="font-size:10px; font-family:monospace; color:#9ca3af; margin-top:2px;">Token: <?= e(substr($dev['device_token'], 0, 16)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($dev['device_model']): ?>
                                    <div style="font-size:12px; font-weight:600;"><?= e($dev['device_model']) ?> (Android <?= e($dev['android_version']) ?>)</div>
                                    <div style="font-size:10px; color:#9ca3af; margin-top:1px;">SN: <?= e($dev['serial_number'] ?: 'N/A') ?></div>
                                    <?php if ($dev['battery_level'] !== null): ?>
                                        <div style="font-size:11px; margin-top:3px; display:flex; align-items:center; gap:4px;">
                                            <i class="ti ti-battery-4" style="color:var(--brand-primary);"></i> <?= (int) $dev['battery_level'] ?>%
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#9ca3af; font-style:italic; font-size:12px;">Waiting for first login</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-weight:600; font-size:13px; color:#4b5563;"><i class="ti ti-map-pin"></i> <?= e($dev['location'] ?: 'Main Gate') ?></span>
                            </td>
                            <td>
                                <?php if ($dev['activation_code']): ?>
                                    <span class="badge bg-warning text-dark font-monospace" style="font-size:13px; padding:6px 12px; border:1px solid #f59e0b;"><?= e($dev['activation_code']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted" style="border:1px solid #d1d5db;">Activated</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($dev['last_seen']): ?>
                                    <div style="font-size:12px; font-weight:600; color: <?= $isOnline ? '#16a34a' : 'inherit' ?>;">
                                        <?= $isOnline ? '● Online Now' : date('M j, g:i A', $lastSeen) ?>
                                    </div>
                                    <?php if ($dev['last_scan_time']): ?>
                                        <div style="font-size:10px; color:#9ca3af; margin-top:2px;">Last Scan: <?= date('g:i A', strtotime($dev['last_scan_time'])) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#9ca3af; font-style:italic;">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $statusCls ?>"><?= ucfirst(e($dev['status'])) ?></span>
                            </td>
                            <td style="text-align:right;">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:11px; padding:4px 10px;">
                                        Options
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editDeviceModal<?= $dev['id'] ?>">
                                                <i class="ti ti-edit me-2"></i> Edit Settings
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="<?= url('admin/devices/status/' . $dev['id']) ?>" onsubmit="return confirm('Toggle scanner activation?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ti ti-power me-2"></i> <?= $dev['status'] === 'active' ? 'Block Scanner' : 'Activate' ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="<?= url('admin/devices/reset-token/' . $dev['id']) ?>" onsubmit="return confirm('WARNING: Revoking token logs out the terminal and invalidates all current sessions. Force logout?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="ti ti-refresh me-2"></i> Revoke Token & Logout
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= url('admin/devices/delete/' . $dev['id']) ?>" onsubmit="return confirm('Delete this device profile? It cannot connect again without re-registration.')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ti ti-trash me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Edit Device Modal -->
                                <div class="modal fade text-start" id="editDeviceModal<?= $dev['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Device Settings</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="<?= url('admin/devices') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="device_id" value="<?= $dev['id'] ?>">
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Device Name</label>
                                                        <input type="text" name="device_name" class="form-control" required value="<?= e($dev['device_name']) ?>" placeholder="e.g. Main Gate POS">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Location / Gate</label>
                                                        <input type="text" name="location" class="form-control" value="<?= e($dev['location']) ?>" placeholder="e.g. Admin Reception, North Gate">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pre-Register Attendance POS Terminal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('admin/devices') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p style="font-size:13px; color:#6b7280; margin-bottom:16px;">
                        Pre-registering creates an activation key profile. Provide the generated activation key during first POS boot to establish trust.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Device Name</label>
                        <input type="text" name="device_name" class="form-control" required placeholder="e.g. Reception Scanner, North Gate POS">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location / Gate</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Main Entrance, Senior Wing Gate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Activation Code</button>
                </div>
            </form>
        </div>
    </div>
</div>
