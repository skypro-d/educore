<div class="sa-top-bar">
    <div>
        <h1>Library Management</h1>
        <p>Manage school library books catalog, lending log, and book inventory details</p>
    </div>
    <div class="sa-top-actions">
        <button class="sa-btn sa-btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal" onclick="clearBookForm()"><i class="ti ti-plus"></i> Add New Book</button>
    </div>
</div>

<div class="sa-metrics" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px; display:grid; gap:16px;">
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-books"></i> Total Catalog</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;"><?= count($books) ?></div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Registered books</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-bookmark-share"></i> Active Borrowings</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;"><?= count($borrowings) ?></div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Currently borrowed</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-alert-triangle"></i> Overdue Logs</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">0</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Exceeded limits</div></div>
    <div class="sa-metric-card" style="background:#fff; padding:16px; border-radius:8px; border:1px solid #e5e7eb;"><div class="label" style="font-size:12px; color:#6b7280;"><i class="ti ti-coin"></i> Library Fines</div><div class="value" style="font-size:24px; font-weight:700; color:#111827; margin-top:4px;">₦0.00</div><div class="sub" style="font-size:10px; color:#9ca3af; margin-top:2px;">Accumulated fines</div></div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; align-items:start;">
    <!-- Catalog list -->
    <div class="sa-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
        <div class="sa-card-title" style="font-size:16px; font-weight:700; margin-bottom:15px;"><i class="ti ti-list"></i> Books Catalog</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Title / Author</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th style="text-align:center;">Copies Available</th>
                        <th>Shelf Location</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books): foreach ($books as $bk): ?>
                    <tr>
                        <td>
                            <strong><?= e($bk['title']) ?></strong><br>
                            <span style="font-size:11px;color:#64748b;"><?= e($bk['author']) ?> (<?= e($bk['publisher']) ?>)</span>
                        </td>
                        <td style="color:#64748b; font-family:monospace;"><?= e($bk['isbn'] ?: 'N/A') ?></td>
                        <td><?= e($bk['category'] ?: 'General') ?></td>
                        <td style="text-align:center;font-weight:700;color:#16a34a;"><?= (int)$bk['available_copies'] ?> / <?= (int)$bk['total_copies'] ?></td>
                        <td><?= e($bk['location'] ?: 'Unassigned') ?></td>
                        <td style="text-align:right;">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick='editBook(<?= json_encode($bk) ?>)'>Edit</button>
                            <form method="POST" action="<?= url('admin/library/delete/' . $bk['id']) ?>" onsubmit="return confirm('Remove this book from catalog?')" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px 0; color:#9ca3af;">No books registered in the catalog yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active borrow log -->
    <div class="sa-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
        <div class="sa-card-title" style="font-size:16px; font-weight:700; margin-bottom:15px;"><i class="ti ti-history"></i> Recent Borrowings</div>
        <div style="font-size:12px;">
            <?php if ($borrowings): foreach ($borrowings as $b): ?>
            <div style="border-bottom:1px solid #f1f5f9; padding:10px 0;">
                <div style="display:flex; justify-content:space-between; font-weight:600;">
                    <span><?= e($b['first_name'] . ' ' . $b['last_name']) ?></span>
                    <span class="badge bg-light text-success"><?= ucfirst(e($b['status'])) ?></span>
                </div>
                <div style="color:#64748b; margin-top:2px;"><?= e($b['book_title']) ?></div>
                <div style="font-size:10px; color:#94a3b8; margin-top:4px;">Due: <?= date('M j, Y', strtotime($b['due_date'])) ?></div>
            </div>
            <?php endforeach; else: ?>
            <p style="color:#9ca3af; text-align:center; padding:20px 0; font-style:italic;">No active borrow records.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Book Modal -->
<div class="modal fade" id="bookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admin/library') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="bookId">
            <div class="modal-header">
                <h5 class="modal-title" id="bookModalTitle">Add Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" id="bookTitle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" id="bookAuthor" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" id="bookIsbn" class="form-control" placeholder="e.g. 978-...">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Publisher</label>
                        <input type="text" name="publisher" id="bookPublisher" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Year Published</label>
                        <input type="number" name="year_published" id="bookYear" class="form-control" value="<?= date('Y') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" id="bookCategory" class="form-control" placeholder="e.g. Mathematics">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Total Copies</label>
                        <input type="number" name="total_copies" id="bookTotal" class="form-control" min="1" value="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Shelf Location</label>
                        <input type="text" name="location" id="bookLocation" class="form-control" placeholder="e.g. Cabinet A, Shelf 1">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Book</button>
            </div>
        </form>
    </div>
</div>

<script>
function clearBookForm() {
    document.getElementById('bookId').value = '';
    document.getElementById('bookTitle').value = '';
    document.getElementById('bookAuthor').value = '';
    document.getElementById('bookIsbn').value = '';
    document.getElementById('bookPublisher').value = '';
    document.getElementById('bookYear').value = '<?= date('Y') ?>';
    document.getElementById('bookCategory').value = '';
    document.getElementById('bookTotal').value = '1';
    document.getElementById('bookLocation').value = '';
    document.getElementById('bookModalTitle').innerText = 'Add Book to Catalog';
}

function editBook(bk) {
    document.getElementById('bookId').value = bk.id;
    document.getElementById('bookTitle').value = bk.title;
    document.getElementById('bookAuthor').value = bk.author;
    document.getElementById('bookIsbn').value = bk.isbn || '';
    document.getElementById('bookPublisher').value = bk.publisher || '';
    document.getElementById('bookYear').value = bk.year_published;
    document.getElementById('bookCategory').value = bk.category || '';
    document.getElementById('bookTotal').value = bk.total_copies;
    document.getElementById('bookLocation').value = bk.location || '';
    document.getElementById('bookModalTitle').innerText = 'Edit Book Settings';
    
    // Show Modal
    var modal = new bootstrap.Modal(document.getElementById('bookModal'));
    modal.show();
}
</script>
