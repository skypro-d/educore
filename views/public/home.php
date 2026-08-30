<?php
$school = setting('school_name', 'Westfield Academy');
$year = date('Y') . ' / ' . ((int) date('y') + 1);
$phone = setting('school_phone', '+234 800 000 0000');
$email = setting('school_email', 'admissions@westfield.edu.ng');
$address = setting('school_address', '14 Academy Road, Victoria Island, Lagos, Nigeria');
$motto = setting('school_motto', 'Smart learning for a brighter future');
$logoLetter = strtoupper(substr($school, 0, 1));
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --navy: <?= setting('primary_color', '#1B2A56') ?>;
  --navy-dark: <?= setting('primary_color_dark', '#101B38') ?>;
  --gold: <?= setting('secondary_color', '#D9A441') ?>;
  --gold-dark: <?= setting('secondary_color_dark', '#B8842C') ?>;
  --paper:#FBF9F4;
  --ink:#14213D;
  --muted:#5C6478;
  --line:#E4E1D6;
  --line-dark:rgba(255,255,255,.14);
}
.school-home-body {
  font-family:'Inter',sans-serif;
  color:var(--ink);
  background:var(--paper);
  -webkit-font-smoothing:antialiased;
}
.school-home-body h1, .school-home-body h2, .school-home-body h3{font-family:'Fraunces',serif;font-weight:500;color:var(--navy);letter-spacing:-0.01em;}
.school-home-body a{color:inherit;text-decoration:none;}
.school-home-body .school-wrap{max-width:1120px;margin:0 auto;padding:0 32px;}
.school-home-body .school-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;
  color:var(--gold-dark);
}
.school-home-body .school-eyebrow::before{content:"";width:16px;height:1px;background:var(--gold-dark);}

/* NAV */
.school-home-body .school-nav{
  display:flex;align-items:center;justify-content:space-between;
  padding:20px 32px;border-bottom:1px solid var(--line);
  background:var(--paper);position:sticky;top:0;z-index:20;
}
.school-home-body .school-brand{display:flex;align-items:center;gap:12px;}
.school-home-body .school-crest{
  width:38px;height:38px;border-radius:50%;
  background:var(--navy);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-family:'Fraunces',serif;font-weight:600;font-size:16px;
  border:1px solid var(--gold);
  overflow:hidden;
}
.school-home-body .school-crest img{
  width:100%;height:100%;border-radius:50%;object-fit:cover;
}
.school-home-body .school-brand-name{font-family:'Fraunces',serif;font-weight:500;font-size:18px;color:var(--navy);text-align:left;}
.school-home-body .school-brand-sub{font-size:10px;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;display:block;margin-top:1px;}
.school-home-body .school-nav-links{display:flex;gap:32px;font-size:14px;color:var(--muted);}
.school-home-body .school-nav-links a:hover{color:var(--navy);}
.school-home-body .school-btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:11px 22px;border-radius:2px;font-size:13px;font-weight:600;
  letter-spacing:.01em;border:1px solid transparent;cursor:pointer;
  transition:all 0.2s;
}
.school-home-body .school-btn-gold{background:var(--gold);color:var(--navy-dark);}
.school-home-body .school-btn-gold:hover{background:var(--gold-dark);color:#fff;}
.school-home-body .school-btn-ghost-dark{border-color:var(--line-dark);color:#fff;}
.school-home-body .school-btn-ghost-dark:hover{background:rgba(255,255,255,0.1);}
.school-home-body .school-btn-ghost-light{border-color:var(--navy);color:var(--navy);}
.school-home-body .school-btn-ghost-light:hover{background:var(--navy);color:#fff;}

/* HERO */
.school-home-body .school-hero{
  background:var(--navy-dark);
  background-image:
    radial-gradient(circle at 85% 20%, rgba(217,164,65,0.10), transparent 55%);
  color:#fff;padding:88px 0 0;
  display: block !important;
}
.school-home-body .school-hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:56px;align-items:center;}
.school-home-body .school-hero h1{color:#fff;font-size:44px;line-height:1.18;margin:18px 0 20px;}
.school-home-body .school-hero h1 em{font-style:italic;color:var(--gold);}
.school-home-body .school-hero p{font-size:16px;line-height:1.75;color:#C7CCDC;max-width:440px;margin-bottom:32px;}
.school-home-body .school-hero-cta{display:flex;gap:14px;margin-bottom:56px;}
.school-home-body .school-id-frame{
  border:1px solid var(--line-dark);padding:14px;position:relative;
}
.school-home-body .school-id-frame::before{
  content:"ADMIT ONE — <?= e(setting('academic_year', '2026/27')) ?>";
  position:absolute;top:-1px;right:-1px;background:var(--gold);color:var(--navy-dark);
  font-size:10px;font-weight:700;letter-spacing:.06em;padding:6px 10px;
}
.school-home-body .school-id-photo{
  height:280px;background:
    linear-gradient(160deg, rgba(217,164,65,.18), rgba(255,255,255,.03));
  border:1px solid var(--line-dark);
  display:flex;align-items:center;justify-content:center;
  color:#8891A8;font-size:13px;flex-direction:column;gap:8px;
}

/* STAT STRIP */
.school-home-body .school-stats{border-top:1px solid var(--line-dark); display: block !important;}
.school-home-body .school-stats-row{display:grid;grid-template-columns:repeat(4,1fr);width:100%;}
.school-home-body .school-stat{padding:24px 0;text-align:center;border-right:1px solid var(--line-dark);}
.school-home-body .school-stat:last-child{border-right:none;}
.school-home-body .school-stat-num{font-family:'Fraunces',serif;font-size:26px;color:var(--gold);}
.school-home-body .school-stat-label{font-size:11px;color:#8891A8;letter-spacing:.05em;text-transform:uppercase;margin-top:4px;}

/* ABOUT */
.school-home-body .school-about{padding:96px 0;}
.school-home-body .school-about-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:64px;}
.school-home-body .school-about h2{font-size:30px;margin:14px 0 20px;}
.school-home-body .school-about p{color:var(--muted);line-height:1.8;font-size:15px;margin-bottom:16px;}
.school-home-body .school-pull{
  font-family:'Fraunces',serif;font-style:italic;font-size:21px;line-height:1.5;
  color:var(--navy);border-left:2px solid var(--gold);padding-left:20px;
}

/* ADMISSIONS — ledger style */
.school-home-body .school-admissions{padding:0 0 100px;}
.school-home-body .school-admissions .school-wrap>.school-eyebrow{display:block;margin-bottom:14px;}
.school-home-body .school-admissions h2{font-size:30px;margin-bottom:44px;max-width:520px;}
.school-home-body .school-ledger{border-top:1px solid var(--line);}
.school-home-body .school-ledger-row{
  display:grid;grid-template-columns:64px 1fr 1fr;gap:24px;
  padding:26px 0;border-bottom:1px solid var(--line);align-items:start;
}
.school-home-body .school-ledger-num{font-family:'Fraunces',serif;font-size:26px;color:var(--gold-dark);}
.school-home-body .school-ledger-title{font-family:'Fraunces',serif;font-size:18px;color:var(--navy);margin-bottom:6px;}
.school-home-body .school-ledger-desc{font-size:14px;color:var(--muted);line-height:1.7;}
.school-home-body .school-ledger-tag{font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted);align-self:center;}

/* FEES */
.school-home-body .school-fees{background:var(--navy);padding:88px 0;color:#fff;}
.school-home-body .school-fees h2{color:#fff;font-size:30px;margin:14px 0 8px;}
.school-home-body .school-fees > .school-wrap > p{color:#C7CCDC;font-size:14px;margin-bottom:40px;}
.school-home-body table.school-fee-table{width:100%;border-collapse:collapse;font-size:14px;}
.school-home-body .school-fee-table th{
  text-align:left;font-size:11px;letter-spacing:.06em;text-transform:uppercase;
  color:#8891A8;font-weight:600;padding:0 0 14px;border-bottom:1px solid var(--line-dark);
}
.school-home-body .school-fee-table td{padding:16px 0;border-bottom:1px solid var(--line-dark);color:#E7E9F0;}
.school-home-body .school-fee-table td:last-child, .school-home-body .school-fee-table th:last-child{text-align:right;color:var(--gold);font-weight:600;}
.school-home-body .school-fee-note{font-size:12px;color:#8891A8;margin-top:20px;}

/* TESTIMONIALS */
.school-home-body .school-testimonials{padding:96px 0;}
.school-home-body .school-testimonials h2{font-size:30px;margin:14px 0 44px;}
.school-home-body .school-t-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;}
.school-home-body .school-t-card{border:1px solid var(--line);padding:28px 24px;background:#fff;}
.school-home-body .school-t-mark{font-family:'Fraunces',serif;font-size:34px;color:var(--gold);line-height:1;margin-bottom:12px;}
.school-home-body .school-t-quote{font-size:14px;color:var(--ink);line-height:1.75;margin-bottom:20px;}
.school-home-body .school-t-who{font-size:13px;font-weight:600;color:var(--navy);}
.school-home-body .school-t-role{font-size:12px;color:var(--muted);}

/* CONTACT + FOOTER */
.school-home-body .school-contact{background:var(--paper);border-top:1px solid var(--line);padding:80px 0 0;}
.school-home-body .school-contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;padding-bottom:64px;}
.school-home-body .school-contact h2{font-size:28px;margin:14px 0 18px;}
.school-home-body .school-contact p{color:var(--muted);font-size:14px;line-height:1.8;margin-bottom:24px;}
.school-home-body .school-contact-list{list-style:none;font-size:14px;color:var(--ink);}
.school-home-body .school-contact-list li{padding:10px 0;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;}
.school-home-body .school-contact-list li span:first-child{color:var(--muted);}
.school-home-body .school-map-block{background:#fff;border:1px solid var(--line);height:100%;min-height:220px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:13px;}
.school-home-body .school-footer{
  border-top:1px solid var(--line);padding:22px 0;
  display:flex;justify-content:space-between;align-items:center;
  font-size:12px;color:var(--muted);
}

@media (max-width:820px){
  .school-home-body .school-nav {
    padding: 16px 20px;
  }
  .school-home-body .school-brand-name {
    font-size: 15px;
  }
  .school-home-body .school-brand-sub {
    font-size: 9px;
  }
  .school-home-body .school-crest {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }
  .school-home-body .school-hero {
    padding: 48px 0 0;
  }
  .school-home-body .school-hero h1 {
    font-size: 32px;
    line-height: 1.25;
  }
  .school-home-body .school-hero-cta {
    flex-direction: column;
    gap: 10px;
  }
  .school-home-body .school-hero-cta .school-btn {
    width: 100%;
    justify-content: center;
  }
  .school-home-body .school-id-frame {
    margin-bottom: 40px;
  }
  .school-home-body .school-hero-grid, .school-home-body .school-about-grid, .school-home-body .school-contact-grid{grid-template-columns:1fr;}
  .school-home-body .school-t-grid{grid-template-columns:1fr;}
  .school-home-body .school-stats-row{grid-template-columns:repeat(2,1fr);}
  .school-home-body .school-stat:nth-child(2){border-right:none;}
  .school-home-body .school-nav-links{display:none;}
  .school-home-body .school-ledger-row{grid-template-columns:40px 1fr;}
  .school-home-body .school-ledger-tag{display:none;}
  
  .school-home-body .school-about {
    padding: 64px 0;
  }
  .school-home-body .school-admissions {
    padding: 0 0 64px;
  }
  .school-home-body .school-fees {
    padding: 64px 0;
  }
  .school-home-body .school-testimonials {
    padding: 64px 0;
  }
  .school-home-body .school-contact {
    padding: 48px 0 0;
  }
  .school-home-body .school-contact-grid {
    gap: 32px;
    padding-bottom: 40px;
  }
}
</style>

<div class="school-home-body">

<!-- NAV -->
<nav class="school-nav">
  <a class="school-brand" href="<?= url() ?>">
    <div class="school-crest">
      <?php if (setting('school_logo')): ?>
        <img src="<?= url('uploads/' . setting('school_logo')) ?>" alt="<?= e($school) ?>">
      <?php else: ?>
        <?= e($logoLetter) ?>
      <?php endif; ?>
    </div>
    <div>
      <span class="school-brand-name"><?= e($school) ?></span>
      <span class="school-brand-sub"><?= e($address) ?></span>
    </div>
  </a>
  <div class="school-nav-links">
    <a href="#about">About</a>
    <a href="#admissions">Admissions</a>
    <a href="#fees">Fees</a>
    <a href="#community">Community</a>
    <a href="#contact">Contact</a>
  </div>
  <a href="<?= url('apply') ?>" class="school-btn school-btn-gold">Apply now</a>
</nav>

<!-- HERO -->
<header class="school-hero">
  <div class="school-wrap">
    <div class="school-hero-grid">
      <div>
        <span class="school-eyebrow" style="color:var(--gold);">Admissions open — <?= e(setting('academic_year', '2026/27')) ?></span>
        <h1>Smart learning for a <em>brighter</em> future</h1>
        <p><?= e($motto) ?>. A place where every child is known, challenged, and supported to become who they're meant to be.</p>
        <div class="school-hero-cta">
          <a href="<?= url('apply') ?>" class="school-btn school-btn-gold">Apply now</a>
          <a href="<?= url('track') ?>" class="school-btn school-btn-ghost-dark">Track application</a>
        </div>
      </div>
      <div class="school-id-frame">
        <div class="school-id-photo" style="overflow: hidden; display: flex; align-items: center; justify-content: center; height: 280px; border: none; padding: 0;">
          <?php if (file_exists(UPLOAD_PATH . 'classroom_hero.png')): ?>
            <img src="<?= url('uploads/classroom_hero.png') ?>" alt="Classroom photograph" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
            <span style="font-size:26px;">&#128247;</span>
            <span>Campus photograph</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="school-stats">
    <div class="school-wrap">
      <div class="school-stats-row">
        <div class="school-stat"><div class="school-stat-num"><?= e(setting('years_running', '32')) ?></div><div class="school-stat-label">Years running</div></div>
        <div class="school-stat"><div class="school-stat-num"><?= e(setting('students_count', '1,200+')) ?></div><div class="school-stat-label">Students</div></div>
        <div class="school-stat"><div class="school-stat-num"><?= e(setting('pass_rate', '98%')) ?></div><div class="school-stat-label">Exam pass rate</div></div>
        <div class="school-stat"><div class="school-stat-num"><?= e(setting('teacher_ratio', '1:15')) ?></div><div class="school-stat-label">Teacher ratio</div></div>
      </div>
    </div>
  </div>
</header>

<!-- ABOUT -->
<section class="school-about" id="about">
  <div class="school-wrap school-about-grid">
    <div>
      <span class="school-eyebrow">About the school</span>
      <h2>Rooted in discipline, built for curiosity</h2>
      <p><?= e($school) ?> was founded on a simple belief: children rise to the standard they're held to, and fall to it just as easily. Our classrooms are small, our expectations are high, and our staff stay long enough to watch a child grow up.</p>
      <p>Every learner follows the same core path — literacy and numeracy built early, character built alongside it — before branching into the sciences, arts, or trades that suit them.</p>
    </div>
    <div style="display:flex;align-items:center;">
      <p class="school-pull">"We don't just prepare students for exams. We prepare them for the next thirty years of their lives."</p>
    </div>
  </div>
</section>

<!-- ADMISSIONS PROCESS -->
<section class="school-admissions" id="admissions">
  <div class="school-wrap">
    <span class="school-eyebrow">How admission works</span>
    <h2>Four steps, from application to your child's first day</h2>
    <div class="school-ledger">
      <div class="school-ledger-row">
        <div class="school-ledger-num">01</div>
        <div>
          <div class="school-ledger-title">Submit your application</div>
          <div class="school-ledger-desc">Complete the online form with your child's details, previous school records, and a passport photograph. Takes about ten minutes.</div>
        </div>
        <div class="school-ledger-tag">~10 minutes</div>
      </div>
      <div class="school-ledger-row">
        <div class="school-ledger-num">02</div>
        <div>
          <div class="school-ledger-title">Assessment &amp; interview</div>
          <div class="school-ledger-desc">We invite shortlisted applicants for a short entrance assessment and a relaxed conversation with the child and one parent.</div>
        </div>
        <div class="school-ledger-tag">Within 2 weeks</div>
      </div>
      <div class="school-ledger-row">
        <div class="school-ledger-num">03</div>
        <div>
          <div class="school-ledger-title">Offer &amp; enrollment</div>
          <div class="school-ledger-desc">Successful applicants receive an offer letter with fee details and a payment link to secure the place.</div>
        </div>
        <div class="school-ledger-tag">Within 1 week</div>
      </div>
      <div class="school-ledger-row">
        <div class="school-ledger-num">04</div>
        <div>
          <div class="school-ledger-title">Resumption</div>
          <div class="school-ledger-desc">Your child receives their admission number, uniform list, and orientation date ahead of first term.</div>
        </div>
        <div class="school-ledger-tag">Term start</div>
      </div>
    </div>
  </div>
</section>

<!-- FEES -->
<section class="school-fees" id="fees">
  <div class="school-wrap">
    <span class="school-eyebrow" style="color:var(--gold);">Investment</span>
    <h2>Fees by class tier</h2>
    <p>Per term, inclusive of tuition, books, and continuous assessment. Boarding and transport are quoted separately at interview.</p>
    <table class="school-fee-table">
      <tr><th>Class tier</th><th>Ages</th><th>Per term</th></tr>
      <tr><td>Primary 1 – 3</td><td>5 – 8 years</td><td>₦185,000</td></tr>
      <tr><td>Primary 4 – 6</td><td>9 – 11 years</td><td>₦210,000</td></tr>
      <tr><td>JSS 1 – 3</td><td>12 – 14 years</td><td>₦245,000</td></tr>
      <tr><td>SS 1 – 3</td><td>15 – 17 years</td><td>₦275,000</td></tr>
    </table>
    <p class="school-fee-note">A non-refundable ₦25,000 registration fee applies once, at admission.</p>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="school-testimonials" id="community">
  <div class="school-wrap">
    <span class="school-eyebrow">From our community</span>
    <h2>What parents tell us</h2>
    <div class="school-t-grid">
      <div class="school-t-card">
        <div class="school-t-mark">&ldquo;</div>
        <p class="school-t-quote">My son struggled with reading before entering <?= e($school) ?>. Eighteen months later he's the one asking me to buy him more books.</p>
        <div class="school-t-who">Funmilayo A.</div>
        <div class="school-t-role">Parent, Primary 4</div>
      </div>
      <div class="school-t-card">
        <div class="school-t-mark">&ldquo;</div>
        <p class="school-t-quote">The teachers actually know my daughter — not just her grades, but how she thinks. That's rare to find.</p>
        <div class="school-t-who">Emeka O.</div>
        <div class="school-t-role">Parent, JSS 2</div>
      </div>
      <div class="school-t-card">
        <div class="school-t-mark">&ldquo;</div>
        <p class="school-t-quote">Admission was straightforward from the first form to the offer letter. No back-and-forth, no confusion.</p>
        <div class="school-t-who">Grace I.</div>
        <div class="school-t-role">Parent, SS 1</div>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="school-contact" id="contact">
  <div class="school-wrap">
    <div class="school-contact-grid">
      <div>
        <span class="school-eyebrow">Get in touch</span>
        <h2>Come see the campus for yourself</h2>
        <p>Tours run every weekday morning during term. No appointment needed for the admissions office — just walk in.</p>
        <a href="<?= url('apply') ?>" class="school-btn school-btn-ghost-light">Apply now</a>
        <ul class="school-contact-list" style="margin-top:28px;">
          <li><span>Address</span><span><?= e($address) ?></span></li>
          <li><span>Phone</span><span><?= e($phone) ?></span></li>
          <li><span>Email</span><span><?= e($email) ?></span></li>
        </ul>
      </div>
      <div class="school-map-block">Map embed</div>
    </div>
    <div class="school-wrap school-footer">
      <span>&copy; <?= date('Y') ?> <?= e($school) ?>. All rights reserved.</span>
      <span>Powered by EduCore</span>
    </div>
  </div>
</section>

</div>
