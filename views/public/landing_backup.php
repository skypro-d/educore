<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduCore — #1 School Management Platform in Nigeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #0052cc;
            --hover-blue: #0747a6;
            --light-blue-bg: #e6f0ff;
            --text-dark: #091e42;
            --text-muted: #6b778c;
            --border-color: #dfe1e6;
            --font-headings: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        body {
            background-color: #ffffff;
            color: var(--text-dark);
            font-family: var(--font-body);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-headings);
            color: var(--text-dark);
            font-weight: 700;
        }

        /* Top Announcement Promo Bar */
        .promo-banner {
            background: linear-gradient(90deg, #fef9c3 0%, #fffbeb 100%);
            border-bottom: 1px solid #fef08a;
            color: #713f12;
            font-size: 13px;
            padding: 10px 0;
            text-align: center;
            position: relative;
            z-index: 1050;
            font-weight: 500;
        }
        .promo-banner a {
            color: var(--primary-blue);
            text-decoration: underline;
            font-weight: 600;
        }
        .promo-banner .close-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #713f12;
            font-size: 16px;
        }

        /* Navbar */
        .landing-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 14px 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-family: var(--font-headings);
            font-weight: 800;
            font-size: 24px;
            color: var(--text-dark) !important;
        }

        .navbar-brand span {
            font-weight: 400;
            font-size: 11px;
            color: var(--text-muted);
            display: block;
            margin-top: -4px;
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 14px !important;
        }

        .nav-link:hover {
            color: var(--primary-blue) !important;
        }

        .btn-login-link {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            padding: 8px 12px;
        }
        .btn-login-link:hover {
            color: var(--primary-blue);
        }

        .btn-demo {
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-demo:hover {
            background-color: #f4f5f7;
        }

        .btn-register {
            background-color: var(--primary-blue);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-register:hover {
            background-color: var(--hover-blue);
        }

        /* Hero Section */
        .hero-container {
            padding: 60px 0 80px 0;
            background: linear-gradient(180deg, #f4f5f7 0%, #ffffff 100%);
            position: relative;
        }

        .hero-badge {
            background-color: var(--light-blue-bg);
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 100px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 24px;
        }

        .hero-h1 {
            font-size: 54px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 24px;
        }

        .hero-h1 span {
            color: var(--primary-blue);
        }

        .hero-p {
            font-size: 16px;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 32px;
            max-width: 520px;
        }

        .btn-hero-primary {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 28px;
            border-radius: 6px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 82, 204, 0.2);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-hero-primary:hover {
            background-color: var(--hover-blue);
            color: #ffffff;
        }

        .btn-hero-secondary {
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 15px;
            padding: 14px 28px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-hero-secondary:hover {
            background-color: #f4f5f7;
        }

        .hero-checklist {
            display: flex;
            gap: 24px;
            margin-top: 40px;
            font-size: 13px;
            font-weight: 600;
            color: #344563;
        }

        .hero-checklist i {
            color: #36b37e;
            margin-right: 4px;
        }

        /* Mockup Bezels Wrapper */
        .mockups-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 480px;
        }

        /* Web Console Mockup - Bluefield International School Dashboard */
        .console-mockup {
            background-color: #f4f5f7;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            width: 95%;
            box-shadow: 0 20px 40px rgba(9, 30, 66, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            aspect-ratio: 16/10;
        }

        .console-header {
            background-color: #ffffff;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .console-body {
            background-color: #f4f5f7;
            flex-grow: 1;
            display: flex;
            font-size: 10px;
            color: var(--text-dark);
        }

        .console-sidebar {
            width: 130px;
            background-color: #ffffff;
            border-right: 1px solid var(--border-color);
            padding: 12px 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .console-main {
            flex-grow: 1;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-y: auto;
        }

        .console-metrics-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
        }

        .console-metric-box {
            background: #faf8f5;
            border: 1px solid #eadecc;
            border-radius: 6px;
            padding: 8px;
            font-size: 8px;
        }

        .console-metric-val {
            font-size: 18px;
            font-weight: 800;
            margin-top: 4px;
            color: var(--text-dark);
            font-family: var(--font-headings);
            line-height: 1;
        }

        /* Chart Columns splitting */
        .console-charts-split {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 12px;
            flex-grow: 1;
        }

        .console-chart-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            display: flex;
            flex-direction: column;
        }

        /* Mobile Mockup overlapping - Parent Dashboard */
        .mobile-mockup {
            position: absolute;
            right: -20px;
            bottom: -40px;
            width: 250px;
            background-color: #0b1a30;
            border: 10px solid #0b1a30;
            border-radius: 36px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            aspect-ratio: 9/19;
            z-index: 10;
        }

        .mobile-screen {
            background-color: #f4f5f7;
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            font-size: 9px;
            color: var(--text-dark);
        }

        .mobile-nav-bar {
            background-color: #0b1a30;
            color: #ffffff;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-welcome-card {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #ffffff;
            border-radius: 8px;
            padding: 12px;
            margin: 10px;
        }

        .mobile-metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 0 10px;
        }

        .mobile-metric-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 10px;
            border: 1px solid var(--border-color);
        }

        .mobile-card-title {
            font-weight: 700;
            font-size: 11px;
            margin-top: 4px;
        }

        .mobile-panel-card {
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin: 10px;
            padding: 12px;
        }

        .mobile-grid-day {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            background-color: #f1f5f9;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            margin: 1px;
            color: var(--text-muted);
        }

        .mobile-action-btn {
            background-color: #f1f5f9;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
            font-size: 8px;
        }

        /* Inline Capabilities Features Banner */
        .features-banner {
            border-bottom: 1px solid var(--border-color);
            padding: 40px 0;
        }

        .feature-item-col {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .feature-item-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .feature-item-title {
            font-weight: 700;
            font-size: 14px;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .feature-item-desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 0;
        }

        /* Dark Blue Statistics Bar */
        .stats-counter-bar {
            background-color: #0747a6;
            color: #ffffff;
            padding: 30px 0;
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stats-icon {
            font-size: 32px;
            opacity: 0.8;
        }

        .stats-val {
            font-size: 24px;
            font-weight: 800;
            font-family: var(--font-headings);
            line-height: 1;
        }

        .stats-label {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Pricing Card Design */
        .pricing-section {
            padding: 80px 0;
            background-color: #f4f5f7;
        }

        .pricing-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: transform 0.2s;
        }

        .pricing-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(9, 30, 66, 0.05);
        }

        .pricing-card.popular {
            border: 2px solid var(--primary-blue);
        }

        .pricing-card.popular .badge-pop {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--primary-blue);
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
        }

        .pricing-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .pricing-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .pricing-amt {
            font-size: 36px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 24px;
            font-family: var(--font-headings);
        }

        .pricing-amt span {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .pricing-list {
            list-style: none;
            padding: 0;
            margin: 0 0 32px 0;
            font-size: 13px;
            color: #344563;
            line-height: 2;
            flex-grow: 1;
        }

        .pricing-list li {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pricing-list li i.ti-check {
            color: #36b37e;
        }

        .pricing-list li i.ti-x {
            color: #ff5630;
        }

        .btn-pricing {
            width: 100%;
            font-weight: 700;
            font-size: 13px;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .btn-pricing-outline {
            border: 1px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-pricing-outline:hover {
            background-color: var(--light-blue-bg);
        }

        .btn-pricing-solid {
            background-color: var(--primary-blue);
            color: #ffffff;
        }

        .btn-pricing-solid:hover {
            background-color: var(--hover-blue);
        }

        /* Under pricing row checklist */
        .pricing-check-row {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 40px;
            font-size: 14px;
            font-weight: 600;
            color: #344563;
        }
        .pricing-check-row span i {
            color: #36b37e;
            margin-right: 6px;
        }

        /* Powerful Features Section style */
        .powerful-features-section {
            padding: 80px 0;
            background-color: #ffffff;
        }
        .feature-box-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            height: 100%;
            transition: all 0.2s;
        }
        .feature-box-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 10px 30px rgba(0, 82, 204, 0.05);
        }
        .feature-box-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .feature-box-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .feature-box-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .feature-box-link {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-blue);
            text-decoration: none;
        }

        /* How EduCore Works flow style */
        .how-it-works-section {
            padding: 80px 0;
            background-color: #f4f5f7;
        }
        .works-step-card {
            text-align: center;
            position: relative;
        }
        .works-step-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary-blue);
            margin-bottom: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .works-step-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .works-step-desc {
            font-size: 12px;
            color: var(--text-muted);
            max-width: 160px;
            margin: 0 auto;
            line-height: 1.4;
        }

        /* Connective Arrow decoration helper */
        .works-step-col {
            position: relative;
        }
        @media (min-width: 992px) {
            .works-step-col:not(:last-child)::after {
                content: "→";
                position: absolute;
                top: 30px;
                right: -10%;
                font-size: 24px;
                color: var(--border-color);
            }
        }

        /* Testimonials styles */
        .testimonials-section {
            padding: 80px 0;
            background-color: #ffffff;
        }
        .testimonial-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            height: 100%;
            background-color: #ffffff;
        }
        .testimonial-stars {
            color: #ffab00;
            font-size: 16px;
            margin-bottom: 16px;
        }
        .testimonial-text {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-dark);
            margin-bottom: 24px;
        }
        .testimonial-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .testimonial-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052cc 0%, #0747a6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
        }
        .testimonial-name {
            font-weight: 700;
            font-size: 13px;
        }
        .testimonial-meta {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Blog Section */
        .blog-section {
            padding: 80px 0;
            background-color: #f4f5f7;
        }
        .blog-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
            transition: all 0.2s;
        }
        .blog-card:hover {
            transform: translateY(-4px);
        }
        .blog-img-holder {
            height: 180px;
            background: linear-gradient(135deg, #0747a6 0%, #0052cc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 40px;
        }
        .blog-body {
            padding: 24px;
        }
        .blog-tag {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            margin-bottom: 12px;
            display: inline-block;
        }
        .blog-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .blog-text {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .blog-link {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-blue);
            text-decoration: none;
        }

        /* Logo Marquee loop */
        .marquee-section {
            padding: 50px 0;
            border-top: 1px solid var(--border-color);
            background-color: #ffffff;
        }
        .marquee-logo-badge {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            border: 1.5px solid var(--border-color);
            border-radius: 30px;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Blue Banner Footer CTA */
        .bottom-cta-banner {
            background-color: #0747a6;
            color: #ffffff;
            border-radius: 12px;
            padding: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            position: relative;
            overflow: hidden;
        }

        .bottom-cta-banner h2 {
            color: #ffffff;
            font-weight: 800;
            margin-bottom: 12px;
        }

        /* Premium Custom Dark Footer Design - EXACT MATCH */
        .premium-footer {
            background-color: #020b1e;
            color: #a5adba;
            padding: 60px 0 30px 0;
            font-size: 12.5px;
        }

        .footer-desc {
            line-height: 1.6;
            color: #97a0af;
            margin-top: 14px;
            margin-bottom: 24px;
            font-size: 12.5px;
            max-width: 230px;
        }

        .footer-social-link {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            background-color: rgba(255, 255, 255, 0.06);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            margin-right: 8px;
            transition: all 0.2s;
            font-size: 14px;
        }

        .footer-social-link:hover {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .footer-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 13.5px;
            margin-bottom: 20px;
        }

        .footer-links-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-links-list a {
            color: #97a0af;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 12.5px;
        }

        .footer-links-list a:hover {
            color: #ffffff;
        }

        .newsletter-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 24px;
            background-color: rgba(255, 255, 255, 0.01);
        }

        .newsletter-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 13.5px;
            margin-bottom: 8px;
        }

        .newsletter-desc {
            font-size: 12px;
            color: #97a0af;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .newsletter-input {
            background-color: #ffffff;
            border: 1px solid #dfe1e6;
            color: #091e42;
            font-size: 13px;
            padding: 10px 14px;
            border-radius: 6px;
            width: 100%;
            margin-bottom: 12px;
            outline: none;
        }

        .newsletter-input::placeholder {
            color: #7a869a;
        }

        .btn-subscribe {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            border: none;
            padding: 10px;
            border-radius: 6px;
            width: 100%;
            transition: background-color 0.2s;
        }

        .btn-subscribe:hover {
            background-color: var(--hover-blue);
        }

        .footer-bottom-bar {
            margin-top: 50px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            font-size: 12px;
            color: #7a869a;
        }

        .footer-bottom-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .footer-bottom-links a {
            color: #7a869a;
            text-decoration: none;
        }

        .footer-bottom-links a:hover {
            color: #ffffff;
        }

        .provider-badge-pill {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 5px;
            padding: 4px 14px;
            font-size: 11px;
            color: #ffffff;
            font-weight: 700;
            background-color: rgba(255, 255, 255, 0.03);
            display: inline-flex;
            align-items: center;
            height: 28px;
        }

        /* Mobile responsiveness overrides */
        @media (max-width: 991px) {
            .hero-h1 {
                font-size: 38px;
            }
            .mockups-wrapper {
                min-height: 400px;
                margin-top: 40px;
            }
            .console-mockup {
                width: 100%;
            }
            .mobile-mockup {
                width: 180px;
                right: 0;
            }
            .bottom-cta-banner {
                flex-direction: column;
                align-items: flex-start;
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<!-- Promo Banner -->
<div class="promo-banner">
    🎉 <strong>New:</strong> EduCore 2.2 is here! Explore powerful new features and performance improvements. <a href="#">View changelog</a>
    <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg landing-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="ti ti-school" style="color: var(--primary-blue); font-size: 28px; margin-right: 8px;"></i>
            <div>
                EduCore
                <span>by SkySavingTech</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto align-items-center gap-1">
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Features</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Solutions</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Marketplace</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Resources</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#">About Us</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= url('admin/login') ?>" class="btn-login-link">Login</a>
                <a href="#" class="btn-demo">Book a Demo</a>
                <a href="<?= url('register-school') ?>" class="btn-register">Start Free Trial</a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<header class="hero-container">
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Left Side Details -->
            <div class="col-lg-5">
                <span class="hero-badge"><i class="ti ti-trophy"></i> #1 School Management Platform in Nigeria</span>
                <h1 class="hero-h1">Smart School.<br>Better Education.<br><span>Stronger Future.</span></h1>
                <p class="hero-p">
                    EduCore is a complete School Management System that helps schools manage students, academics, fees, attendance, exams, communication and more — all in one intelligent platform.
                </p>
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= url('register-school') ?>" class="btn-hero-primary">Start Free Trial <i class="ti ti-arrow-right"></i></a>
                    <a href="#" class="btn-hero-secondary"><i class="ti ti-player-play-filled"></i> Watch Demo</a>
                </div>
                <div class="hero-checklist">
                    <div><i class="ti ti-checkbox"></i> No Credit Card Required</div>
                    <div><i class="ti ti-checkbox"></i> 30-Day Free Trial</div>
                    <div><i class="ti ti-checkbox"></i> Setup in Minutes</div>
                </div>
            </div>
            <!-- Right Side Mockup Graphic Bezels -->
            <div class="col-lg-7">
                <div class="mockups-wrapper">
                    <!-- Web Console Mockup - Bluefield International School Dashboard -->
                    <div class="console-mockup">
                        <div class="console-header" style="background-color:#ffffff; height:45px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:24px; height:24px; border-radius:4px; background-color:var(--primary-blue); display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px;"><i class="ti ti-school"></i></div>
                                <span style="font-weight:700; font-size:11px; color:var(--text-dark);">Bluefield International School Dashboard</span>
                            </div>
                            <div style="display:flex; gap:6px;">
                                <span class="badge bg-success" style="font-size:8px; padding:4px 8px; display:inline-flex; align-items:center; gap:2px;"><i class="ti ti-wifi"></i> Portal Live</span>
                                <span style="font-size:9px; border:1px solid #dfe1e6; border-radius:3px; padding:2px 6px; font-weight:600; color:var(--text-dark); background:#fff;"><i class="ti ti-settings"></i> Settings</span>
                                <span style="font-size:9px; background-color:var(--hover-blue); color:#fff; border-radius:3px; padding:2px 6px; font-weight:600;">+ Record Payment</span>
                            </div>
                        </div>
                        <div class="console-body">
                            <!-- Left Sidebar containing Bluefield International School and scrollbar -->
                            <div class="console-sidebar">
                                <div style="display:flex; align-items:center; gap:6px; margin-top: auto; padding-top:20px; border-top:1px solid var(--border-color);">
                                    <div style="width:18px; height:18px; border-radius:3px; background-color:var(--primary-blue); display:flex; align-items:center; justify-content:center; color:#fff; font-size:9px;"><i class="ti ti-school"></i></div>
                                    <span style="font-weight:700; font-size:8px; display:block; max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Bluefield International School</span>
                                </div>
                            </div>
                            <!-- Main console view matching screenshot -->
                            <div class="console-main">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-size:12px; font-weight:700; color:var(--text-dark);">Bluefield International School Dashboard</div>
                                        <div style="font-size:8px; color:var(--text-muted);">Session: 2024/2025 . Term: First . Quick Overview</div>
                                    </div>
                                </div>
                                <div class="console-metrics-grid">
                                    <div class="console-metric-box">
                                        <div style="color:#8a8074; font-weight:700; font-size:7px;"><i class="ti ti-users"></i> TOTAL STUDENTS</div>
                                        <div class="console-metric-val">1</div>
                                        <div style="color:#8a8074; font-size:6px;">Active enrolled</div>
                                    </div>
                                    <div class="console-metric-box">
                                        <div style="color:#8a8074; font-weight:700; font-size:7px;"><i class="ti ti-user-check"></i> STAFF REGISTRY</div>
                                        <div class="console-metric-val">0</div>
                                        <div style="color:#8a8074; font-size:6px;">Teachers & admin</div>
                                    </div>
                                    <div class="console-metric-box">
                                        <div style="color:#8a8074; font-weight:700; font-size:7px;"><i class="ti ti-door-enter"></i> TOTAL CLASSES</div>
                                        <div class="console-metric-val">7</div>
                                        <div style="color:#8a8074; font-size:6px;">Configured rooms</div>
                                    </div>
                                    <div class="console-metric-box">
                                        <div style="color:#8a8074; font-weight:700; font-size:7px;"><i class="ti ti-cash"></i> ADMISSION REV</div>
                                        <div class="console-metric-val" style="color:var(--primary-blue);">₦5,000</div>
                                        <div style="color:#8a8074; font-size:6px;">Form & acceptance</div>
                                    </div>
                                    <div class="console-metric-box">
                                        <div style="color:#8a8074; font-weight:700; font-size:7px;"><i class="ti ti-cash-banknote"></i> TERM FEES PAID</div>
                                        <div class="console-metric-val" style="color:#36b37e;">₦0</div>
                                        <div style="color:#8a8074; font-size:6px;">Tuition collected</div>
                                    </div>
                                    <div class="console-metric-box">
                                        <div style="color:#8a8074; font-weight:700; font-size:7px;"><i class="ti ti-alert-circle"></i> OUTSTANDING FEES</div>
                                        <div class="console-metric-val" style="color:#ff5630;">₦0</div>
                                        <div style="color:#8a8074; font-size:6px;">Pending balances</div>
                                    </div>
                                </div>
                                <div class="console-charts-split">
                                    <div class="console-chart-card">
                                        <div style="font-weight:700; margin-bottom:8px; font-size:9px; color:var(--text-dark); display:flex; align-items:center; gap:4px;"><i class="ti ti-chart-bar" style="color:var(--primary-blue);"></i> Monthly Admission Intake</div>
                                        <div style="font-size:7px; color:var(--text-muted); display:flex; align-items:center; gap:4px; margin-bottom:10px;">
                                            <span style="display:inline-block; width:6px; height:6px; background-color:#0747a6; border-radius:1px;"></span> Applications
                                        </div>
                                        <!-- Exact bar chart representation -->
                                        <div style="display:flex; flex-grow:1; position:relative; min-height:80px;">
                                            <!-- Y axis labels -->
                                            <div style="display:flex; flex-direction:column; justify-content:space-between; font-size:7px; color:var(--text-muted); text-align:right; width:15px; padding-right:4px; border-right:1px solid #dfe1e6;">
                                                <span>1.0</span><span>0.9</span><span>0.8</span><span>0.7</span><span>0.6</span><span>0.5</span><span>0.4</span><span>0.3</span><span>0.2</span><span>0.1</span><span>0</span>
                                            </div>
                                            <!-- Chart Bar area -->
                                            <div style="flex-grow:1; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; position:relative; background-image: linear-gradient(to bottom, #ebecf0 1px, transparent 1px); background-size: 100% 8px;">
                                                <div style="width:75%; height:100%; background-color:#0747a6; border-radius:1px 1px 0 0;"></div>
                                            </div>
                                        </div>
                                        <div style="text-align:center; font-size:7px; color:var(--text-muted); margin-top:4px; padding-left:15px;">Jun 2026</div>
                                    </div>
                                    <div class="console-chart-card">
                                        <div style="font-weight:700; margin-bottom:8px; font-size:9px; color:var(--text-dark); display:flex; align-items:center; gap:4px;"><i class="ti ti-funnel" style="color:var(--primary-blue);"></i> Admission Funnel Pipeline</div>
                                        <div style="display:flex; flex-direction:column; gap:6px; font-size:8px; flex-grow:1; justify-content:center;">
                                            <div>
                                                <div style="display:flex; justify-content:space-between;"><span>Applications received</span><strong>1</strong></div>
                                                <div class="progress" style="height:4px; margin-top:2px; background-color:#ebecf0;"><div class="progress-bar" style="width:100%; background-color:var(--primary-blue);"></div></div>
                                            </div>
                                            <div>
                                                <div style="display:flex; justify-content:space-between;"><span>Document verified</span><strong>1</strong></div>
                                                <div class="progress" style="height:4px; margin-top:2px; background-color:#ebecf0;"><div class="progress-bar" style="width:100%; background-color:var(--primary-blue);"></div></div>
                                            </div>
                                            <div>
                                                <div style="display:flex; justify-content:space-between; color:#a5adba;"><span>Under review</span><strong>0</strong></div>
                                                <div class="progress" style="height:4px; margin-top:2px; background-color:#ebecf0;"></div>
                                            </div>
                                            <div>
                                                <div style="display:flex; justify-content:space-between; color:#a5adba;"><span>Interviews scheduled</span><strong>0</strong></div>
                                                <div class="progress" style="height:4px; margin-top:2px; background-color:#ebecf0;"></div>
                                            </div>
                                            <div>
                                                <div style="display:flex; justify-content:space-between; color:#a5adba;"><span>Decisions made</span><strong>0</strong></div>
                                                <div class="progress" style="height:4px; margin-top:2px; background-color:#ebecf0;"></div>
                                            </div>
                                            <div>
                                                <div style="display:flex; justify-content:space-between; color:#a5adba;"><span>Enrollment confirmed</span><strong>1</strong></div>
                                                <div class="progress" style="height:4px; margin-top:2px; background-color:#ebecf0;"><div class="progress-bar" style="width:100%; background-color:#36b37e;"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Overlapping Mobile Phone Mockup - Parent Dashboard eyitayo Azzan -->
                    <div class="mobile-mockup">
                        <div class="mobile-screen">
                            <div class="mobile-nav-bar">
                                <i class="ti ti-menu"></i>
                                <span style="font-weight:700; font-size:10px;">Bluefield International School</span>
                                <span style="font-size:7px;">Logout</span>
                            </div>
                            <div style="padding:10px 10px 0 10px; display:flex; justify-content:space-between; align-items:center;">
                                <strong style="font-size:11px;">Dashboard</strong>
                                <span style="font-size:7px; color:var(--text-muted);"><i class="ti ti-calendar"></i> Tue, Jun 30, 2026</span>
                            </div>
                            <!-- Parent Welcome gradient card -->
                            <div class="mobile-welcome-card">
                                <div style="font-size:8px; opacity:0.8;">Welcome back, Parent</div>
                                <div style="font-size:14px; font-weight:800; margin:2px 0;">eyitayo Azzan</div>
                                <div style="font-size:7px; opacity:0.9; margin-top:4px; display:flex; flex-direction:column; gap:1.5px;">
                                    <span>👤 eyitayo Azzan</span>
                                    <span>🏫 Primary 2</span>
                                    <span>🔑 Admission No: SCH/2026/00001</span>
                                </div>
                            </div>
                            <!-- Metrics 2x2 grid -->
                            <div class="mobile-metrics-grid">
                                <div class="mobile-metric-card">
                                    <div style="display:flex; justify-content:space-between; align-items:center;"><i class="ti ti-calendar-event" style="color:var(--primary-blue);"></i> <strong style="color:#36b37e;">100%</strong></div>
                                    <div class="mobile-card-title">Attendance rate</div>
                                    <div class="progress" style="height:3px; margin-top:4px; background-color:#e2e8f0;"><div class="progress-bar bg-success" style="width:100%;"></div></div>
                                </div>
                                <div class="mobile-metric-card">
                                    <div style="display:flex; justify-content:space-between; align-items:center;"><i class="ti ti-file-text" style="color:var(--primary-blue);"></i> <strong style="color:#36b37e;">NO</strong></div>
                                    <div class="mobile-card-title">Fees Up to Date</div>
                                </div>
                                <div class="mobile-metric-card">
                                    <div style="display:flex; justify-content:space-between; align-items:center;"><i class="ti ti-volume-up" style="color:var(--primary-blue);"></i> <strong>0</strong></div>
                                    <div class="mobile-card-title">Active Announcements</div>
                                </div>
                                <div class="mobile-metric-card">
                                    <div style="display:flex; justify-content:space-between; align-items:center;"><i class="ti ti-book" style="color:var(--primary-blue);"></i> <strong>0</strong></div>
                                    <div class="mobile-card-title">Academic Subjects</div>
                                </div>
                            </div>
                            <!-- Attendance grid -->
                            <div class="mobile-panel-card" style="margin-top:8px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-weight:700;">
                                    <span>Attendance Grid (June)</span>
                                    <span style="font-size:6px; color:var(--text-muted);">✓ Present | X Absent | Late</span>
                                </div>
                                <div style="display:flex; flex-wrap:wrap; gap:1.5px;">
                                    <?php for($i=1; $i<=30; $i++): ?>
                                        <div class="mobile-grid-day"><?= $i ?></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <!-- Child Schedule Today -->
                            <div class="mobile-panel-card" style="margin-top:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; font-weight:700;">
                                    <span>Child Schedule Today</span>
                                    <span style="font-size:8px; color:var(--primary-blue);">Full timetable →</span>
                                </div>
                                <p style="color:var(--text-muted); font-size:8px; margin:10px 0 0 0; text-align:center;">No classes scheduled for today.</p>
                            </div>
                            <!-- Recent Child Grades -->
                            <div class="mobile-panel-card" style="margin-top:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; font-weight:700;">
                                    <span>Recent Child Grades</span>
                                    <span style="font-size:8px; color:var(--primary-blue);">View all →</span>
                                </div>
                                <p style="color:var(--text-muted); font-size:8px; margin:10px 0 0 0; text-align:center;">No results recorded yet.</p>
                            </div>
                            <!-- Recent Notifications -->
                            <div class="mobile-panel-card" style="margin-top:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; font-weight:700; margin-bottom:8px;">
                                    <span>Recent Notifications</span>
                                    <span style="font-size:8px; color:var(--primary-blue);">View all →</span>
                                </div>
                                <div style="border:1px solid #deebff; background-color:#f4f5f7; border-radius:6px; padding:8px;">
                                    <div style="font-weight:700; font-size:8px; color:var(--primary-blue);">Welcome to Parent Portal!</div>
                                    <div style="color:var(--text-muted); font-size:7px; margin-top:2px;">Welcome to the parent portal. You can now monitor your child's results...</div>
                                    <div style="font-size:6px; color:#a5adba; margin-top:4px;">Jun 17, 11:14 PM</div>
                                </div>
                            </div>
                            <!-- Quick Actions -->
                            <div class="mobile-panel-card" style="margin-top:0;">
                                <div style="font-weight:700; margin-bottom:8px;">Quick Actions</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                                    <div class="mobile-action-btn" style="background-color:#ebf5ff; color:var(--primary-blue);"><i class="ti ti-calendar-stats"></i> View Attendance</div>
                                    <div class="mobile-action-btn" style="background-color:#eae6ff; color:#5243aa;"><i class="ti ti-file-analytics"></i> View Results</div>
                                    <div class="mobile-action-btn" style="background-color:#e3fcef; color:#006644;"><i class="ti ti-cash"></i> Pay School Fees</div>
                                    <div class="mobile-action-btn" style="background-color:#fff0b3; color:#a56104;"><i class="ti ti-calendar-time"></i> Child Timetable</div>
                                </div>
                                <div class="mobile-action-btn" style="background-color:#e6fcff; color:#008da6; margin-top:6px; text-align:center; width:100%;"><i class="ti ti-id"></i> Child ID Card</div>
                            </div>
                            <!-- Footer -->
                            <p style="text-align:center; color:var(--text-muted); font-size:8px; margin:16px 0;">Powered by SkySaving Tech Hub</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Inline Capabilities Features Banner -->
<section id="features" class="container features-banner">
    <div class="row g-4">
        <!-- 1. Admissions -->
        <div class="col-lg-4 col-md-6">
            <div class="feature-item-col">
                <div class="feature-item-icon" style="background-color:#eae6ff; color:#403294;"><i class="ti ti-edit"></i></div>
                <div>
                    <h3 class="feature-item-title">Admission & CBT</h3>
                    <p class="feature-item-desc">Online admissions, CBT exams and student enrollment made easy and secure.</p>
                </div>
            </div>
        </div>
        <!-- 2. QR Attendance -->
        <div class="col-lg-4 col-md-6">
            <div class="feature-item-col">
                <div class="feature-item-icon" style="background-color:#e3fcef; color:#006644;"><i class="ti ti-device-nfc"></i></div>
                <div>
                    <h3 class="feature-item-title">QR Attendance</h3>
                    <p class="feature-item-desc">Real-time QR/POS attendance with instant SMS & email alerts to parents.</p>
                </div>
            </div>
        </div>
        <!-- 3. School Management -->
        <div class="col-lg-4 col-md-6">
            <div class="feature-item-col">
                <div class="feature-item-icon" style="background-color:#deebff; color:#0747a6;"><i class="ti ti-school"></i></div>
                <div>
                    <h3 class="feature-item-title">School Management</h3>
                    <p class="feature-item-desc">Manage academics, classes, teachers, exams, results and promotions.</p>
                </div>
            </div>
        </div>
        <!-- 4. Fee & Payments -->
        <div class="col-lg-4 col-md-6 mt-lg-5">
            <div class="feature-item-col">
                <div class="feature-item-icon" style="background-color:#fffae6; color:#97600c;"><i class="ti ti-credit-card"></i></div>
                <div>
                    <h3 class="feature-item-title">Fee & Payments</h3>
                    <p class="feature-item-desc">Collect school fees online, track payments and generate invoices instantly.</p>
                </div>
            </div>
        </div>
        <!-- 5. Parent & Student Portals -->
        <div class="col-lg-4 col-md-6 mt-lg-5">
            <div class="feature-item-col">
                <div class="feature-item-icon" style="background-color:#ffebf5; color:#c71585;"><i class="ti ti-users"></i></div>
                <div>
                    <h3 class="feature-item-title">Parent & Student Portals</h3>
                    <p class="feature-item-desc">Dedicated portals for parents and students to stay informed and connected.</p>
                </div>
            </div>
        </div>
        <!-- 6. Reports & Analytics -->
        <div class="col-lg-4 col-md-6 mt-lg-5">
            <div class="feature-item-col">
                <div class="feature-item-icon" style="background-color:#ebe6ff; color:#5243aa;"><i class="ti ti-chart-bar"></i></div>
                <div>
                    <h3 class="feature-item-title">Reports & Analytics</h3>
                    <p class="feature-item-desc">Powerful dashboards and reports for data-driven decisions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dark Blue Statistics Bar -->
<section class="stats-counter-bar">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <div class="col-md-2 col-6">
                <div class="stats-item">
                    <div class="stats-icon"><i class="ti ti-school"></i></div>
                    <div>
                        <div class="stats-val">350+</div>
                        <div class="stats-label">Active Schools</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stats-item">
                    <div class="stats-icon"><i class="ti ti-users"></i></div>
                    <div>
                        <div class="stats-val">85,000+</div>
                        <div class="stats-label">Students</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stats-item">
                    <div class="stats-icon"><i class="ti ti-user-check"></i></div>
                    <div>
                        <div class="stats-val">4,500+</div>
                        <div class="stats-label">Teachers</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stats-item">
                    <div class="stats-icon"><i class="ti ti-history"></i></div>
                    <div>
                        <div class="stats-val">1.2M+</div>
                        <div class="stats-label">Attendance Records</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stats-item">
                    <div class="stats-icon"><i class="ti ti-shield-check"></i></div>
                    <div>
                        <div class="stats-val">99.9%</div>
                        <div class="stats-label">Uptime & Security</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Tiers -->
<section id="pricing" class="pricing-section">
    <div class="container">
        <div class="text-center mb-5">
            <span style="color:var(--primary-blue); font-weight:700; font-size:12px; letter-spacing:1px; text-transform:uppercase;">PRICING PLANS</span>
            <h2 class="fw-extrabold mt-2" style="font-size:36px; letter-spacing:-1px;">Choose the Perfect Plan for Your School</h2>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- 1. Trial Plan -->
            <div class="col-md-3">
                <div class="pricing-card">
                    <div class="pricing-name">Trial Plan</div>
                    <div class="pricing-desc">Perfect for new schools</div>
                    <div class="pricing-amt">₦0<span>/30 Days</span></div>
                    <ul class="pricing-list">
                        <li><i class="ti ti-check"></i> Full Access</li>
                        <li><i class="ti ti-check"></i> All Core Features</li>
                    </ul>
                    <a href="<?= url('register-school?plan=starter') ?>" class="btn-pricing btn-pricing-outline">Start Free Trial</a>
                </div>
            </div>

            <!-- 2. Basic Plan -->
            <div class="col-md-3">
                <div class="pricing-card">
                    <div class="pricing-name">Basic Plan</div>
                    <div class="pricing-desc">For small schools</div>
                    <div class="pricing-amt">₦25,000<span>/Year</span></div>
                    <ul class="pricing-list">
                        <li><i class="ti ti-check"></i> Up to 300 Students</li>
                        <li><i class="ti ti-check"></i> All Core Features</li>
                    </ul>
                    <a href="<?= url('register-school?plan=starter') ?>" class="btn-pricing btn-pricing-solid">Get Started</a>
                </div>
            </div>

            <!-- 3. Professional Plan -->
            <div class="col-md-3">
                <div class="pricing-card popular">
                    <div class="badge-pop">Most Popular</div>
                    <div class="pricing-name">Professional Plan</div>
                    <div class="pricing-desc">For growing schools</div>
                    <div class="pricing-amt">₦45,000<span>/Year</span></div>
                    <ul class="pricing-list">
                        <li><i class="ti ti-check"></i> Up to 1,000 Students</li>
                        <li><i class="ti ti-check"></i> All Premium Features</li>
                    </ul>
                    <a href="<?= url('register-school?plan=professional') ?>" class="btn-pricing btn-pricing-solid">Get Started</a>
                </div>
            </div>

            <!-- 4. Enterprise Plan -->
            <div class="col-md-3">
                <div class="pricing-card">
                    <div class="pricing-name">Enterprise Plan</div>
                    <div class="pricing-desc">For large institutions</div>
                    <div class="pricing-amt">₦75,000+<span>/Year</span></div>
                    <ul class="pricing-list">
                        <li><i class="ti ti-check"></i> Unlimited Students</li>
                        <li><i class="ti ti-check"></i> All Premium Features</li>
                    </ul>
                    <a href="<?= url('register-school?plan=enterprise') ?>" class="btn-pricing btn-pricing-outline">Contact Sales</a>
                </div>
            </div>
        </div>

        <div class="pricing-check-row">
            <span><i class="ti ti-circle-check-filled"></i> Free Installation</span>
            <span><i class="ti ti-circle-check-filled"></i> Free Training</span>
            <span><i class="ti ti-circle-check-filled"></i> Free Updates</span>
            <span><i class="ti ti-circle-check-filled"></i> 24/7 Support</span>
            <span><i class="ti ti-circle-check-filled"></i> Secure & Reliable</span>
        </div>
    </div>
</section>

<!-- Powerful Features Section -->
<section class="powerful-features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-extrabold" style="font-size:36px; letter-spacing:-1px;">Powerful Features for Modern Schools</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="feature-box-card">
                    <div class="feature-box-icon" style="background-color:#eae6ff; color:#403294;"><i class="ti ti-device-nfc"></i></div>
                    <h3 class="feature-box-title">Android POS Attendance</h3>
                    <p class="feature-box-desc">Use Android POS terminals for fast QR attendance with offline support and auto sync.</p>
                    <a href="#" class="feature-box-link">Learn More <i class="ti ti-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-box-card">
                    <div class="feature-box-icon" style="background-color:#e3fcef; color:#006644;"><i class="ti ti-messages"></i></div>
                    <h3 class="feature-box-title">Instant Parent SMS</h3>
                    <p class="feature-box-desc">Automatically send SMS alerts to parents for attendance, fees and announcements.</p>
                    <a href="#" class="feature-box-link">Learn More <i class="ti ti-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="feature-box-card">
                    <div class="feature-box-icon" style="background-color:#deebff; color:#0747a6;"><i class="ti ti-credit-card"></i></div>
                    <h3 class="feature-box-title">Online Payments</h3>
                    <p class="feature-box-desc">Accept secure payments online with Paystack, Flutterwave and Monnify.</p>
                    <a href="#" class="feature-box-link">Learn More <i class="ti ti-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mt-lg-4">
                <div class="feature-box-card">
                    <div class="feature-box-icon" style="background-color:#fffae6; color:#a56104;"><i class="ti ti-file-text"></i></div>
                    <h3 class="feature-box-title">Comprehensive Reports</h3>
                    <p class="feature-box-desc">Get detailed analytics and reports for students, staff, attendance, fees and more.</p>
                    <a href="#" class="feature-box-link">Learn More <i class="ti ti-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mt-lg-4">
                <div class="feature-box-card">
                    <div class="feature-box-icon" style="background-color:#ffebf5; color:#c71585;"><i class="ti ti-server"></i></div>
                    <h3 class="feature-box-title">Multi-Tenant SaaS</h3>
                    <p class="feature-box-desc">Built from scratch with secure multi-tenant architecture and isolated data and more.</p>
                    <a href="#" class="feature-box-link">Learn More <i class="ti ti-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How EduCore Works (Timeline Steps) -->
<section class="how-it-works-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-extrabold" style="font-size:36px; letter-spacing:-1px;">How EduCore Works</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-2 col-md-4 col-6 works-step-col">
                <div class="works-step-card">
                    <div class="works-step-icon"><i class="ti ti-edit"></i></div>
                    <h4 class="works-step-title">1. Register School</h4>
                    <p class="works-step-desc">Create your school account in minutes.</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 works-step-col">
                <div class="works-step-card">
                    <div class="works-step-icon"><i class="ti ti-list-check"></i></div>
                    <h4 class="works-step-title">2. Choose Plan</h4>
                    <p class="works-step-desc">Select the perfect plan for your school.</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 works-step-col">
                <div class="works-step-card">
                    <div class="works-step-icon"><i class="ti ti-credit-card"></i></div>
                    <h4 class="works-step-title">3. Make Payment</h4>
                    <p class="works-step-desc">Pay securely online and get started.</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 works-step-col">
                <div class="works-step-card">
                    <div class="works-step-icon"><i class="ti ti-users-group"></i></div>
                    <h4 class="works-step-title">4. Setup & Onboard</h4>
                    <p class="works-step-desc">We set up your school and provide training.</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 works-step-col">
                <div class="works-step-card">
                    <div class="works-step-icon"><i class="ti ti-device-laptop"></i></div>
                    <h4 class="works-step-title">5. Start Using</h4>
                    <p class="works-step-desc">Manage your school with powerful tools.</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 works-step-col">
                <div class="works-step-card">
                    <div class="works-step-icon"><i class="ti ti-trending-up"></i></div>
                    <h4 class="works-step-title">6. Grow & Succeed</h4>
                    <p class="works-step-desc">Deliver better education and grow together.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted by Schools Across Nigeria (Testimonial Cards) -->
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-extrabold" style="font-size:36px; letter-spacing:-1px;">Trusted by Schools Across Nigeria</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                    </div>
                    <p class="testimonial-text">
                        "EduCore has transformed how we manage our school. Attendance tracking and SMS alerts are a game changer!"
                    </p>
                    <div class="testimonial-profile">
                        <div class="testimonial-avatar" style="background:#0052cc;">MA</div>
                        <div>
                            <div class="testimonial-name">Mrs. Adebayo</div>
                            <div class="testimonial-meta">Principal, Greenfield Academy</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                    </div>
                    <p class="testimonial-text">
                        "The platform is easy to use and the support team is always there to help. Highly recommended!"
                    </p>
                    <div class="testimonial-profile">
                        <div class="testimonial-avatar" style="background:#36b37e;">MI</div>
                        <div>
                            <div class="testimonial-name">Mr. Ibrahim</div>
                            <div class="testimonial-meta">Proprietor, Royal College</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i><i class="ti ti-star-filled"></i>
                    </div>
                    <p class="testimonial-text">
                        "We can now collect fees online and parents love the instant SMS notifications."
                    </p>
                    <div class="testimonial-profile">
                        <div class="testimonial-avatar" style="background:#ff5630;">MO</div>
                        <div>
                            <div class="testimonial-name">Mrs. Okeke</div>
                            <div class="testimonial-meta">Bursar, Prime International School</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex; justify-content:center; gap:6px; margin-top:30px;">
            <span style="width:8px; height:8px; border-radius:50%; background-color:var(--primary-blue);"></span>
            <span style="width:8px; height:8px; border-radius:50%; background-color:var(--border-color);"></span>
            <span style="width:8px; height:8px; border-radius:50%; background-color:var(--border-color);"></span>
        </div>
    </div>
</section>

<!-- Blue Banner Footer CTA -->
<section class="container py-5">
    <div class="bottom-cta-banner">
        <div>
            <h2>Ready to Transform Your School?</h2>
            <p class="mb-0" style="font-size:14px; opacity:0.9; max-width:600px;">
                Join hundreds of schools already using EduCore to simplify their operations and focus on what matters most — education.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:3px; flex-wrap:wrap; position:relative; z-index:5;">
            <a href="#" class="btn btn-light fw-bold py-2.5 px-4" style="color:var(--primary-blue); font-size:14px; border-radius:6px; margin:5px;"><i class="ti ti-calendar"></i> Book a Demo</a>
            <a href="<?= url('register-school') ?>" class="btn fw-bold py-2.5 px-4" style="font-size:14px; border-radius:6px; background-color:#36b37e; color:#fff; border:none; margin:5px;">Start Free Trial <i class="ti ti-arrow-right"></i></a>
        </div>
        <!-- Right side graphic placeholder overlaying -->
        <div style="position:absolute; right:20px; bottom:-10px; font-size:120px; opacity:0.1; color:#fff; pointer-events:none;"><i class="ti ti-school"></i></div>
    </div>
</section>

<!-- Latest News & Updates (Blog Card list) -->
<section class="blog-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-extrabold" style="font-size:36px; letter-spacing:-1px;">Latest News & Updates</h2>
        </div>
        <div class="row g-4">
            <!-- Blog 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-holder"><i class="ti ti-device-laptop"></i></div>
                    <div class="blog-body">
                        <span class="blog-tag">Product Updates • May 15, 2026</span>
                        <h4 class="blog-title">EduCore 2.2 — New Features & Improvements</h4>
                        <p class="blog-text">Discover the latest features and performance enhancements in EduCore 2.2.</p>
                        <a href="#" class="blog-link">Read More <i class="ti ti-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <!-- Blog 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-holder" style="background: linear-gradient(135deg, #36b37e 0%, #006644 100%);"><i class="ti ti-school"></i></div>
                    <div class="blog-body">
                        <span class="blog-tag">Education • May 10, 2026</span>
                        <h4 class="blog-title">5 Ways to Improve School Management</h4>
                        <p class="blog-text">Practical tips for school leaders to improve efficiency and student outcomes.</p>
                        <a href="#" class="blog-link">Read More <i class="ti ti-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <!-- Blog 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-holder" style="background: linear-gradient(135deg, #ffab00 0%, #a56104 100%);"><i class="ti ti-device-nfc"></i></div>
                    <div class="blog-body">
                        <span class="blog-tag">Tips & Tricks • May 5, 2026</span>
                        <h4 class="blog-title">How to Use QR Attendance Effectively</h4>
                        <p class="blog-text">Best practices for implementing QR attendance in your school.</p>
                        <a href="#" class="blog-link">Read More <i class="ti ti-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <!-- Blog 4 -->
            <div class="col-lg-3 col-md-6">
                <div class="blog-card">
                    <div class="blog-img-holder" style="background: linear-gradient(135deg, #ff5630 0%, #bf2600 100%);"><i class="ti ti-shopping-bag"></i></div>
                    <div class="blog-body">
                        <span class="blog-tag">Announcement • Apr 28, 2026</span>
                        <h4 class="blog-title">SkySavingTech Hub Marketplace is Live!</h4>
                        <p class="blog-text">Explore add-ons, SMS credits, POS devices and more in our new marketplace.</p>
                        <a href="#" class="blog-link">Read More <i class="ti ti-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted by Leading Schools Horizontal list -->
<section class="marquee-section text-center">
    <div class="container">
        <h5 style="color:var(--text-muted); font-size:12px; text-transform:uppercase; font-weight:700; margin-bottom:24px; letter-spacing:1px;">Trusted by Leading Schools</h5>
        <div style="display:flex; justify-content:center; align-items:center; gap:20px; flex-wrap:wrap;">
            <span class="marquee-logo-badge"><i class="ti ti-leaf"></i> Greenfield Academy</span>
            <span class="marquee-logo-badge"><i class="ti ti-crown"></i> Royal College</span>
            <span class="marquee-logo-badge"><i class="ti ti-star"></i> Prime International</span>
            <span class="marquee-logo-badge"><i class="ti ti-book"></i> Wisdom Academy</span>
            <span class="marquee-logo-badge"><i class="ti ti-rocket"></i> Future Minds</span>
            <span class="marquee-logo-badge"><i class="ti ti-school"></i> Bluefield College</span>
            <span class="marquee-logo-badge"><i class="ti ti-award"></i> Victory Academy</span>
        </div>
    </div>
</section>

<!-- Premium Footer -->
<footer class="premium-footer">
    <div class="container">
        <div class="row g-4">
            <!-- Left Info Block -->
            <div class="col-lg-3 col-md-6">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div style="width:36px; height:36px; border-radius:6px; background-color:#ffffff; display:flex; align-items:center; justify-content:center; color:#0b1a30; font-size:20px;">
                        <i class="ti ti-school" style="color:#0052cc;"></i>
                    </div>
                    <div>
                        <div style="font-weight:800; font-size:20px; color:#ffffff; font-family:var(--font-headings); line-height:1;">EduCore</div>
                        <div style="font-size:9px; color:#97a0af; margin-top:2px;">by SkySavingTech</div>
                    </div>
                </div>
                <p class="footer-desc">
                    The complete school management platform trusted by hundreds of schools across Nigeria.
                </p>
                <div class="footer-socials">
                    <a href="#" class="footer-social-link"><i class="ti ti-brand-facebook"></i></a>
                    <a href="#" class="footer-social-link"><i class="ti ti-brand-x"></i></a>
                    <a href="#" class="footer-social-link"><i class="ti ti-brand-linkedin"></i></a>
                    <a href="#" class="footer-social-link"><i class="ti ti-brand-youtube"></i></a>
                    <a href="#" class="footer-social-link"><i class="ti ti-brand-instagram"></i></a>
                </div>
            </div>

            <!-- Product Links -->
            <div class="col-lg-1.5 col-md-3 col-6">
                <div class="footer-title">Product</div>
                <ul class="footer-links-list">
                    <li><a href="#">Features</a></li>
                    <li><a href="#">School Admin</a></li>
                    <li><a href="#">Teacher Portal</a></li>
                    <li><a href="#">Parent Portal</a></li>
                    <li><a href="#">Student Portal</a></li>
                    <li><a href="#">Android POS</a></li>
                </ul>
            </div>

            <!-- Solutions Links -->
            <div class="col-lg-1.5 col-md-3 col-6 ms-lg-4">
                <div class="footer-title">Solutions</div>
                <ul class="footer-links-list">
                    <li><a href="#">For Primary Schools</a></li>
                    <li><a href="#">For Secondary Schools</a></li>
                    <li><a href="#">For Colleges</a></li>
                    <li><a href="#">SaaS (Cloud)</a></li>
                    <li><a href="#">Self-Hosted</a></li>
                    <li><a href="#">Mobile Apps</a></li>
                </ul>
            </div>

            <!-- Company Links -->
            <div class="col-lg-1.5 col-md-3 col-6 ms-lg-4">
                <div class="footer-title">Company</div>
                <ul class="footer-links-list">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Partners</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Book a Demo</a></li>
                </ul>
            </div>

            <!-- Marketplace Links -->
            <div class="col-lg-1.5 col-md-3 col-6 ms-lg-4">
                <div class="footer-title">Marketplace</div>
                <ul class="footer-links-list">
                    <li><a href="#">Buy SMS Credits</a></li>
                    <li><a href="#">Android POS Terminals</a></li>
                    <li><a href="#">Custom Mobile Apps</a></li>
                    <li><a href="#">Premium Support</a></li>
                    <li><a href="#">Training & Onboarding</a></li>
                    <li><a href="#">More Add-ons</a></li>
                </ul>
            </div>

            <!-- Resources Links -->
            <div class="col-lg-1.5 col-md-3 col-6 ms-lg-4">
                <div class="footer-title">Resources</div>
                <ul class="footer-links-list">
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Video Tutorials</a></li>
                    <li><a href="#">API Reference</a></li>
                    <li><a href="#">System Status</a></li>
                    <li><a href="#">Updates</a></li>
                </ul>
            </div>

            <!-- Newsletter Block -->
            <div class="col-lg-3 col-md-6 mt-lg-0 mt-4 ms-lg-4">
                <div class="newsletter-card">
                    <div class="newsletter-title">Subscribe to our newsletter</div>
                    <div class="newsletter-desc">Get product updates, tips and news delivered to your inbox.</div>
                    <form method="POST" action="#">
                        <?= csrf_field() ?>
                        <input type="email" name="email" class="newsletter-input" placeholder="Enter your email" required>
                        <button type="submit" class="btn-subscribe">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom bar copyright and compliance badges -->
        <div class="footer-bottom-bar">
            <div>© 2026 SkySavingTech Limited. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Refund Policy</a>
                <a href="#">Security</a>
                <a href="#">SLA</a>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="provider-badge-pill">Paystack</span>
                <span class="provider-badge-pill">Monnify</span>
                <span class="provider-badge-pill">Flutterwave</span>
                <span style="color:rgba(255,255,255,0.15)">|</span>
                <span style="font-size:10px; color:#ffffff; font-weight:700; border:1px solid rgba(255,255,255,0.12); border-radius:5px; padding:4px 10px; background-color:rgba(255,255,255,0.03); display:inline-flex; align-items:center; height:28px;"><i class="ti ti-shield-check" style="margin-right:4px;"></i> PCI-DSS COMPLIANT</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
