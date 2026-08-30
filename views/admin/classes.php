<h1 class="h3 mb-4">Class Management</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <form class="panel" method="post" action="<?= url('admin/classes') ?>">
            <?= csrf_field() ?>
            <h2>Add / Edit Class</h2>
            <input type="hidden" name="id" id="classId">
            <label class="form-label">Class Name</label>
            <input class="form-control mb-3" name="name" id="className" required>
            <label class="form-label">Sort Order</label>
            <input class="form-control mb-3" type="number" name="sort_order" id="classSort" value="0">
            <button class="btn btn-primary">Save Class</button>
        </form>
    </div>
    <div class="col-lg-8">
        <div class="panel">
            <table class="table align-middle"><thead><tr><th>Name</th><th>Order</th><th></th></tr></thead><tbody>
            <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?= e($class['name']) ?></td><td><?= e((string) $class['sort_order']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary edit-class" data-id="<?= e($class['id']) ?>" data-name="<?= e($class['name']) ?>" data-sort="<?= e((string) $class['sort_order']) ?>">Edit</button>
                        <form class="d-inline" method="post" action="<?= url('admin/classes/' . $class['id'] . '/delete') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>

