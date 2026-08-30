<div class="sa-top-bar">
    <div>
        <h1>Inventory & Assets</h1>
        <p>Track school stock logs, textbook packages, uniforms, furniture, and stationery items</p>
    </div>
    <div class="sa-top-actions">
        <button class="sa-btn sa-btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="clearItemForm()"><i class="ti ti-plus"></i> Add New Item</button>
    </div>
</div>

<div class="sa-metrics" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px; display:grid; gap:16px;">
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-archive"></i> Catalog Types</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;"><?= count($items) ?></div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Registered asset types</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-alert-triangle"></i> Reorder Items</div><div class="value" style="font-size:24px; font-weight:700; color:#dc2626; margin-top:4px;">0</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Below reorder levels</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-shopping-cart"></i> Stock Handled</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">342</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Dispatched this month</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-coin"></i> Stored Value</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">₦2.4M</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Asset catalog net worth</div></div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; align-items:start;">
    <!-- Inventory Items list -->
    <div class="sa-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
        <div class="sa-card-title" style="font-size:16px; font-weight:700; margin-bottom:15px;"><i class="ti ti-clipboard-list"></i> Current Stock Registry</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th style="text-align:center;">Quantity</th>
                        <th>Unit</th>
                        <th style="text-align:right;">Unit Cost</th>
                        <th>Storage Location</th>
                        <th style="text-align:right; width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items): foreach ($items as $it): ?>
                    <tr>
                        <td style="font-weight:600;"><?= e($it['item_name']) ?></td>
                        <td><?= e($it['category']) ?></td>
                        <td style="text-align:center;font-weight:700;color:#16a34a;"><?= (int)$it['quantity'] ?></td>
                        <td style="color:#64748b;"><?= e($it['unit'] ?: 'pcs') ?></td>
                        <td style="text-align:right;font-weight:700;color:#0b3d91;">₦<?= number_format((float)$it['unit_cost']) ?></td>
                        <td><?= e($it['location'] ?: 'Warehouse') ?></td>
                        <td style="text-align:right;">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick='editItem(<?= json_encode($it) ?>)'>Edit</button>
                            <form method="POST" action="<?= url('admin/inventory/delete/' . $it['id']) ?>" onsubmit="return confirm('Delete this stock item?')" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px 0; color:#9ca3af;">No items registered in the inventory catalog.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent transactions -->
    <div class="sa-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
        <div class="sa-card-title" style="font-size:16px; font-weight:700; margin-bottom:15px;"><i class="ti ti-receipt"></i> Recent Stock Logs</div>
        <div style="font-size:12px;">
            <div style="border-bottom:1px solid #f1f5f9; padding:10px 0;">
                <div style="display:flex; justify-content:space-between; font-weight:600;">
                    <span>Restock: Marker Boxes</span>
                    <span style="color:#16a34a;">+50 boxes</span>
                </div>
                <div style="color:#64748b; margin-top:2px;">Supplier: OfficeMax Ltd.</div>
                <div style="font-size:10px; color:#94a3b8; margin-top:4px;">Date: Jun 16, 2026</div>
            </div>
            <div style="border-bottom:1px solid #f1f5f9; padding:10px 0;">
                <div style="display:flex; justify-content:space-between; font-weight:600;">
                    <span>Issued: Blazer (Medium)</span>
                    <span style="color:#b91c1c;">-1 pc</span>
                </div>
                <div style="color:#64748b; margin-top:2px;">Issued to: David Adebayo (SS1)</div>
                <div style="font-size:10px; color:#94a3b8; margin-top:4px;">Date: Jun 14, 2026</div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admin/inventory') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="itemId">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalTitle">Add Stock Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="item_name" id="itemName" class="form-control" required placeholder="e.g. Whiteboard Markers">
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" id="itemCategory" class="form-select" required>
                        <option value="Books">Books</option>
                        <option value="Uniform">Uniform</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Sports">Sports</option>
                        <option value="Stationery">Stationery</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="itemQuantity" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unit Measure</label>
                        <input type="text" name="unit" id="itemUnit" class="form-control" placeholder="e.g. pcs, boxes" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Unit Cost (₦)</label>
                        <input type="number" name="unit_cost" id="itemCost" class="form-control" min="0" step="100" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="supplier" id="itemSupplier" class="form-control" placeholder="e.g. Shoprite Ltd">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Storage Location</label>
                    <input type="text" name="location" id="itemLocation" class="form-control" placeholder="e.g. Store 1, Shelf B">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>

<script>
function clearItemForm() {
    document.getElementById('itemId').value = '';
    document.getElementById('itemName').value = '';
    document.getElementById('itemCategory').value = 'Stationery';
    document.getElementById('itemQuantity').value = '0';
    document.getElementById('itemUnit').value = 'pcs';
    document.getElementById('itemCost').value = '0';
    document.getElementById('itemSupplier').value = '';
    document.getElementById('itemLocation').value = '';
    document.getElementById('itemModalTitle').innerText = 'Add Stock Item';
}

function editItem(it) {
    document.getElementById('itemId').value = it.id;
    document.getElementById('itemName').value = it.item_name;
    document.getElementById('itemCategory').value = it.category;
    document.getElementById('itemQuantity').value = it.quantity;
    document.getElementById('itemUnit').value = it.unit || 'pcs';
    document.getElementById('itemCost').value = it.unit_cost;
    document.getElementById('itemSupplier').value = it.supplier || '';
    document.getElementById('itemLocation').value = it.location || '';
    document.getElementById('itemModalTitle').innerText = 'Edit Stock Item Details';
    
    var modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
}
</script>
