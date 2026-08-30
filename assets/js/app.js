(function () {
    const root = document.documentElement;
    const saved = localStorage.getItem('theme') || 'auto';
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.setAttribute('data-theme', saved === 'auto' ? (prefersDark ? 'dark' : 'light') : saved);

    function syncThemeButtons() {
        const isDark = root.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('#themeToggle').forEach((button) => {
            button.innerHTML = isDark ? '<i class="ti ti-sun"></i> Light mode' : '<i class="ti ti-moon"></i> Dark mode';
        });
    }

    document.querySelectorAll('#themeToggle').forEach((button) => {
        button.addEventListener('click', () => {
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            syncThemeButtons();
        });
    });
    syncThemeButtons();

    if (document.querySelector('.sidebar')) {
        const probe = document.createElement('i');
        probe.className = 'ti ti-layout-dashboard';
        probe.style.position = 'absolute';
        probe.style.left = '-9999px';
        document.body.appendChild(probe);
        requestAnimationFrame(() => {
            const fontFamily = window.getComputedStyle(probe, '::before').fontFamily || '';
            if (!fontFamily.toLowerCase().includes('tabler')) {
                root.classList.add('sidebar-icons-fallback');
            }
            probe.remove();
        });
    }

    const adminShell = document.querySelector('.admin-shell');
    const adminMenuToggle = document.getElementById('adminMenuToggle');
    const adminSidebarOverlay = document.getElementById('adminSidebarOverlay');
    const adminSidebarClose = document.getElementById('adminSidebarClose');
    function setAdminSidebar(open) {
        if (!adminShell) return;
        adminShell.classList.toggle('sidebar-open', open);
        adminMenuToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
    }
    adminMenuToggle?.addEventListener('click', () => setAdminSidebar(!adminShell?.classList.contains('sidebar-open')));
    adminSidebarOverlay?.addEventListener('click', () => setAdminSidebar(false));
    adminSidebarClose?.addEventListener('click', () => setAdminSidebar(false));
    document.querySelectorAll('.sidebar a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 900px)').matches) setAdminSidebar(false);
        });
    });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setAdminSidebar(false);
    });
    window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 900px)').matches) setAdminSidebar(false);
    });

    document.querySelectorAll('.edit-class').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('classId').value = button.dataset.id;
            document.getElementById('className').value = button.dataset.name;
            document.getElementById('classSort').value = button.dataset.sort;
        });
    });

    document.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.closest('.dropzone')?.querySelector('.file-name');
            if (label && input.files[0]) label.textContent = input.files[0].name;
        });
    });

    if (window.jQuery && jQuery.fn.DataTable) {
        jQuery('.data-table').DataTable({ pageLength: 10, responsive: true });
    }

    document.querySelectorAll('form[action*="/delete"], form[action*="/reject"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (typeof Swal === 'undefined') return;
            event.preventDefault();
            Swal.fire({
                title: 'Confirm action',
                text: 'This update will be recorded in the audit log.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0b3d91'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    function chart(id, type) {
        const canvas = document.getElementById(id);
        if (!canvas || typeof Chart === 'undefined') return;
        new Chart(canvas, {
            type,
            data: {
                labels: JSON.parse(canvas.dataset.labels || '[]'),
                datasets: [{
                    data: JSON.parse(canvas.dataset.values || '[]'),
                    backgroundColor: ['#0b3d91', '#1056c2', '#f4b942', '#20a161', '#d64545', '#5b6ee1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: type === 'doughnut' } }
            }
        });
    }

    chart('classChart', 'doughnut');
    chart('monthChart', 'bar');

    const dashboardSearch = document.getElementById('saSearchBox');
    const dashboardStatus = document.getElementById('saStatusFilter');
    const dashboardRows = document.querySelectorAll('#saAppTable tbody tr');
    function filterDashboardTable() {
        const query = (dashboardSearch?.value || '').toLowerCase();
        const status = dashboardStatus?.value || '';
        dashboardRows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status || '';
            row.style.display = text.includes(query) && (!status || rowStatus === status) ? '' : 'none';
        });
    }
    dashboardSearch?.addEventListener('input', filterDashboardTable);
    dashboardStatus?.addEventListener('change', filterDashboardTable);

    const payButton = document.getElementById('payButton');
    if (payButton && typeof PaystackPop !== 'undefined') {
        payButton.addEventListener('click', () => {
            const handler = PaystackPop.setup({
                key: payButton.dataset.key,
                email: payButton.dataset.email,
                amount: payButton.dataset.amount,
                ref: payButton.dataset.reference,
                callback: function () {
                    window.location.href = payButton.dataset.callback;
                }
            });
            handler.openIframe();
        });
    }

    const admissionForm = document.getElementById('admissionForm');
    if (admissionForm) {
        const sections = Array.from(admissionForm.querySelectorAll('.admission-section'));
        const requiredFields = Array.from(admissionForm.querySelectorAll('[required]'));
        const progressLabel = document.querySelector('[data-progress-label]');
        const progressFill = document.querySelector('[data-progress-fill]');

        function updateAdmissionProgress() {
            const completed = requiredFields.filter((field) => {
                if (field.type === 'file') return field.files && field.files.length > 0;
                return String(field.value || '').trim() !== '';
            }).length;
            const percent = requiredFields.length ? Math.round((completed / requiredFields.length) * 100) : 0;
            if (progressLabel) progressLabel.textContent = percent + '%';
            if (progressFill) progressFill.style.width = percent + '%';

            sections.forEach((section) => {
                const sectionRequired = Array.from(section.querySelectorAll('[required]'));
                const complete = sectionRequired.length > 0 && sectionRequired.every((field) => {
                    if (field.type === 'file') return field.files && field.files.length > 0;
                    return String(field.value || '').trim() !== '';
                });
                section.classList.toggle('is-complete', complete);
            });
        }

        sections.forEach((section) => {
            const header = section.querySelector('.admission-section-header');
            header?.addEventListener('click', () => {
                const isOpen = section.classList.contains('is-open');
                sections.forEach((item) => {
                    item.classList.remove('is-open');
                    item.querySelector('.admission-section-header')?.setAttribute('aria-expanded', 'false');
                });
                if (!isOpen) {
                    section.classList.add('is-open');
                    header.setAttribute('aria-expanded', 'true');
                }
            });
        });

        admissionForm.querySelectorAll('[data-upload-box]').forEach((box) => {
            const input = box.querySelector('input[type="file"]');
            const hint = box.querySelector('.file-name');
            const maxSize = 2 * 1024 * 1024;

            function syncFileLabel() {
                const file = input?.files?.[0];
                if (!file) {
                    box.classList.remove('is-uploaded');
                    if (hint) hint.textContent = 'Drag, drop, or choose file';
                    updateAdmissionProgress();
                    return;
                }

                if (file.size > maxSize) {
                    alert('File size must not exceed 2MB.');
                    input.value = '';
                    syncFileLabel();
                    return;
                }

                box.classList.add('is-uploaded');
                if (hint) hint.textContent = file.name;
                updateAdmissionProgress();
            }

            input?.addEventListener('change', syncFileLabel);
            box.addEventListener('dragover', (event) => {
                event.preventDefault();
                box.classList.add('is-dragging');
            });
            box.addEventListener('dragleave', () => box.classList.remove('is-dragging'));
            box.addEventListener('drop', (event) => {
                event.preventDefault();
                box.classList.remove('is-dragging');
                if (input && event.dataTransfer?.files?.length) {
                    input.files = event.dataTransfer.files;
                    syncFileLabel();
                }
            });
        });

        requiredFields.forEach((field) => {
            field.addEventListener('input', updateAdmissionProgress);
            field.addEventListener('change', updateAdmissionProgress);
        });

        admissionForm.addEventListener('submit', (event) => {
            const firstInvalid = requiredFields.find((field) => {
                if (field.type === 'file') return !field.files || field.files.length === 0;
                return String(field.value || '').trim() === '';
            });
            if (firstInvalid) {
                const section = firstInvalid.closest('.admission-section');
                if (section && !section.classList.contains('is-open')) {
                    sections.forEach((item) => {
                        item.classList.remove('is-open');
                        item.querySelector('.admission-section-header')?.setAttribute('aria-expanded', 'false');
                    });
                    section.classList.add('is-open');
                    section.querySelector('.admission-section-header')?.setAttribute('aria-expanded', 'true');
                }
            }
        });

        updateAdmissionProgress();
    }

    const landingNavbar = document.getElementById('landingNavbar');
    if (landingNavbar) {
        window.addEventListener('scroll', () => {
            landingNavbar.classList.toggle('scrolled', window.scrollY > 60);
        });
    }

    const landingMenuToggle = document.getElementById('landingMenuToggle');
    const landingBody = document.querySelector('.landing-body');
    function setLandingMenu(open) {
        landingBody?.classList.toggle('landing-menu-open', open);
        landingMenuToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    landingMenuToggle?.addEventListener('click', () => {
        setLandingMenu(!landingBody?.classList.contains('landing-menu-open'));
    });
    document.querySelectorAll('.landing-mobile-menu a').forEach((link) => {
        link.addEventListener('click', () => setLandingMenu(false));
    });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setLandingMenu(false);
    });
    window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 900px)').matches) setLandingMenu(false);
    });

    const cursor = document.getElementById('landingCursor');
    const ring = document.getElementById('landingCursorRing');
    if (cursor && ring && window.matchMedia('(pointer:fine)').matches) {
        let mouseX = 0;
        let mouseY = 0;
        let ringX = 0;
        let ringY = 0;
        document.addEventListener('mousemove', (event) => {
            mouseX = event.clientX;
            mouseY = event.clientY;
            cursor.style.left = mouseX - 5 + 'px';
            cursor.style.top = mouseY - 5 + 'px';
        });
        function animateLandingRing() {
            ringX += (mouseX - ringX) * .12;
            ringY += (mouseY - ringY) * .12;
            ring.style.left = ringX - 18 + 'px';
            ring.style.top = ringY - 18 + 'px';
            requestAnimationFrame(animateLandingRing);
        }
        animateLandingRing();
        document.querySelectorAll('.landing-body a, .landing-body button, .landing-pillar, .landing-t-card, .landing-fee-card').forEach((element) => {
            element.addEventListener('mouseenter', () => {
                cursor.style.transform = 'scale(2)';
                ring.style.transform = 'scale(1.5)';
            });
            element.addEventListener('mouseleave', () => {
                cursor.style.transform = 'scale(1)';
                ring.style.transform = 'scale(1)';
            });
        });
    }

    if (document.querySelector('.landing-body')) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) entry.target.classList.add('show');
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));

        document.querySelectorAll('.landing-body a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', (event) => {
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    event.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }
})();
