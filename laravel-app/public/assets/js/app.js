/* ============================================
   NaijaBuilders - JavaScript App
   Professional marketplace application
   ============================================ */

// Initialize app on DOM load
document.addEventListener('DOMContentLoaded', function() {
    initializeThemeToggle();
    initializeSideMenu();

    // Initialize profile dropdown menu
    initializeProfileDropdown();
    
    // Initialize form handlers
    initializeForms();
    
    // Initialize event listeners
    initializeEventListeners();

    // Auto-dismiss toast-style alerts globally
    initializeAlertToasts();

    // One-time onboarding tour for newly registered users
    initializeOnboardingTour();
});

const DEFAULT_TOAST_DURATION_MS = 3000;
const TOAST_FADE_DURATION_MS = 350;
const activeToastTimers = new WeakMap();

function getToastContainer() {
    let container = document.getElementById('app-toast-container');
    if (container) {
        return container;
    }

    container = document.createElement('div');
    container.id = 'app-toast-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'false');
    document.body.appendChild(container);
    return container;
}

function dismissToastElement(element) {
    if (!element || !element.isConnected || element.classList.contains('is-fading-out')) {
        return;
    }

    element.classList.add('is-fading-out');
    window.setTimeout(function () {
        if (element && element.isConnected) {
            element.remove();
        }
    }, TOAST_FADE_DURATION_MS);
}

function scheduleToastDismiss(element, durationMs) {
    if (!element) {
        return;
    }

    const existingTimer = activeToastTimers.get(element);
    if (existingTimer) {
        clearTimeout(existingTimer);
    }

    const safeDuration = Number.isFinite(durationMs) && durationMs > 0
        ? durationMs
        : DEFAULT_TOAST_DURATION_MS;

    const timer = window.setTimeout(function () {
        dismissToastElement(element);
        activeToastTimers.delete(element);
    }, safeDuration);

    activeToastTimers.set(element, timer);
}

function initializeAlertToasts() {
    const toastCandidates = document.querySelectorAll('.alert:not([data-no-auto-fade]):not([data-toast-init])');
    toastCandidates.forEach(function (alertElement) {
        alertElement.setAttribute('data-toast-init', 'true');

        const customDuration = parseInt(alertElement.getAttribute('data-auto-fade') || '', 10);
        const duration = Number.isFinite(customDuration) && customDuration > 0
            ? customDuration
            : DEFAULT_TOAST_DURATION_MS;

        scheduleToastDismiss(alertElement, duration);
    });
}

const toastObserver = new MutationObserver(function () {
    initializeAlertToasts();
});

if (document.body) {
    toastObserver.observe(document.body, { childList: true, subtree: true });
}

function initializeThemeToggle() {
    const toggleButton = document.getElementById('themeToggle');
    const toggleIcon = document.getElementById('themeToggleIcon');

    if (!toggleButton || !toggleIcon) {
        return;
    }

    const root = document.documentElement;
    const storedTheme = getStoredTheme();
    const currentTheme = (storedTheme === 'dark' || storedTheme === 'light')
        ? storedTheme
        : (root.getAttribute('data-theme') || 'light');
    root.setAttribute('data-theme', currentTheme);

    const setIcon = function (theme) {
        toggleIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
        toggleButton.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        toggleButton.setAttribute('title', theme === 'dark' ? 'Light mode' : 'Dark mode');
    };

    setIcon(currentTheme);

    toggleButton.addEventListener('click', function () {
        const nextTheme = (root.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
        root.setAttribute('data-theme', nextTheme);
        setIcon(nextTheme);
        persistTheme(nextTheme);
    });
}

function getStoredTheme() {
    try {
        const saved = localStorage.getItem('naijabuilders-theme');
        if (saved === 'dark' || saved === 'light') {
            return saved;
        }
    } catch (error) {
        // Ignore storage errors
    }

    const cookieMatch = document.cookie.match(/(?:^|; )naijabuilders-theme=([^;]+)/);
    const cookieTheme = cookieMatch ? decodeURIComponent(cookieMatch[1]) : '';

    return (cookieTheme === 'dark' || cookieTheme === 'light') ? cookieTheme : '';
}

function persistTheme(theme) {
    try {
        localStorage.setItem('naijabuilders-theme', theme);
    } catch (error) {
        // Ignore storage errors
    }

    document.cookie = 'naijabuilders-theme=' + encodeURIComponent(theme) + '; path=/; max-age=31536000; SameSite=Lax';
}

function initializeSideMenu() {
    const menu = document.getElementById('sideMenu');
    const backdrop = document.getElementById('sideMenuBackdrop');
    const openButton = document.getElementById('sideMenuToggle');
    const closeButton = document.getElementById('sideMenuClose');
    const sideMenuSearchForm = document.querySelector('.side-menu-search-form');

    if (!menu || !backdrop || !openButton || !closeButton) {
        return;
    }

    const openMenu = function () {
        menu.classList.add('open');
        backdrop.classList.add('open');
        menu.setAttribute('aria-hidden', 'false');
        openButton.setAttribute('aria-expanded', 'true');
    };

    const closeMenu = function () {
        menu.classList.remove('open');
        backdrop.classList.remove('open');
        menu.setAttribute('aria-hidden', 'true');
        openButton.setAttribute('aria-expanded', 'false');
    };

    openButton.addEventListener('click', openMenu);
    closeButton.addEventListener('click', closeMenu);
    backdrop.addEventListener('click', closeMenu);

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    if (sideMenuSearchForm) {
        sideMenuSearchForm.addEventListener('submit', closeMenu);
    }
}

function initializeProfileDropdown() {
    const userMenu = document.querySelector('.nav-user-menu');
    if (!userMenu) {
        return;
    }

    const profileButton = userMenu.querySelector('.btn-user-profile');
    if (!profileButton) {
        return;
    }

    profileButton.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();

        const isOpen = userMenu.classList.toggle('open');
        profileButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function(event) {
        if (!userMenu.contains(event.target)) {
            userMenu.classList.remove('open');
            profileButton.setAttribute('aria-expanded', 'false');
        }
    });
}

// Form Initialization
function initializeForms() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any client-side validation here
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }
        });
    });
}

// Event Listeners
function initializeEventListeners() {
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('sideMenu');
        const openButton = document.getElementById('sideMenuToggle');
        const backdrop = document.getElementById('sideMenuBackdrop');

        if (!menu || !openButton || !backdrop) {
            return;
        }

        if (!menu.contains(event.target) && !openButton.contains(event.target) && backdrop.classList.contains('open')) {
            menu.classList.remove('open');
            backdrop.classList.remove('open');
            menu.setAttribute('aria-hidden', 'true');
            openButton.setAttribute('aria-expanded', 'false');
        }
    });
}

function initializeOnboardingTour() {
    const shouldStartTour = document.body.getAttribute('data-start-onboarding-tour') === '1';
    if (!shouldStartTour) {
        return;
    }

    const primaryActionTarget = document.querySelector('[data-tour-id="primary-action"]');
    const primaryActionLabel = primaryActionTarget ? (primaryActionTarget.getAttribute('aria-label') || '').toLowerCase() : '';
    const primaryActionDescription = primaryActionLabel === 'analysis'
        ? 'Use this icon to open supplier analytics and track listing performance quickly.'
        : 'Use this cart icon to review selected materials before placing your order.';

    const tourSteps = [
        {
            id: 'menu',
            title: 'Main Menu',
            description: 'Tap this icon to open quick links like dashboard, messages, and profile settings.'
        },
        {
            id: 'search',
            title: 'Search Materials',
            description: 'Search for materials by name from anywhere on the site.'
        },
        {
            id: 'primary-action',
            title: primaryActionLabel === 'analysis' ? 'Analytics Shortcut' : 'Cart Shortcut',
            description: primaryActionDescription
        },
        {
            id: 'profile-menu',
            title: 'Profile Menu',
            description: 'Open your profile menu to access dashboard, messages, subscription, and account actions.'
        },
        {
            id: 'support',
            title: 'Support Assistant',
            description: 'Need help? Click here to contact support quickly.'
        },
        {
            id: 'theme-toggle',
            title: 'Theme Switch',
            description: 'Toggle between light and dark themes for your preferred reading comfort.'
        }
    ];

    const availableSteps = tourSteps.filter(function (step) {
        return document.querySelector('[data-tour-id="' + step.id + '"]') !== null;
    });

    if (availableSteps.length === 0) {
        return;
    }

    const overlay = document.createElement('div');
    overlay.className = 'tour-overlay';

    const panel = document.createElement('div');
    panel.className = 'tour-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    panel.innerHTML = [
        '<div class="tour-panel-progress"></div>',
        '<h4 class="tour-panel-title"></h4>',
        '<p class="tour-panel-description"></p>',
        '<div class="tour-panel-controls">',
            '<button type="button" class="btn btn-outline tour-back">Back</button>',
            '<button type="button" class="btn btn-outline tour-skip">Skip</button>',
            '<button type="button" class="btn btn-primary tour-next">Next</button>',
        '</div>'
    ].join('');

    const titleElement = panel.querySelector('.tour-panel-title');
    const descriptionElement = panel.querySelector('.tour-panel-description');
    const progressElement = panel.querySelector('.tour-panel-progress');
    const backButton = panel.querySelector('.tour-back');
    const skipButton = panel.querySelector('.tour-skip');
    const nextButton = panel.querySelector('.tour-next');

    let currentStepIndex = 0;
    let currentTarget = null;

    const clearActiveTarget = function () {
        if (currentTarget) {
            currentTarget.classList.remove('tour-active-target');
            currentTarget.classList.remove('tour-active-target-layered');
            currentTarget = null;
        }
    };

    const closeTour = function () {
        clearActiveTarget();
        overlay.remove();
        panel.remove();
        window.removeEventListener('resize', positionPanel);
        document.removeEventListener('keydown', handleKeyboard);
    };

    const clamp = function (value, min, max) {
        return Math.min(Math.max(value, min), max);
    };

    function positionPanel() {
        if (!currentTarget) {
            return;
        }

        const rect = currentTarget.getBoundingClientRect();
        const panelRect = panel.getBoundingClientRect();
        const viewportPadding = 12;
        const defaultOffset = 12;

        const canPlaceBelow = (window.innerHeight - rect.bottom) >= (panelRect.height + defaultOffset + viewportPadding);
        const top = canPlaceBelow
            ? rect.bottom + defaultOffset
            : rect.top - panelRect.height - defaultOffset;

        const centeredLeft = rect.left + (rect.width / 2) - (panelRect.width / 2);
        const left = clamp(centeredLeft, viewportPadding, window.innerWidth - panelRect.width - viewportPadding);

        panel.style.top = Math.max(viewportPadding, top) + 'px';
        panel.style.left = left + 'px';
    }

    function renderStep() {
        clearActiveTarget();

        const step = availableSteps[currentStepIndex];
        const stepTarget = document.querySelector('[data-tour-id="' + step.id + '"]');

        if (!stepTarget) {
            if (currentStepIndex < availableSteps.length - 1) {
                currentStepIndex += 1;
                renderStep();
            } else {
                closeTour();
            }
            return;
        }

        currentTarget = stepTarget;
        currentTarget.classList.add('tour-active-target');
        if (window.getComputedStyle(currentTarget).position === 'static') {
            currentTarget.classList.add('tour-active-target-layered');
            currentTarget.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
        }

        titleElement.textContent = step.title;
        descriptionElement.textContent = step.description;
        progressElement.textContent = 'Step ' + (currentStepIndex + 1) + ' of ' + availableSteps.length;
        backButton.disabled = currentStepIndex === 0;
        nextButton.textContent = currentStepIndex === (availableSteps.length - 1) ? 'Done' : 'Next';

        requestAnimationFrame(positionPanel);
    }

    function handleKeyboard(event) {
        if (event.key === 'Escape') {
            closeTour();
            return;
        }

        if (event.key === 'ArrowRight') {
            nextButton.click();
        }

        if (event.key === 'ArrowLeft') {
            backButton.click();
        }
    }

    backButton.addEventListener('click', function () {
        if (currentStepIndex > 0) {
            currentStepIndex -= 1;
            renderStep();
        }
    });

    nextButton.addEventListener('click', function () {
        if (currentStepIndex >= availableSteps.length - 1) {
            closeTour();
            return;
        }

        currentStepIndex += 1;
        renderStep();
    });

    skipButton.addEventListener('click', function () {
        closeTour();
    });

    overlay.addEventListener('click', function () {
        closeTour();
    });

    window.addEventListener('resize', positionPanel);
    document.addEventListener('keydown', handleKeyboard);
    document.body.appendChild(overlay);
    document.body.appendChild(panel);
    renderStep();
}

// Navigation helper
function goToDashboard(event) {
    event.preventDefault();
    window.location.href = 'dashboard.php';
}

// Logout handler
function handleLogout(event) {
    event.preventDefault();
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

// Add to cart placeholder
function addToCart(productId) {
    console.log('Added product to cart:', productId);
    alert('Product added to cart!');
}

// View product details placeholder
function viewProduct(productId) {
    console.log('Viewing product:', productId);
    // In production, navigate to product detail page
    window.location.href = 'material-detail.php?id=' + productId;
}

// Filter products
function filterProducts(event) {
    event.preventDefault();
    console.log('Applying filters...');
    // In production, submit filters to server
    const form = event.target.closest('form');
    if (form) {
        form.submit();
    }
}

// Price range handler
function updatePriceRange(value) {
    const display = document.querySelector('.price-display');
    if (display) {
        display.textContent = '₦0 - ₦' + parseInt(value).toLocaleString();
    }
}

// Smooth scroll to section
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// Utility: Format currency
function formatCurrency(amount) {
    return '₦' + parseFloat(amount).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Utility: Show notification
function showNotification(message, type = 'success', duration = DEFAULT_TOAST_DURATION_MS) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + type + ' toast-floating';
    notification.textContent = message;
    notification.setAttribute('data-toast-init', 'true');

    const container = getToastContainer();
    container.appendChild(notification);

    scheduleToastDismiss(notification, duration);

    return notification;
}

// Account type toggle on signup
function toggleAccountType(type) {
    document.querySelectorAll('input[name="account_type"]').forEach(radio => {
        if (radio.value === type) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    });
}

// Password validation
function validatePassword(password) {
    return password.length >= 8;
}

// Email validation
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}
