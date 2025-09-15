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

        containers.forEach(function (container) {
            var status = container.querySelector('[data-fbm-status]');
            updateStatus(status, settings.strings.ready);

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
                    updateStatus(status, settings.strings.error);
                    return;
                }

                updateStatus(status, settings.strings.loading);

                var payload = {
                    reference: reference,
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

                        if (data.status === 'duplicate') {
                            updateStatus(status, settings.strings.duplicate);
                            return;
                        }

                        if (data.status === 'success') {
                            updateStatus(status, settings.strings.success);
                            if (referenceInput) {
                                referenceInput.value = '';
                            }
                            return;
                        }

                        updateStatus(status, settings.strings.error);
                    })
                    .catch(function () {
                        updateStatus(status, settings.strings.error);
                    });
            });
        });
    });
})(window, document);
