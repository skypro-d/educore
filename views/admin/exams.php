<h1 class="h3 mb-4">Entrance Exam CBT Setup</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <form class="panel" method="post" action="<?= url('admin/exams') ?>">
            <?= csrf_field() ?>
            <h2>Subject</h2>
            <input class="form-control mb-3" name="subject_name" placeholder="Mathematics">
            <button class="btn btn-primary">Add Subject</button>
        </form>
        <form class="panel mt-4" method="post" action="<?= url('admin/exams') ?>">
            <?= csrf_field() ?>
            <h2>Objective Question</h2>
            <select class="form-select mb-2" name="subject_id"><?php foreach ($subjects as $subject): ?><option value="<?= e($subject['id']) ?>"><?= e($subject['name']) ?></option><?php endforeach; ?></select>
            <textarea class="form-control mb-2" name="question" placeholder="Question"></textarea>
            <?php foreach (['a','b','c','d'] as $option): ?><input class="form-control mb-2" name="option_<?= $option ?>" placeholder="Option <?= strtoupper($option) ?>"><?php endforeach; ?>
            <select class="form-select mb-3" name="correct_option"><option>A</option><option>B</option><option>C</option><option>D</option></select>
            <button class="btn btn-primary">Save Question</button>
        </form>
    </div>
    <div class="col-lg-8"><div class="panel"><h2>Question Bank</h2><table class="table"><thead><tr><th>Subject</th><th>Question</th><th>Answer</th></tr></thead><tbody><?php foreach ($questions as $q): ?><tr><td><?= e($q['subject_name']) ?></td><td><?= e($q['question']) ?></td><td><?= e($q['correct_option']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>

