(function (window, document) {
    'use strict';

    if (!window || !document) {
        return;
    }

    var settings = window.fbmStaffDashboard;
    if (!settings || !settings.strings) {
        return;
    }

    var updateStatus = function (element, message) {
        if (element) {
            element.textContent = message;
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var containers = document.querySelectorAll('[data-fbm-staff-dashboard]');
        if (!containers.length) {
            return;
        }

        var strings = settings.strings;

        containers.forEach(function (container) {
            var status = container.querySelector('[data-fbm-status]');
            updateStatus(status, strings.ready);

            var referenceInput = container.querySelector('[data-fbm-reference]');
            var action = container.querySelector('[data-fbm-checkin]');

            if (!action) {
                return;
            }

            action.addEventListener('click', function () {
                if (!status) {
                    return;
                }

                var reference = '';
                if (referenceInput && typeof referenceInput.value === 'string') {
                    reference = referenceInput.value.trim();
                }

                if (!reference) {
                    updateStatus(status, strings.error);
                    return;
                }

                updateStatus(status, strings.loading);

                var payload = {
                    manual_code: reference,
                    method: action.dataset.fbmCheckin || 'manual'
                };

                window.fetch(settings.restUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': settings.nonce
                    },
                    body: JSON.stringify(payload)
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('request failed');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data || typeof data.status !== 'string') {
                            throw new Error('invalid');
                        }

                        var statusKey = data.status;
                        var fallback = strings.error || '';
                        var nextMessage = '';

                        if (Object.prototype.hasOwnProperty.call(strings, statusKey) && typeof strings[statusKey] === 'string' && strings[statusKey]) {
                            nextMessage = strings[statusKey];
                        } else if (typeof data.message === 'string' && data.message) {
                            nextMessage = data.message;
                        }

                        if (!nextMessage) {
                            nextMessage = fallback;
                        }

                        updateStatus(status, nextMessage);

                        if (statusKey === 'success' && referenceInput) {
                            referenceInput.value = '';
                        }
                    })
                    .catch(function () {
                        updateStatus(status, strings.error);
                    });
            });
        });
    });
})(window, document);
