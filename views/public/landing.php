<?php
// EduCore Landing Page
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EduCore - #1 School Management Platform in Nigeria</title>
<meta name="description" content="Complete school management system for Nigerian schools. Admissions, attendance, fees, academics in one platform.">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--blue:#0052cc;--bdark:#0747a6;--blight:#e6f0ff;--text:#091e42;--muted:#6b778c;--brd:#dfe1e6;--hf:'Outfit',sans-serif;--bf:'Inter',sans-serif;}
*{box-sizing:border-box;}
body{background:#fff;color:var(--text);font-family:var(--bf);overflow-x:hidden;margin:0;padding:0;}
h1,h2,h3,h4,h5,h6{font-family:var(--hf);color:#000;font-weight:700;}
.pbar{background:linear-gradient(90deg,#fef9c3,#fffbeb);border-bottom:1px solid #fef08a;color:#713f12;font-size:13px;padding:9px 0;text-align:center;position:relative;z-index:1060;font-weight:500;}
.pbar a{color:var(--blue);font-weight:700;}
.pbx{position:absolute;right:18px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#713f12;font-size:18px;}
.lnav{background:#fff;border-bottom:1px solid var(--brd);padding:12px 0;}
.lnav .navbar-brand{font-family:var(--hf);font-weight:800;font-size:22px;color:var(--text)!important;display:flex;align-items:center;gap:8px;}
.lnav .navbar-brand small{font-size:10px;font-weight:400;color:var(--muted);display:block;margin-top:-3px;}
.lnav .nav-link{color:var(--text)!important;font-weight:600;font-size:14px;padding:7px 13px!important;}
.lnav .nav-link:hover{color:var(--blue)!important;}
.bnl{color:var(--text);font-weight:600;font-size:14px;text-decoration:none;padding:7px 12px;}
.bnd{border:1px solid var(--brd);background:#fff;color:var(--text);font-weight:600;font-size:13px;padding:7px 16px;border-radius:6px;text-decoration:none;}
.bnr{background:var(--blue);color:#fff!important;font-weight:700;font-size:13px;padding:7px 18px;border-radius:6px;text-decoration:none;}
.bnr:hover{background:var(--bdark);}
.hero{padding:64px 0 90px;background:linear-gradient(180deg,#f4f5f7 0%,#fff 100%);}
.hbadge{background:var(--blight);color:var(--blue);font-weight:700;font-size:12.5px;padding:5px 15px;border-radius:100px;display:inline-flex;align-items:center;gap:6px;margin-bottom:22px;}
.hh1{font-size:50px;font-weight:800;line-height:1.15;letter-spacing:-1.5px;margin-bottom:20px;color:#000;}
.hh1 span{color:var(--blue);}
.hp{font-size:15.5px;line-height:1.65;color:#000;margin-bottom:30px;max-width:500px;font-weight:700;}
.hcta{display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
.bph{background:var(--blue);color:#fff;font-weight:700;font-size:15px;padding:13px 28px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(0,82,204,.22);}
.bph:hover{background:var(--bdark);color:#fff;}
.bsh{border:1px solid var(--brd);background:#fff;color:var(--blue);font-weight:700;font-size:15px;padding:13px 24px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
.bsh:hover{background:#f4f5f7;}
.mwrap{position:relative;width:100%;min-height:460px;}
.cbox{background:#f4f5f7;border:1px solid var(--brd);border-radius:12px;width:88%;box-shadow:0 18px 40px rgba(9,30,66,.1);overflow:hidden;display:flex;flex-direction:column;}
.ctop{background:#fff;height:42px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;border-bottom:1px solid var(--brd);flex-shrink:0;}
.cbody{display:flex;flex:1;font-size:10px;min-height:300px;}
.csb{width:115px;background:#fff;border-right:1px solid var(--brd);padding:10px 8px;display:flex;flex-direction:column;gap:5px;flex-shrink:0;}
.csi{display:flex;align-items:center;gap:5px;font-size:8px;color:var(--muted);padding:4px 5px;border-radius:4px;}
.csi.act{background:var(--blight);color:var(--blue);font-weight:700;}
.cmain{flex:1;padding:12px;display:flex;flex-direction:column;gap:10px;overflow:hidden;}
.c4g{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;}
.cmet{background:#faf8f5;border:1px solid #eadecc;border-radius:6px;padding:8px;}
.cml{font-size:7px;font-weight:700;color:#8a8074;text-transform:uppercase;}
.cmv{font-size:18px;font-weight:800;font-family:var(--hf);line-height:1.1;margin:3px 0 2px;}
.cms{font-size:6px;color:#a9a39a;}
.cch{display:grid;grid-template-columns:3fr 2fr;gap:8px;flex:1;}
.ccrd{background:#fff;border:1px solid var(--brd);border-radius:7px;padding:10px;display:flex;flex-direction:column;overflow:hidden;}
.cct{font-size:8.5px;font-weight:700;color:var(--text);margin-bottom:6px;display:flex;align-items:center;gap:4px;}
.mbox{position:absolute;right:-20px;bottom:-30px;width:220px;background:#0b1a30;border:9px solid #0b1a30;border-radius:34px;box-shadow:0 18px 45px rgba(0,0,0,.28);overflow:hidden;z-index:10;}
.mscr{background:#f4f5f7;font-size:9px;color:var(--text);display:flex;flex-direction:column;max-height:460px;overflow:hidden;}
.mtop{background:#0b1a30;color:#fff;padding:9px 11px;display:flex;justify-content:space-between;align-items:center;font-size:8.5px;}
.mwel{background:linear-gradient(135deg,#1d4ed8,#1e40af);color:#fff;border-radius:8px;margin:7px;padding:10px;}
.mmg{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin:0 7px 5px;}
.mmc{background:#fff;border-radius:7px;border:1px solid var(--brd);padding:7px;}
.mag{display:grid;grid-template-columns:1fr 1fr;gap:4px;padding:0 7px;}
.ma{background:#f1f5f9;border-radius:5px;padding:6px 5px;text-align:center;font-size:7px;font-weight:600;}
.mc{background:#fff;border-radius:7px;border:1px solid var(--brd);margin:5px 7px;padding:9px;}
.caps{border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);padding:44px 0;}
.cap{display:flex;align-items:flex-start;gap:14px;}
.capi{width:44px;height:44px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.capt{font-weight:700;font-size:15px;color:#000;margin-bottom:3px;}
.capd{font-size:13px;color:#444;line-height:1.4;margin:0;}
.sbar{background:var(--bdark);color:#fff;padding:30px 0;}
.si{display:flex;align-items:center;gap:13px;}
.sic{font-size:30px;opacity:.8;}
.siv{font-size:23px;font-weight:800;font-family:var(--hf);line-height:1;}
.sil{font-size:11.5px;opacity:.8;}
.psec{padding:80px 0;background:#f4f5f7;}
.pc{background:#fff;border:1.5px solid var(--brd);border-radius:12px;padding:36px 24px;height:100%;display:flex;flex-direction:column;position:relative;transition:transform .2s,box-shadow .2s;}
.pc:hover{transform:translateY(-5px);box-shadow:0 12px 30px rgba(9,30,66,.08);}
.pc.pop{border-color:var(--blue);}
.plbl{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:var(--blue);color:#fff;font-size:10px;font-weight:700;text-transform:uppercase;padding:4px 14px;border-radius:100px;white-space:nowrap;}
.pn{font-size:17px;font-weight:700;margin-bottom:4px;color:#111;}
.pt{font-size:12.5px;color:var(--muted);margin-bottom:18px;}
.pa{font-size:33px;font-weight:800;font-family:var(--hf);margin-bottom:20px;line-height:1;color:#000;}
.pa span{font-size:13px;color:var(--muted);font-weight:400;}
.pl{list-style:none;padding:0;margin:0 0 28px;font-size:13px;color:#222;line-height:2.1;flex:1;}
.pl li{display:flex;align-items:center;gap:8px;}
.ok{color:#36b37e;}
.no{color:#ff5630;}
.pbtn{width:100%;font-weight:700;font-size:13px;padding:10px;border-radius:6px;text-align:center;text-decoration:none;display:block;}
.pbs{background:var(--blue);color:#fff;border:1.5px solid var(--blue);}
.pbs:hover{background:var(--bdark);color:#fff;}
.pbo{border:1.5px solid var(--blue);color:var(--blue);}
.pbo:hover{background:var(--blight);}
.pbd{border:1.5px solid #344563;color:#344563;}
.pbd:hover{background:#f4f5f7;color:var(--text);}
.prks{display:flex;justify-content:center;gap:28px;flex-wrap:wrap;margin-top:36px;font-size:13.5px;font-weight:600;color:#344563;}
.prks span i{color:#36b37e;margin-right:5px;}
.fsec{padding:80px 0;background:#fff;}
.fc{border:1px solid var(--brd);border-radius:12px;padding:28px;height:100%;transition:all .2s;}
.fc:hover{border-color:var(--blue);box-shadow:0 8px 28px rgba(0,82,204,.07);}
.fi{width:48px;height:48px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:18px;}
.ftt{font-size:17px;font-weight:700;margin-bottom:10px;color:#000;}
.fd{font-size:13px;color:#444;line-height:1.55;margin-bottom:18px;}
.fl{font-size:13px;font-weight:700;color:var(--blue);text-decoration:none;}
.hsec{padding:80px 0;background:#f4f5f7;}
.stpc{text-align:center;}
.stpi{width:60px;height:60px;border-radius:50%;background:#fff;border:1.5px solid var(--brd);display:inline-flex;align-items:center;justify-content:center;font-size:24px;color:var(--blue);margin-bottom:14px;}
.stpn{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;}
.stpt{font-size:14px;font-weight:700;margin-bottom:6px;color:#000;}
.stpd{font-size:11.5px;color:#444;max-width:140px;margin:0 auto;line-height:1.45;}
.stc{position:relative;}
@media(min-width:992px){.stc:not(:last-child)::after{content:">";position:absolute;top:28px;right:-6%;font-size:22px;color:var(--brd);}}
.tsec{padding:80px 0;background:#fff;}
.tc{border:1px solid var(--brd);border-radius:12px;padding:28px;height:100%;}
.tstar{color:#ffab00;font-size:15px;margin-bottom:14px;}
.ttxt{font-size:14px;line-height:1.6;color:#222;margin-bottom:22px;font-style:italic;}
.tpro{display:flex;align-items:center;gap:12px;}
.tav{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;}
.tn{font-weight:700;font-size:13px;color:#000;}
.tm{font-size:11px;color:#555;}
.ctaban{background:var(--bdark);color:#fff;border-radius:14px;padding:52px 48px;display:flex;align-items:center;justify-content:space-between;gap:28px;position:relative;overflow:hidden;}
.ctaban h2{color:#fff;font-weight:800;font-size:28px;margin-bottom:10px;}
.ctaban p{opacity:.9;font-size:14px;margin:0;max-width:560px;}
.ctabtns{display:flex;align-items:center;gap:12px;flex-shrink:0;flex-wrap:wrap;}
.bw{background:#fff;color:var(--blue);font-weight:700;font-size:14px;padding:12px 22px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
.bw:hover{background:#e6f0ff;color:var(--bdark);}
.bg{background:#36b37e;color:#fff;font-weight:700;font-size:14px;padding:12px 22px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
.bg:hover{background:#2d9a6b;}
.blsec{padding:80px 0;background:#f4f5f7;}
.blc{background:#fff;border:1px solid var(--brd);border-radius:12px;overflow:hidden;height:100%;transition:transform .2s;}
.blc:hover{transform:translateY(-5px);}
.blim{height:170px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:38px;}
.blb{padding:22px;}
.blt{font-size:11px;font-weight:700;color:var(--blue);text-transform:uppercase;display:block;margin-bottom:10px;}
.blh{font-size:15.5px;font-weight:700;margin-bottom:10px;line-height:1.4;color:#000;}
.bld{font-size:12.5px;color:#444;margin-bottom:18px;line-height:1.5;}
.bll{font-size:13px;font-weight:700;color:var(--blue);text-decoration:none;}
.mqsec{padding:48px 0;border-top:1px solid var(--brd);background:#fff;overflow:hidden;}
.mqtrk{display:flex;gap:20px;animation:mq 25s linear infinite;width:max-content;}
.mqtrk:hover{animation-play-state:paused;}
@keyframes mq{0%{transform:translateX(0);}100%{transform:translateX(-50%);}}
.lbdg{font-size:13px;font-weight:700;color:var(--muted);border:1.5px solid var(--brd);border-radius:30px;padding:8px 20px;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;}
.sft{background:#020b1e;color:#97a0af;padding:60px 0 0;font-size:13px;}
.fbr{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.fbi{width:36px;height:36px;border-radius:6px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;}
.fbn{font-weight:800;font-size:19px;color:#fff;font-family:var(--hf);line-height:1.1;}
.fbs{font-size:10px;color:#97a0af;}
.fdsc{line-height:1.65;color:#97a0af;margin-bottom:22px;font-size:12.5px;max-width:220px;}
.fsoc a{width:31px;height:31px;border-radius:50%;border:1.5px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);display:inline-flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;margin-right:7px;font-size:14px;}
.fsoc a:hover{background:var(--blue);border-color:var(--blue);}
.fct{color:#fff;font-weight:700;font-size:13px;margin-bottom:18px;}
.fls{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:11px;}
.fls a{color:#97a0af;text-decoration:none;font-size:12.5px;}
.fls a:hover{color:#fff;}
.nlbx{border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:22px;}
.nlbx h6{color:#fff;font-weight:700;font-size:13.5px;margin-bottom:7px;}
.nlbx p{font-size:12px;color:#97a0af;margin-bottom:14px;line-height:1.5;}
.nli{background:#fff;border:1px solid #dfe1e6;color:var(--text);font-size:13px;padding:10px 13px;border-radius:6px;width:100%;margin-bottom:10px;outline:none;}
.nli::placeholder{color:#7a869a;}
.nlb{background:var(--blue);color:#fff;font-weight:700;font-size:13px;border:none;padding:10px;border-radius:6px;width:100%;cursor:pointer;}
.nlb:hover{background:var(--bdark);}
.ftbot{margin-top:48px;padding:22px 0;border-top:1px solid rgba(255,255,255,.08);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;font-size:11.5px;color:#7a869a;}
.ftbls{display:flex;gap:18px;flex-wrap:wrap;}
.ftbls a{color:#7a869a;text-decoration:none;}
.ftbls a:hover{color:#fff;}
.pbdg{border:1px solid rgba(255,255,255,.15);border-radius:5px;padding:4px 12px;font-size:11px;color:#fff;font-weight:700;background:rgba(255,255,255,.03);display:inline-flex;align-items:center;height:26px;}
.pcib{border:1px solid rgba(255,255,255,.15);border-radius:5px;padding:4px 10px;font-size:10px;color:#fff;font-weight:700;background:rgba(255,255,255,.03);display:inline-flex;align-items:center;gap:4px;height:26px;}
@media(max-width:991px){.hh1{font-size:36px;}.mwrap{min-height:340px;margin-top:36px;}.mbox{right:0;width:180px;}.ctaban{flex-direction:column;padding:32px 28px;}.ctabtns{width:100%;}}
@media(max-width:575px){.hh1{font-size:28px;}}
</style>
</head>
<body>



<nav class="navbar navbar-expand-lg lnav sticky-top">
<div class="container">
<a class="navbar-brand" href="#"><i class="ti ti-school" style="color:var(--blue);font-size:26px;"></i><div>EduCore<small>by SkySavingTech</small></div></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nm"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nm">
<ul class="navbar-nav mx-auto align-items-center gap-1">
<li class="nav-item"><a class="nav-link" href="#">Home</a></li>
<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Features</a><ul class="dropdown-menu"><li><a class="dropdown-item" href="#">All Features</a></li></ul></li>
<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Solutions</a><ul class="dropdown-menu"><li><a class="dropdown-item" href="#">Primary Schools</a></li><li><a class="dropdown-item" href="#">Secondary Schools</a></li></ul></li>
<li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
<li class="nav-item"><a class="nav-link" href="#">Marketplace</a></li>
<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Resources</a><ul class="dropdown-menu"><li><a class="dropdown-item" href="#">Documentation</a></li></ul></li>
<li class="nav-item"><a class="nav-link" href="#">About Us</a></li>
</ul>
<div class="d-flex align-items-center gap-2">
<a href="<?= url('admin/login') ?>" class="bnr">Login</a>
</div>
</div>
</div>
</nav>

<header class="hero">
<div class="container">
<div class="row align-items-center g-5">
<div class="col-lg-5">
<span class="hbadge"><i class="ti ti-trophy"></i> #1 School Management Platform in Nigeria</span>
<h1 class="hh1">Smart School.<br>Better Education.<br><span>Stronger Future.</span></h1>
<p class="hp">EduCore is a complete School Management System that helps schools manage students, academics, fees, attendance, exams, communication and more.</p>
<div class="hcta">
<a href="<?= url('register-school') ?>" class="bph">Get Started <i class="ti ti-arrow-right"></i></a>
<a href="#" class="bsh"><i class="ti ti-player-play-filled"></i> Watch Demo</a>
</div>
</div>
<div class="col-lg-7">
<div class="mwrap">
<div class="cbox">
<div class="ctop">
<div style="display:flex;align-items:center;gap:8px;"><div style="width:22px;height:22px;border-radius:4px;background:var(--blue);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;"><i class="ti ti-school"></i></div><span style="font-weight:700;font-size:10px;">Bluefield International School Dashboard</span></div>
<div style="display:flex;gap:5px;"><span style="font-size:7.5px;border:1px solid #dfe1e6;border-radius:3px;padding:2px 6px;font-weight:600;">Settings</span><span style="font-size:7.5px;background:var(--bdark);color:#fff;border-radius:3px;padding:2px 6px;font-weight:700;">+ Record Payment</span></div>
</div>
<div class="cbody">
<div class="csb">
<div class="csi act"><i class="ti ti-layout-dashboard"></i> Dashboard</div>
<div class="csi"><i class="ti ti-users"></i> Students</div>
<div class="csi"><i class="ti ti-user-check"></i> Staff</div>
<div class="csi"><i class="ti ti-edit"></i> Admissions</div>
<div class="csi"><i class="ti ti-device-nfc"></i> Attendance</div>
<div class="csi"><i class="ti ti-credit-card"></i> Fees</div>
<div class="csi"><i class="ti ti-book"></i> Results</div>
<div class="csi"><i class="ti ti-message"></i> SMS</div>
<div style="margin-top:auto;padding-top:8px;border-top:1px solid var(--brd);display:flex;align-items:center;gap:5px;"><div style="width:15px;height:15px;border-radius:3px;background:var(--blue);display:flex;align-items:center;justify-content:center;color:#fff;font-size:8px;"><i class="ti ti-school"></i></div><span style="font-size:7.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Bluefield Intl.</span></div>
</div>
<div class="cmain">
<div style="font-size:11px;font-weight:700;">Bluefield International School Dashboard</div>
<div style="font-size:7.5px;color:var(--muted);margin-top:-6px;">Session: 2024/2025 &middot; Term: First &middot; Quick Overview</div>
<div class="c4g">
<div class="cmet"><div class="cml"><i class="ti ti-users"></i> STUDENTS</div><div class="cmv">1</div><div class="cms">Enrolled</div></div>
<div class="cmet"><div class="cml"><i class="ti ti-user-check"></i> STAFF</div><div class="cmv">0</div><div class="cms">Active</div></div>
<div class="cmet"><div class="cml"><i class="ti ti-door-enter"></i> CLASSES</div><div class="cmv">7</div><div class="cms">Configured</div></div>
<div class="cmet"><div class="cml"><i class="ti ti-cash"></i> REVENUE</div><div class="cmv" style="color:var(--blue);font-size:14px;">&#8358;5,000</div><div class="cms">Admission</div></div>
</div>
<div class="cch">
<div class="ccrd">
<div class="cct"><i class="ti ti-chart-bar" style="color:var(--blue);"></i> Monthly Admission Intake</div>
<div style="flex:1;display:flex;align-items:flex-end;gap:3px;">
<?php foreach([20,35,25,45,30,55,40,60,70,50,80,65] as $h): ?>
<div style="flex:1;height:<?=$h?>%;background:<?=$h>50?'var(--bdark)':'#c2d6f8'?>;border-radius:2px 2px 0 0;"></div>
<?php endforeach; ?>
</div>
<div style="display:flex;justify-content:space-between;font-size:6px;color:var(--muted);margin-top:4px;"><span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span></div>
</div>
<div class="ccrd">
<div class="cct"><i class="ti ti-funnel" style="color:var(--blue);"></i> Admission Funnel</div>
<div style="display:flex;flex-direction:column;gap:7px;flex:1;justify-content:center;">
<?php foreach([['Applications',100,'var(--blue)'],['Doc Verified',100,'var(--blue)'],['Under Review',0,'#e0e0e0'],['Interviews',0,'#e0e0e0'],['Enrolled',100,'#36b37e']] as [$lb,$pct,$col]): ?>
<div><div style="display:flex;justify-content:space-between;font-size:7.5px;<?=$pct==0?'color:var(--muted);':''?>"><span><?=$lb?></span><strong><?=$pct>0?1:0?></strong></div><div style="height:3px;background:#ebecf0;border-radius:2px;margin-top:2px;"><div style="width:<?=$pct?>%;height:100%;background:<?=$col?>;border-radius:2px;"></div></div></div>
<?php endforeach; ?>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="mbox">
<div class="mscr">
<div class="mtop"><i class="ti ti-menu-2"></i><span style="font-weight:700;">Bluefield Int'l School</span><span style="opacity:.7;">Logout</span></div>
<div style="padding:7px 8px 0;display:flex;justify-content:space-between;align-items:center;"><strong style="font-size:10px;">Parent Dashboard</strong><span style="font-size:6.5px;color:var(--muted);">Jun 30, 2026</span></div>
<div class="mwel"><div style="font-size:7.5px;opacity:.8;">Welcome back, Parent</div><div style="font-size:13px;font-weight:800;margin:2px 0;">eyitayo Azzan</div><div style="font-size:7px;margin-top:4px;display:flex;flex-direction:column;gap:2px;"><span>&#128100; eyitayo Azzan</span><span>&#127979; Primary 2</span></div></div>
<div class="mmg">
<div class="mmc"><div style="display:flex;justify-content:space-between;"><i class="ti ti-calendar-event" style="color:var(--blue);font-size:11px;"></i><strong style="color:#36b37e;font-size:9px;">100%</strong></div><div style="font-size:8.5px;font-weight:700;margin-top:3px;">Attendance</div><div style="height:3px;background:#e2e8f0;border-radius:2px;margin-top:4px;"><div style="width:100%;height:100%;background:#36b37e;border-radius:2px;"></div></div></div>
<div class="mmc"><div style="display:flex;justify-content:space-between;"><i class="ti ti-file-text" style="color:var(--blue);font-size:11px;"></i><strong style="font-size:9px;">0</strong></div><div style="font-size:8.5px;font-weight:700;margin-top:3px;">Pending Fees</div></div>
<div class="mmc"><div style="display:flex;justify-content:space-between;"><i class="ti ti-volume" style="color:var(--blue);font-size:11px;"></i><strong style="font-size:9px;">0</strong></div><div style="font-size:8.5px;font-weight:700;margin-top:3px;">Notices</div></div>
<div class="mmc"><div style="display:flex;justify-content:space-between;"><i class="ti ti-book" style="color:var(--blue);font-size:11px;"></i><strong style="font-size:9px;">0</strong></div><div style="font-size:8.5px;font-weight:700;margin-top:3px;">Subjects</div></div>
</div>
<div class="mc"><div style="display:flex;justify-content:space-between;font-weight:700;font-size:8.5px;margin-bottom:5px;"><span>Attendance (June)</span><span style="font-size:6px;color:var(--muted);">&#10003; Present</span></div><div style="display:flex;flex-wrap:wrap;gap:2px;"><?php for($i=1;$i<=25;$i++): ?><div style="width:14px;height:14px;border-radius:2px;background:#<?=($i%7==0||$i%7==6)?'e2e8f0':'d4edda'?>;display:inline-flex;align-items:center;justify-content:center;font-size:6.5px;color:var(--muted);"><?=$i?></div><?php endfor; ?></div></div>
<div class="mc" style="margin-top:0;"><div style="font-weight:700;font-size:8.5px;margin-bottom:6px;">Quick Actions</div><div class="mag"><div class="ma" style="background:#ebf5ff;color:var(--blue);"><i class="ti ti-calendar-stats"></i><br>Attendance</div><div class="ma" style="background:#eae6ff;color:#5243aa;"><i class="ti ti-file-analytics"></i><br>Results</div><div class="ma" style="background:#e3fcef;color:#006644;"><i class="ti ti-cash"></i><br>Pay Fees</div><div class="ma" style="background:#fff0b3;color:#a56104;"><i class="ti ti-calendar-time"></i><br>Timetable</div></div></div>
</div>
</div>
</div>
</div>
</div>
</div>
</header>

<section id="features" class="caps">
<div class="container"><div class="row g-4">
<div class="col-lg-4 col-md-6"><div class="cap"><div class="capi" style="background:#eae6ff;color:#403294;"><i class="ti ti-edit"></i></div><div><div class="capt">Admission &amp; CBT</div><p class="capd">Online admissions, CBT exams and student enrollment made easy and secure.</p></div></div></div>
<div class="col-lg-4 col-md-6"><div class="cap"><div class="capi" style="background:#e3fcef;color:#006644;"><i class="ti ti-device-nfc"></i></div><div><div class="capt">QR Attendance</div><p class="capd">Real-time QR/POS attendance with instant SMS &amp; email alerts to parents.</p></div></div></div>
<div class="col-lg-4 col-md-6"><div class="cap"><div class="capi" style="background:#deebff;color:#0747a6;"><i class="ti ti-school"></i></div><div><div class="capt">School Management</div><p class="capd">Manage academics, classes, teachers, exams, results and promotions.</p></div></div></div>
<div class="col-lg-4 col-md-6"><div class="cap"><div class="capi" style="background:#fffae6;color:#97600c;"><i class="ti ti-credit-card"></i></div><div><div class="capt">Fee &amp; Payments</div><p class="capd">Collect school fees online, track payments and generate invoices instantly.</p></div></div></div>
<div class="col-lg-4 col-md-6"><div class="cap"><div class="capi" style="background:#ffebf5;color:#c71585;"><i class="ti ti-users"></i></div><div><div class="capt">Parent &amp; Student Portals</div><p class="capd">Dedicated portals for parents and students to stay informed and connected.</p></div></div></div>
<div class="col-lg-4 col-md-6"><div class="cap"><div class="capi" style="background:#ebe6ff;color:#5243aa;"><i class="ti ti-chart-bar"></i></div><div><div class="capt">Reports &amp; Analytics</div><p class="capd">Powerful dashboards and reports for data-driven decisions.</p></div></div></div>
</div></div>
</section>

<section class="sbar">
<div class="container"><div class="row g-4 justify-content-between">
<div class="col-md-auto col-6"><div class="si"><div class="sic"><i class="ti ti-school"></i></div><div><div class="siv">350+</div><div class="sil">Active Schools</div></div></div></div>
<div class="col-md-auto col-6"><div class="si"><div class="sic"><i class="ti ti-users"></i></div><div><div class="siv">85,000+</div><div class="sil">Students</div></div></div></div>
<div class="col-md-auto col-6"><div class="si"><div class="sic"><i class="ti ti-user-check"></i></div><div><div class="siv">4,500+</div><div class="sil">Teachers</div></div></div></div>
<div class="col-md-auto col-6"><div class="si"><div class="sic"><i class="ti ti-history"></i></div><div><div class="siv">1.2M+</div><div class="sil">Attendance Records</div></div></div></div>
<div class="col-md-auto col-6"><div class="si"><div class="sic"><i class="ti ti-shield-check"></i></div><div><div class="siv">99.9%</div><div class="sil">Uptime &amp; Security</div></div></div></div>
</div></div>
</section>

<section id="pricing" class="psec">
<div class="container">
<div class="text-center mb-5"><span style="color:var(--blue);font-weight:700;font-size:12px;letter-spacing:1px;text-transform:uppercase;">PRICING PLANS</span><h2 style="font-size:34px;letter-spacing:-1px;margin-top:6px;">Choose the Perfect Plan for Your School</h2></div>
<div class="row g-4 justify-content-center">
<div class="col-lg-4 col-md-6"><div class="pc"><div class="pn">Basic Plan</div><div class="pt">For small schools</div><div class="pa">&#8358;25,000<span>/Year</span></div><ul class="pl"><li><i class="ti ti-check ok"></i> Up to 300 Students</li><li><i class="ti ti-check ok"></i> All Core Features</li><li><i class="ti ti-check ok"></i> SMS Attendance Alerts</li><li><i class="ti ti-check ok"></i> Parent Portal</li><li><i class="ti ti-check ok"></i> Basic Reports</li><li><i class="ti ti-x no"></i> Android POS</li></ul><a href="<?= url('register-school?plan=basic') ?>" class="pbtn pbs">Get Started</a></div></div>
<div class="col-lg-4 col-md-6"><div class="pc pop"><div class="plbl">Most Popular</div><div class="pn">Professional Plan</div><div class="pt">For growing schools</div><div class="pa">&#8358;45,000<span>/Year</span></div><ul class="pl"><li><i class="ti ti-check ok"></i> Up to 1,000 Students</li><li><i class="ti ti-check ok"></i> All Premium Features</li><li><i class="ti ti-check ok"></i> SMS Attendance Alerts</li><li><i class="ti ti-check ok"></i> Android POS Attendance</li><li><i class="ti ti-check ok"></i> Advanced Reports</li><li><i class="ti ti-check ok"></i> Online Fee Payments</li></ul><a href="<?= url('register-school?plan=professional') ?>" class="pbtn pbs">Get Started</a></div></div>
<div class="col-lg-4 col-md-6"><div class="pc"><div class="pn">Enterprise Plan</div><div class="pt">For large institutions</div><div class="pa">&#8358;75,000+<span>/Year</span></div><ul class="pl"><li><i class="ti ti-check ok"></i> Unlimited Students</li><li><i class="ti ti-check ok"></i> All Premium Features</li><li><i class="ti ti-check ok"></i> Custom Branding</li><li><i class="ti ti-check ok"></i> Dedicated Support</li><li><i class="ti ti-check ok"></i> Custom Integrations</li><li><i class="ti ti-check ok"></i> Priority Onboarding</li></ul><a href="#" class="pbtn pbd">Contact Sales</a></div></div>
</div>
<div class="prks"><span><i class="ti ti-circle-check-filled"></i> Free Installation</span><span><i class="ti ti-circle-check-filled"></i> Free Training</span><span><i class="ti ti-circle-check-filled"></i> Free Updates</span><span><i class="ti ti-circle-check-filled"></i> 24/7 Support</span><span><i class="ti ti-circle-check-filled"></i> Secure &amp; Reliable</span></div>
</div>
</section>

<section class="fsec">
<div class="container">
<div class="text-center mb-5"><h2 style="font-size:34px;letter-spacing:-1px;">Powerful Features for Modern Schools</h2></div>
<div class="row g-4 justify-content-center">
<div class="col-lg-4 col-md-6"><div class="fc"><div class="fi" style="background:#eae6ff;color:#403294;"><i class="ti ti-device-nfc"></i></div><h3 class="ftt">Android POS Attendance</h3><p class="fd">Use Android POS terminals for fast QR attendance with offline support and auto cloud sync.</p><a href="#" class="fl">Learn More <i class="ti ti-arrow-right"></i></a></div></div>
<div class="col-lg-4 col-md-6"><div class="fc"><div class="fi" style="background:#e3fcef;color:#006644;"><i class="ti ti-messages"></i></div><h3 class="ftt">Instant Parent SMS</h3><p class="fd">Automatically send SMS alerts to parents for attendance, fees and important announcements.</p><a href="#" class="fl">Learn More <i class="ti ti-arrow-right"></i></a></div></div>
<div class="col-lg-4 col-md-6"><div class="fc"><div class="fi" style="background:#deebff;color:#0747a6;"><i class="ti ti-credit-card"></i></div><h3 class="ftt">Online Payments</h3><p class="fd">Accept secure school fee payments online via Paystack, Flutterwave and Monnify gateways.</p><a href="#" class="fl">Learn More <i class="ti ti-arrow-right"></i></a></div></div>
<div class="col-lg-4 col-md-6"><div class="fc"><div class="fi" style="background:#fffae6;color:#a56104;"><i class="ti ti-file-text"></i></div><h3 class="ftt">Comprehensive Reports</h3><p class="fd">Get detailed analytics on students, staff, attendance, fees and academic performance.</p><a href="#" class="fl">Learn More <i class="ti ti-arrow-right"></i></a></div></div>
<div class="col-lg-4 col-md-6"><div class="fc"><div class="fi" style="background:#ffebf5;color:#c71585;"><i class="ti ti-server"></i></div><h3 class="ftt">Multi-Tenant SaaS</h3><p class="fd">Built with secure multi-tenant architecture ensuring isolated and protected data per school.</p><a href="#" class="fl">Learn More <i class="ti ti-arrow-right"></i></a></div></div>
</div>
</div>
</section>

<section class="hsec">
<div class="container">
<div class="text-center mb-5"><h2 style="font-size:34px;letter-spacing:-1px;">How EduCore Works</h2></div>
<div class="row g-4 justify-content-center">
<div class="col-lg-2 col-md-4 col-6 stc"><div class="stpc"><div class="stpi"><i class="ti ti-edit"></i></div><div class="stpn">Step 1</div><div class="stpt">Register School</div><p class="stpd">Create your school account in minutes.</p></div></div>
<div class="col-lg-2 col-md-4 col-6 stc"><div class="stpc"><div class="stpi"><i class="ti ti-list-check"></i></div><div class="stpn">Step 2</div><div class="stpt">Choose Plan</div><p class="stpd">Select the perfect plan for your school.</p></div></div>
<div class="col-lg-2 col-md-4 col-6 stc"><div class="stpc"><div class="stpi"><i class="ti ti-credit-card"></i></div><div class="stpn">Step 3</div><div class="stpt">Make Payment</div><p class="stpd">Pay securely online and get started.</p></div></div>
<div class="col-lg-2 col-md-4 col-6 stc"><div class="stpc"><div class="stpi"><i class="ti ti-users-group"></i></div><div class="stpn">Step 4</div><div class="stpt">Setup &amp; Onboard</div><p class="stpd">We set up your school and provide training.</p></div></div>
<div class="col-lg-2 col-md-4 col-6 stc"><div class="stpc"><div class="stpi"><i class="ti ti-device-laptop"></i></div><div class="stpn">Step 5</div><div class="stpt">Start Using</div><p class="stpd">Manage your school with powerful tools.</p></div></div>
<div class="col-lg-2 col-md-4 col-6 stc"><div class="stpc"><div class="stpi"><i class="ti ti-trending-up"></i></div><div class="stpn">Step 6</div><div class="stpt">Grow &amp; Succeed</div><p class="stpd">Deliver better education and grow together.</p></div></div>
</div>
</div>
</section>

<section class="tsec">
<div class="container">
<div class="text-center mb-5"><h2 style="font-size:34px;letter-spacing:-1px;">Trusted by Schools Across Nigeria</h2></div>
<div class="row g-4">
<div class="col-md-4"><div class="tc"><div class="tstar"><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i></div><p class="ttxt">"EduCore has transformed how we manage our school. Attendance tracking and SMS alerts are a game changer!"</p><div class="tpro"><div class="tav" style="background:#0052cc;">MA</div><div><div class="tn">Mrs. Adebayo</div><div class="tm">Principal, Greenfield Academy</div></div></div></div></div>
<div class="col-md-4"><div class="tc"><div class="tstar"><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i></div><p class="ttxt">"The platform is easy to use and the support team is always there to help. Highly recommended!"</p><div class="tpro"><div class="tav" style="background:#36b37e;">MI</div><div><div class="tn">Mr. Ibrahim</div><div class="tm">Proprietor, Royal College</div></div></div></div></div>
<div class="col-md-4"><div class="tc"><div class="tstar"><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i></div><p class="ttxt">"We can now collect fees online and parents love the instant SMS notifications."</p><div class="tpro"><div class="tav" style="background:#ff5630;">MO</div><div><div class="tn">Mrs. Okeke</div><div class="tm">Bursar, Prime International School</div></div></div></div></div>
</div>
<div style="display:flex;justify-content:center;gap:6px;margin-top:28px;"><span style="width:8px;height:8px;border-radius:50%;background:var(--blue);display:inline-block;"></span><span style="width:8px;height:8px;border-radius:50%;background:var(--brd);display:inline-block;"></span><span style="width:8px;height:8px;border-radius:50%;background:var(--brd);display:inline-block;"></span></div>
</div>
</section>

<section class="container py-5">
<div class="ctaban">
<div><h2>Ready to Transform Your School?</h2><p>Join hundreds of schools already using EduCore to simplify operations and focus on what matters most — education.</p></div>
<div class="ctabtns"><a href="#" class="bw"><i class="ti ti-calendar"></i> Book a Demo</a><a href="<?= url('register-school') ?>" class="bg">Get Started Now <i class="ti ti-arrow-right"></i></a></div>
<div style="position:absolute;right:24px;bottom:-10px;font-size:120px;opacity:.07;color:#fff;pointer-events:none;"><i class="ti ti-school"></i></div>
</div>
</section>

<section class="blsec">
<div class="container">
<div class="text-center mb-5"><h2 style="font-size:34px;letter-spacing:-1px;">Latest News &amp; Updates</h2></div>
<div class="row g-4">
<div class="col-lg-3 col-md-6"><div class="blc"><div class="blim" style="background:linear-gradient(135deg,#0747a6,#0052cc);"><i class="ti ti-device-laptop"></i></div><div class="blb"><span class="blt">Product Updates &middot; May 15, 2026</span><h4 class="blh">EduCore 2.2 &mdash; New Features &amp; Improvements</h4><p class="bld">Discover the latest features and performance enhancements in EduCore 2.2.</p><a href="#" class="bll">Read More <i class="ti ti-arrow-right"></i></a></div></div></div>
<div class="col-lg-3 col-md-6"><div class="blc"><div class="blim" style="background:linear-gradient(135deg,#36b37e,#006644);"><i class="ti ti-school"></i></div><div class="blb"><span class="blt">Education &middot; May 10, 2026</span><h4 class="blh">5 Ways to Improve School Management Effectively</h4><p class="bld">Practical tips for school leaders to improve efficiency and student outcomes.</p><a href="#" class="bll">Read More <i class="ti ti-arrow-right"></i></a></div></div></div>
<div class="col-lg-3 col-md-6"><div class="blc"><div class="blim" style="background:linear-gradient(135deg,#ffab00,#a56104);"><i class="ti ti-device-nfc"></i></div><div class="blb"><span class="blt">Tips &amp; Tricks &middot; May 5, 2026</span><h4 class="blh">How to Use QR Attendance Effectively</h4><p class="bld">Best practices for implementing QR attendance in your school.</p><a href="#" class="bll">Read More <i class="ti ti-arrow-right"></i></a></div></div></div>
<div class="col-lg-3 col-md-6"><div class="blc"><div class="blim" style="background:linear-gradient(135deg,#ff5630,#bf2600);"><i class="ti ti-shopping-bag"></i></div><div class="blb"><span class="blt">Announcement &middot; Apr 28, 2026</span><h4 class="blh">SkySavingTech Hub Marketplace is Live!</h4><p class="bld">Explore add-ons, SMS credits, POS devices and more in our new marketplace.</p><a href="#" class="bll">Read More <i class="ti ti-arrow-right"></i></a></div></div></div>
</div>
</div>
</section>

<section class="mqsec">
<div class="container text-center mb-4"><h6 style="color:var(--muted);font-size:11.5px;text-transform:uppercase;font-weight:700;letter-spacing:1px;">Trusted by Leading Schools</h6></div>
<div style="overflow:hidden;"><div class="mqtrk">
<?php foreach([['ti-leaf','Greenfield Academy'],['ti-crown','Royal College'],['ti-star','Prime International'],['ti-book','Wisdom Academy'],['ti-rocket','Future Minds'],['ti-school','Bluefield College'],['ti-award','Victory Academy'],['ti-building','Excel Schools'],['ti-leaf','Greenfield Academy'],['ti-crown','Royal College'],['ti-star','Prime International'],['ti-book','Wisdom Academy'],['ti-rocket','Future Minds'],['ti-school','Bluefield College'],['ti-award','Victory Academy'],['ti-building','Excel Schools']] as [$ic,$nm]): ?>
<span class="lbdg"><i class="ti <?=$ic?>"></i> <?=$nm?></span>
<?php endforeach; ?>
</div></div>
</section>

<footer class="sft">
<div class="container">
<div class="row g-4">
<div class="col-lg-3 col-md-6">
<div class="fbr"><div class="fbi"><i class="ti ti-school" style="color:#0052cc;font-size:20px;"></i></div><div><div class="fbn">EduCore</div><div class="fbs">by SkySavingTech</div></div></div>
<p class="fdsc">The complete school management platform trusted by hundreds of schools across Nigeria.</p>
<div class="fsoc"><a href="#"><i class="ti ti-brand-facebook"></i></a><a href="#"><i class="ti ti-brand-x"></i></a><a href="#"><i class="ti ti-brand-linkedin"></i></a><a href="#"><i class="ti ti-brand-youtube"></i></a><a href="#"><i class="ti ti-brand-instagram"></i></a></div>
</div>
<div class="col-lg-2 col-md-3 col-6"><div class="fct">Product</div><ul class="fls"><li><a href="#">Features</a></li><li><a href="#">School Admin</a></li><li><a href="#">Teacher Portal</a></li><li><a href="#">Parent Portal</a></li><li><a href="#">Student Portal</a></li><li><a href="#">Android POS</a></li></ul></div>
<div class="col-lg-2 col-md-3 col-6"><div class="fct">Solutions</div><ul class="fls"><li><a href="#">For Primary Schools</a></li><li><a href="#">For Secondary Schools</a></li><li><a href="#">For Colleges</a></li><li><a href="#">SaaS (Cloud)</a></li><li><a href="#">Self-Hosted</a></li><li><a href="#">Mobile Apps</a></li></ul></div>
<div class="col-lg-2 col-md-3 col-6"><div class="fct">Company</div><ul class="fls"><li><a href="#">About Us</a></li><li><a href="#">Careers</a></li><li><a href="#">Blog</a></li><li><a href="#">Partners</a></li><li><a href="#">Contact Us</a></li><li><a href="#">Book a Demo</a></li></ul></div>
<div class="col-lg-2 col-md-3 col-6"><div class="fct">Resources</div><ul class="fls"><li><a href="#">Documentation</a></li><li><a href="#">Help Center</a></li><li><a href="#">Video Tutorials</a></li><li><a href="#">API Reference</a></li><li><a href="#">System Status</a></li><li><a href="#">Updates</a></li></ul></div>
</div>
<div class="row mt-5">
<div class="col-lg-5 col-md-6">
<div class="nlbx">
<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;"><span style="width:28px;height:28px;background:#ffab00;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;">&#9993;</span><h6 class="mb-0">Subscribe to our newsletter</h6></div>
<p>Get product updates, tips and news delivered to your inbox.</p>
<form method="POST" action="#">
<?= csrf_field() ?>
<input type="email" name="email" class="nli" placeholder="Enter your email address" required>
<button type="submit" class="nlb">Subscribe</button>
</form>
</div>
</div>
<div class="col-lg-4 col-md-6 mt-4 mt-lg-0">
<div class="fct">Marketplace</div>
<ul class="fls"><li><a href="#">Buy SMS Credits</a></li><li><a href="#">Android POS Terminals</a></li><li><a href="#">Custom Mobile Apps</a></li><li><a href="#">Premium Support</a></li><li><a href="#">Training &amp; Onboarding</a></li><li><a href="#">More Add-ons</a></li></ul>
</div>
</div>
<div class="ftbot">
<div>&copy; 2026 SkySavingTech Limited. All rights reserved.</div>
<div class="ftbls"><a href="#">Privacy Policy</a><a href="#">Terms of Service</a><a href="#">Refund Policy</a><a href="#">SLA</a><a href="#">Security</a></div>
<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;"><span class="pbdg">Paystack</span><span class="pbdg">Monnify</span><span class="pbdg">Flutterwave</span><span style="color:rgba(255,255,255,.15);">|</span><span class="pcib"><i class="ti ti-shield-check"></i> PCI-DSS Compliant</span></div>
</div>
</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
