<h1 class="h3 mb-4">Role Management</h1>
<div class="panel">
    <p class="text-muted">Role permissions are enforced in code and seeded in the database. Super Admin has full access.</p>
    <table class="table align-middle">
        <thead><tr><th>Role</th><th>Description</th></tr></thead>
        <tbody><?php foreach ($roles as $role): ?><tr><td><?= e($role['name']) ?></td><td><?= e($role['description']) ?></td></tr><?php endforeach; ?></tbody>
    </table>
</div>
