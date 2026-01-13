/**
 * LMS - Lumixa Manufacturing System
 * Main JavaScript
 */

(function() {
    'use strict';

    // ========================================
    // Utility Functions
    // ========================================

    /**
     * Get CSRF token from meta tag
     */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Make AJAX request
     */
    async function ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        };

        const config = { ...defaults, ...options };

        if (config.body && !(config.body instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(config.body);
        }

        const response = await fetch(url, config);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    }

    // ========================================
    // DOM Ready
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initDropdowns();
        initAlerts();
        initConfirmations();
        initForms();
    });

    // ========================================
    // Sidebar Toggle
    // ========================================

    function initSidebar() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');

        if (toggle && sidebar) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                }
            });
        }
    }

    // ========================================
    // Dropdowns
    // ========================================

    function initDropdowns() {
        const dropdownToggle = document.getElementById('userDropdownToggle');
        const dropdownMenu = document.getElementById('userDropdownMenu');

        if (dropdownToggle && dropdownMenu) {
            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
        }
    }

    // ========================================
    // Auto-dismiss Alerts
    // ========================================

    function initAlerts() {
        const alerts = document.querySelectorAll('.alert');

        alerts.forEach(function(alert) {
            // Auto dismiss after 5 seconds for success messages
            if (alert.classList.contains('alert-success')) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            }
        });
    }

    // ========================================
    // Confirmation Dialogs
    // ========================================

    function initConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure?';

                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    // ========================================
    // Form Enhancements
    // ========================================

    function initForms() {
        // Disable submit button on form submit to prevent double-submit
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                }
            });
        });
    }

    // ========================================
    // Expose Global Functions
    // ========================================

    window.LMS = {
        ajax: ajax,
        getCsrfToken: getCsrfToken
    };

})();
