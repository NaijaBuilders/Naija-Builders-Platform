import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.body.classList.add('is-ready');

    initializeMarketingTheme();
    initializeMarketingNav();
    initializeMarketingReveal(reducedMotion);
    initializeMarketingFaq();
    initializeWaitlistForms();

    if (!reducedMotion) {
        initializeMarketingParallax();
        initializeTiltCards();
        initializeMagneticButtons();
    }
});

function initializeMarketingTheme() {
    const toggle = document.querySelector('[data-theme-toggle]');
    const root = document.documentElement;

    if (!toggle) {
        return;
    }

    const persistTheme = (theme) => {
        root.setAttribute('data-theme', theme);

        try {
            localStorage.setItem('naijabuilders-theme', theme);
        } catch (error) {
            // Ignore storage errors.
        }

        document.cookie = 'naijabuilders-theme=' + encodeURIComponent(theme) + '; path=/; max-age=31536000; SameSite=Lax';
    };

    toggle.addEventListener('click', () => {
        const currentTheme = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        persistTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });
}

function initializeMarketingNav() {
    const nav = document.querySelector('[data-marketing-nav]');
    const toggle = document.querySelector('[data-mobile-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');

    if (nav) {
        let navIsScrolled = null;
        let navTicking = false;

        const syncNav = () => {
            const nextState = window.scrollY > 10;

            if (nextState !== navIsScrolled) {
                nav.classList.toggle('is-scrolled', nextState);
                navIsScrolled = nextState;
            }

            navTicking = false;
        };

        const requestNavSync = () => {
            if (!navTicking) {
                window.requestAnimationFrame(syncNav);
                navTicking = true;
            }
        };

        syncNav();
        window.addEventListener('scroll', requestNavSync, { passive: true });
    }

    if (!toggle || !menu) {
        return;
    }

    const closeMenu = () => {
        toggle.classList.remove('is-open');
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const shouldOpen = !menu.classList.contains('is-open');
        toggle.classList.toggle('is-open', shouldOpen);
        menu.classList.toggle('is-open', shouldOpen);
        toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
}

function initializeMarketingReveal(reducedMotion) {
    const revealElements = Array.from(document.querySelectorAll('.nb-section-reveal'));

    if (revealElements.length === 0) {
        return;
    }

    revealElements.forEach((element, index) => {
        element.style.setProperty('--nb-reveal-delay', Math.min((index % 6) * 70, 350) + 'ms');
    });

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.12,
    });

    revealElements.forEach((element) => observer.observe(element));
}

function initializeMarketingParallax() {
    const layers = Array.from(document.querySelectorAll('[data-parallax]')).map((element) => ({
        element,
        speed: parseFloat(element.getAttribute('data-parallax') || '0.08'),
        center: 0,
    }));

    if (layers.length === 0) {
        return;
    }

    const activeLayers = new Set();
    let ticking = false;
    let viewportHeight = window.innerHeight || 1;

    const cacheLayerPositions = () => {
        viewportHeight = window.innerHeight || 1;

        layers.forEach((layer) => {
            const rect = layer.element.getBoundingClientRect();
            layer.center = rect.top + window.scrollY + rect.height / 2;
        });
    };

    const update = () => {
        const scrollY = window.scrollY;

        activeLayers.forEach((layer) => {
            const distanceFromCenter = (layer.center - scrollY - viewportHeight / 2) / viewportHeight;
            const offset = Math.max(-42, Math.min(42, distanceFromCenter * layer.speed * -180));

            layer.element.style.translate = '0 ' + offset.toFixed(2) + 'px';
        });

        ticking = false;
    };

    const requestUpdate = () => {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    };

    if ('IntersectionObserver' in window) {
        const activeObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const layer = layers.find((candidate) => candidate.element === entry.target);

                if (!layer) {
                    return;
                }

                if (entry.isIntersecting) {
                    activeLayers.add(layer);
                } else {
                    activeLayers.delete(layer);
                }
            });

            requestUpdate();
        }, {
            rootMargin: '35% 0px',
            threshold: 0,
        });

        layers.forEach((layer) => activeObserver.observe(layer.element));
    } else {
        layers.forEach((layer) => activeLayers.add(layer));
    }

    cacheLayerPositions();
    update();
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', () => {
        cacheLayerPositions();
        requestUpdate();
    });
    window.addEventListener('load', () => {
        cacheLayerPositions();
        requestUpdate();
    });
}

function initializeTiltCards() {
    const tiltElements = Array.from(document.querySelectorAll('[data-tilt]'));

    tiltElements.forEach((element) => {
        let rect = null;

        element.addEventListener('pointerenter', () => {
            rect = element.getBoundingClientRect();
        });

        element.addEventListener('pointermove', (event) => {
            if (event.pointerType === 'touch') {
                return;
            }

            if (!rect) {
                rect = element.getBoundingClientRect();
            }

            const x = ((event.clientX - rect.left) / rect.width) - 0.5;
            const y = ((event.clientY - rect.top) / rect.height) - 0.5;

            element.style.setProperty('--nb-tilt-x', (y * -5).toFixed(2) + 'deg');
            element.style.setProperty('--nb-tilt-y', (x * 5).toFixed(2) + 'deg');
        });

        element.addEventListener('pointerleave', () => {
            rect = null;
            element.style.setProperty('--nb-tilt-x', '0deg');
            element.style.setProperty('--nb-tilt-y', '0deg');
        });
    });
}

function initializeMagneticButtons() {
    const buttons = Array.from(document.querySelectorAll('.nb-magnetic'));

    buttons.forEach((button) => {
        let rect = null;

        button.addEventListener('pointerenter', () => {
            rect = button.getBoundingClientRect();
        });

        button.addEventListener('pointermove', (event) => {
            if (event.pointerType === 'touch') {
                return;
            }

            if (!rect) {
                rect = button.getBoundingClientRect();
            }

            const x = (event.clientX - rect.left - rect.width / 2) * 0.16;
            const y = (event.clientY - rect.top - rect.height / 2) * 0.16;

            button.style.translate = x.toFixed(2) + 'px ' + y.toFixed(2) + 'px';
        });

        button.addEventListener('pointerleave', () => {
            rect = null;
            button.style.translate = '0 0';
        });
    });
}

function initializeMarketingFaq() {
    const triggers = Array.from(document.querySelectorAll('.nb-faq-trigger'));

    triggers.forEach((trigger) => {
        const panel = trigger.nextElementSibling;

        if (!panel) {
            return;
        }

        if (trigger.getAttribute('aria-expanded') === 'true') {
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }

        trigger.addEventListener('click', () => {
            const expanded = trigger.getAttribute('aria-expanded') === 'true';
            trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            panel.style.maxHeight = expanded ? '0px' : panel.scrollHeight + 'px';
        });
    });
}

function initializeWaitlistForms() {
    const forms = Array.from(document.querySelectorAll('[data-waitlist-form]'));

    forms.forEach((form) => {
        const message = form.querySelector('[data-waitlist-message]');

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const email = String(formData.get('email') || '').trim();

            if (!email || !email.includes('@')) {
                if (message) {
                    message.textContent = 'Enter a valid email address to join the waitlist.';
                }
                return;
            }

            const payload = {
                name: String(formData.get('name') || '').trim(),
                company: String(formData.get('company') || '').trim(),
                email,
                role: String(formData.get('role') || '').trim(),
                joinedAt: new Date().toISOString(),
            };

            try {
                localStorage.setItem('naijabuilders-waitlist-preview', JSON.stringify(payload));
            } catch (error) {
                // The acknowledgement can still be shown if storage is unavailable.
            }

            form.reset();

            if (message) {
                message.textContent = 'You are on the preview waitlist. We will share launch updates when the full flow opens.';
            }
        });
    });
}
