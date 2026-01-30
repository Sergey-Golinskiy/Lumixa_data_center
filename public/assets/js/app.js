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

    // Mobile sidebar toggle
    var sidebarToggle = document.querySelector('.sidebar-toggle');
    var sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');

    function closeSidebar() {
        document.body.classList.remove('sidebar-open');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    document.querySelectorAll('.nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1200) {
                closeSidebar();
            }
        });
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

    // Image preview modal
    var imageModal = document.getElementById('image-preview-modal');
    var imageModalImg = document.getElementById('image-preview-img');
    if (imageModal && imageModalImg) {
        document.querySelectorAll('[data-image-preview]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var src = this.getAttribute('data-image-preview') || this.getAttribute('src');
                if (!src) return;
                imageModalImg.src = src;
                imageModal.classList.add('open');
                imageModal.setAttribute('aria-hidden', 'false');
            });
        });

        imageModal.querySelectorAll('[data-image-preview-close]').forEach(function(closeBtn) {
            closeBtn.addEventListener('click', function() {
                imageModal.classList.remove('open');
                imageModal.setAttribute('aria-hidden', 'true');
                imageModalImg.src = '';
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && imageModal.classList.contains('open')) {
                imageModal.classList.remove('open');
                imageModal.setAttribute('aria-hidden', 'true');
                imageModalImg.src = '';
            }
        });
    }
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

/**
 * Live Filters Component
 * Automatically submits filter forms with debounce
 */
(function() {
    'use strict';

    var DEBOUNCE_DELAY = 400;
    var DATE_DEBOUNCE_DELAY = 600;

    function initLiveFilters() {
        var filterForms = document.querySelectorAll('.live-filters');

        filterForms.forEach(function(filterContainer) {
            var form = filterContainer.querySelector('form') || filterContainer.closest('form');
            if (!form) return;

            var debounceTimer = null;
            var isSubmitting = false;

            // Get all filter inputs
            var inputs = filterContainer.querySelectorAll('.live-filter-input, .live-filter-select, .live-filter-checkbox input');

            // Initialize has-value classes
            updateHasValueClasses(filterContainer);

            // Handle input changes
            inputs.forEach(function(input) {
                var eventType = input.type === 'checkbox' ? 'change' : 'input';
                var delay = input.type === 'date' ? DATE_DEBOUNCE_DELAY : DEBOUNCE_DELAY;

                // For select and checkbox, use change event
                if (input.tagName === 'SELECT' || input.type === 'checkbox') {
                    eventType = 'change';
                    delay = 0; // Immediate for select/checkbox
                }

                input.addEventListener(eventType, function() {
                    clearTimeout(debounceTimer);
                    updateHasValueClasses(filterContainer);

                    if (delay === 0) {
                        submitFilters(form, filterContainer);
                    } else {
                        debounceTimer = setTimeout(function() {
                            submitFilters(form, filterContainer);
                        }, delay);
                    }
                });

                // Handle Enter key for text inputs
                if (input.type === 'text' || input.type === 'search') {
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            clearTimeout(debounceTimer);
                            submitFilters(form, filterContainer);
                        }
                    });
                }
            });

            // Handle clear search buttons
            filterContainer.querySelectorAll('.live-filter-clear-search').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var wrapper = this.closest('.live-filter-search-wrapper');
                    var input = wrapper.querySelector('.live-filter-input');
                    if (input) {
                        input.value = '';
                        input.focus();
                        updateHasValueClasses(filterContainer);
                        submitFilters(form, filterContainer);
                    }
                });
            });

            // Handle clear all button
            var clearAllBtn = filterContainer.querySelector('.live-filter-clear-all');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Clear all inputs
                    inputs.forEach(function(input) {
                        if (input.type === 'checkbox') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                    updateHasValueClasses(filterContainer);
                    // Navigate to base URL
                    window.location.href = form.action || window.location.pathname;
                });
            }
        });
    }

    function updateHasValueClasses(container) {
        // Update search wrappers
        container.querySelectorAll('.live-filter-search-wrapper').forEach(function(wrapper) {
            var input = wrapper.querySelector('.live-filter-input');
            if (input && input.value.trim()) {
                wrapper.classList.add('has-value');
            } else {
                wrapper.classList.remove('has-value');
            }
        });

        // Update filter groups
        container.querySelectorAll('.live-filter-group').forEach(function(group) {
            var input = group.querySelector('.live-filter-input, .live-filter-select');
            if (input && input.value && input.value.trim()) {
                group.classList.add('has-value');
            } else {
                group.classList.remove('has-value');
            }
        });

        // Update clear all button state
        var clearAllBtn = container.querySelector('.live-filter-clear-all');
        if (clearAllBtn) {
            var hasAnyValue = false;
            container.querySelectorAll('.live-filter-input, .live-filter-select').forEach(function(input) {
                if (input.value && input.value.trim()) {
                    hasAnyValue = true;
                }
            });
            container.querySelectorAll('.live-filter-checkbox input').forEach(function(input) {
                if (input.checked) {
                    hasAnyValue = true;
                }
            });
            clearAllBtn.disabled = !hasAnyValue;
        }
    }

    function submitFilters(form, container) {
        // Add loading state
        container.classList.add('loading');

        // Build URL with filter parameters
        var formData = new FormData(form);
        var params = new URLSearchParams();

        formData.forEach(function(value, key) {
            if (value && value.trim()) {
                params.append(key, value.trim());
            }
        });

        // Remove page parameter to reset pagination
        params.delete('page');

        // Navigate to filtered URL
        var url = form.action || window.location.pathname;
        var queryString = params.toString();
        if (queryString) {
            url += '?' + queryString;
        }

        window.location.href = url;
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLiveFilters);
    } else {
        initLiveFilters();
    }
})();
