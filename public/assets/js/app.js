/**
 * Lumixa LMS - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Close alert buttons
    document.querySelectorAll('.alert-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.alert').remove();
        });
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert-success, .alert-info').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Confirm dangerous actions
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Form submission loading state
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                var originalText = btn.innerHTML;
                btn.innerHTML = 'Processing...';

                // Reset after timeout (in case of error)
                setTimeout(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 10000);
            }
        });
    });

    // Sidebar active state and auto-expand parent group
    var currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(function(link) {
        var href = link.getAttribute('href');
        if (href === currentPath || (href !== '/' && currentPath.startsWith(href))) {
            link.classList.add('active');
            // Auto-expand parent nav-group if link is active
            var parentGroup = link.closest('.nav-group');
            if (parentGroup) {
                parentGroup.classList.add('open');
            }
        }
    });

    // Collapsible sidebar navigation
    document.querySelectorAll('.nav-group-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            var navGroup = this.closest('.nav-group');
            var section = this.dataset.section;

            // Toggle current group
            navGroup.classList.toggle('open');

            // Save state to localStorage
            var openSections = JSON.parse(localStorage.getItem('navOpenSections') || '{}');
            openSections[section] = navGroup.classList.contains('open');
            localStorage.setItem('navOpenSections', JSON.stringify(openSections));
        });
    });

    // Restore nav state from localStorage
    var savedSections = JSON.parse(localStorage.getItem('navOpenSections') || '{}');
    Object.keys(savedSections).forEach(function(section) {
        if (savedSections[section]) {
            var toggle = document.querySelector('.nav-group-toggle[data-section="' + section + '"]');
            if (toggle) {
                toggle.closest('.nav-group').classList.add('open');
            }
        }
    });

    // Language dropdown toggle
    document.querySelectorAll('.language-dropdown-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var dropdown = this.closest('.language-dropdown');
            var isOpen = dropdown.classList.contains('open');

            // Close all other dropdowns
            document.querySelectorAll('.language-dropdown.open').forEach(function(d) {
                d.classList.remove('open');
            });

            // Toggle current dropdown
            if (!isOpen) {
                dropdown.classList.add('open');
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.language-dropdown')) {
            document.querySelectorAll('.language-dropdown.open').forEach(function(dropdown) {
                dropdown.classList.remove('open');
            });
        }
    });

    // Close dropdown on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.language-dropdown.open').forEach(function(dropdown) {
                dropdown.classList.remove('open');
            });
        }
    });

    // Debug panel toggle (if exists)
    var debugPanel = document.querySelector('.debug-panel');
    if (debugPanel) {
        var toggle = debugPanel.querySelector('.debug-toggle');
        var content = debugPanel.querySelector('.debug-content');

        toggle.addEventListener('click', function() {
            content.style.display = content.style.display === 'block' ? 'none' : 'block';
        });
    }

    // Table row click (for items with data-href)
    document.querySelectorAll('tr[data-href]').forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                window.location.href = this.dataset.href;
            }
        });
    });

    // Search input debounce
    var searchInputs = document.querySelectorAll('input[data-search]');
    searchInputs.forEach(function(input) {
        var timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            var form = this.closest('form');
            timeout = setTimeout(function() {
                if (form) form.submit();
            }, 500);
        });
    });
});

// CSRF token for AJAX requests
function getCsrfToken() {
    var input = document.querySelector('input[name="_csrf_token"]');
    return input ? input.value : '';
}

// Helper for AJAX POST
function postData(url, data) {
    data._csrf_token = getCsrfToken();

    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': getCsrfToken()
        },
        body: new URLSearchParams(data).toString()
    }).then(function(response) {
        return response.json();
    });
}

// Flash message helper
function showFlash(type, message) {
    var container = document.querySelector('.content-body');
    if (!container) return;

    var alert = document.createElement('div');
    alert.className = 'alert alert-' + type;
    alert.innerHTML = message + '<button type="button" class="alert-close">&times;</button>';

    container.insertBefore(alert, container.firstChild);

    alert.querySelector('.alert-close').addEventListener('click', function() {
        alert.remove();
    });

    if (type === 'success' || type === 'info') {
        setTimeout(function() {
            alert.remove();
        }, 5000);
    }
}
