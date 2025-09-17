(function (window, document) {
    'use strict';

    if (!window || !document) {
        return;
    }

    var settings = window.fbmStaffDashboard;
    if (!settings || !settings.strings || !settings.restUrl) {
        return;
    }

    var requestFrame = window.requestAnimationFrame || function (callback) {
        return window.setTimeout(callback, 1000 / 30);
    };
    var cancelFrame = window.cancelAnimationFrame || window.clearTimeout;
    var forEach = Array.prototype.forEach;
    var schedule = (settings.schedule && typeof settings.schedule === 'object') ? settings.schedule : {};
    if (!settings.schedule || typeof settings.schedule !== 'object') {
        settings.schedule = schedule;
    }
    var scheduleLabels = (schedule.labels && typeof schedule.labels === 'object') ? schedule.labels : {};
    if (!schedule.labels || typeof schedule.labels !== 'object') {
        schedule.labels = scheduleLabels;
    }

    var applyScheduleFromResponse = function (data, strings) {
        if (!strings || !settings) {
            return;
        }

        if (!schedule || typeof schedule !== 'object') {
            schedule = {};
            settings.schedule = schedule;
        }

        if (data && data.window && typeof data.window === 'object') {
            schedule.window = data.window;
        }

        if (data && data.window_labels && typeof data.window_labels === 'object') {
            scheduleLabels = data.window_labels;
            schedule.labels = scheduleLabels;
        }

        if (data && typeof data.window_notice === 'string') {
            strings.collection_window_notice = data.window_notice;
        } else if (scheduleLabels && typeof scheduleLabels.notice === 'string') {
            strings.collection_window_notice = scheduleLabels.notice;
        }
    };

    var resolveWindowNotice = function (strings) {
        if (scheduleLabels && typeof scheduleLabels.notice === 'string' && scheduleLabels.notice) {
            return scheduleLabels.notice;
        }
        if (strings.collection_window_notice && typeof strings.collection_window_notice === 'string') {
            return strings.collection_window_notice;
        }
        return strings.out_of_window;
    };

    var updateStatus = function (element, message, tone) {
        if (!element) {
            return;
        }

        element.textContent = typeof message === 'string' ? message : '';

        var previousTone = element.getAttribute('data-fbm-tone');
        if (previousTone) {
            element.classList.remove('fbm-staff-dashboard__status--' + previousTone);
        }

        if (tone) {
            element.setAttribute('data-fbm-tone', tone);
            element.classList.add('fbm-staff-dashboard__status--' + tone);
        } else {
            element.removeAttribute('data-fbm-tone');
        }
    };

    var updateCounters = function (container, key) {
        if (!container) {
            return;
        }

        if (!container.__fbmState) {
            container.__fbmState = {};
        }

        var state = container.__fbmState;
        if (!state.counters) {
            state.counters = {
                success: 0,
                duplicate: 0,
                override: 0
            };
        }

        if (key && Object.prototype.hasOwnProperty.call(state.counters, key)) {
            state.counters[key] += 1;
        }

        var successEl = container.querySelector('[data-fbm-today-success]');
        var duplicateEl = container.querySelector('[data-fbm-today-duplicate]');
        var overrideEl = container.querySelector('[data-fbm-today-override]');

        if (successEl) {
            successEl.textContent = String(state.counters.success);
        }
        if (duplicateEl) {
            duplicateEl.textContent = String(state.counters.duplicate);
        }
        if (overrideEl) {
            overrideEl.textContent = String(state.counters.override);
        }
    };

    var hideOverride = function (container) {
        if (!container) {
            return;
        }

        var override = container.querySelector('[data-fbm-override]');
        if (override) {
            override.hidden = true;
        }

        var note = container.querySelector('[data-fbm-override-note]');
        if (note && typeof note.value === 'string') {
            note.value = '';
        }

        if (container.__fbmState) {
            container.__fbmState.overrideContext = null;
        }
    };

    var showOverride = function (container, context, strings) {
        if (!container) {
            return;
        }

        if (!container.__fbmState) {
            container.__fbmState = {};
        }

        container.__fbmState.overrideContext = context || null;

        var override = container.querySelector('[data-fbm-override]');
        if (override) {
            override.hidden = false;
        }

        var message = container.querySelector('[data-fbm-override-message]');
        if (message && strings && strings.override_prompt && context && context.reference) {
            var prompt = strings.override_prompt;
            if (prompt.indexOf('%s') !== -1) {
                prompt = prompt.replace('%s', context.reference);
            }
            if (strings.override_requirements && typeof strings.override_requirements === 'string') {
                prompt += ' ' + strings.override_requirements;
            }
            message.textContent = prompt;
        }

        var note = container.querySelector('[data-fbm-override-note]');
        if (note && typeof note.focus === 'function') {
            note.focus();
        }
    };

    var prepareScanner = function (container, statusEl, strings, onScan) {
        var startButton = container.querySelector('[data-fbm-start-scan]');
        var stopButton = container.querySelector('[data-fbm-stop-scan]');
        var wrapper = container.querySelector('[data-fbm-camera-wrapper]');
        var video = container.querySelector('[data-fbm-camera]');
        var fallback = container.querySelector('[data-fbm-camera-fallback]');

        var supported = typeof window.BarcodeDetector === 'function';
        var detector = null;
        var scanning = false;
        var rafId = null;
        var mediaStream = null;
        var lastResult = '';

        if (supported) {
            try {
                detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            } catch (error) {
                supported = false;
                detector = null;
            }
        }

        var stopScanning = function () {
            scanning = false;

            if (rafId) {
                cancelFrame(rafId);
                rafId = null;
            }

            if (mediaStream && typeof mediaStream.getTracks === 'function') {
                mediaStream.getTracks().forEach(function (track) {
                    if (track && typeof track.stop === 'function') {
                        track.stop();
                    }
                });
            }

            mediaStream = null;

            if (video) {
                if (typeof video.pause === 'function') {
                    video.pause();
                }
                video.srcObject = null;
            }

            if (wrapper) {
                wrapper.hidden = true;
            }

            if (startButton) {
                startButton.hidden = false;
            }
        };

        var scanFrame = function () {
            if (!scanning || !detector || !video) {
                return;
            }

            if (video.readyState < 2) {
                rafId = requestFrame(scanFrame);
                return;
            }

            detector.detect(video).then(function (codes) {
                if (!codes || !codes.length) {
                    rafId = requestFrame(scanFrame);
                    return;
                }

                var code = codes[0];
                var value = '';

                if (code && typeof code.rawValue === 'string') {
                    value = code.rawValue.trim();
                }

                if (value && value !== lastResult) {
                    lastResult = value;
                    stopScanning();
                    if (typeof onScan === 'function') {
                        onScan(value);
                    }
                } else {
                    rafId = requestFrame(scanFrame);
                }
            }).catch(function () {
                rafId = requestFrame(scanFrame);
            });
        };

        var startScanning = function () {
            if (!supported) {
                if (fallback) {
                    fallback.hidden = false;
                    fallback.textContent = strings.scanner_unsupported;
                }
                updateStatus(statusEl, strings.scanner_unsupported, 'warning');
                return;
            }

            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                if (fallback) {
                    fallback.hidden = false;
                    fallback.textContent = strings.scanner_error;
                }
                updateStatus(statusEl, strings.scanner_error, 'error');
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (stream) {
                mediaStream = stream;

                if (video) {
                    video.srcObject = stream;
                    if (typeof video.play === 'function') {
                        video.play();
                    }
                }

                scanning = true;
                lastResult = '';

                if (wrapper) {
                    wrapper.hidden = false;
                }

                if (startButton) {
                    startButton.hidden = true;
                }

                if (fallback) {
                    fallback.hidden = true;
                }

                updateStatus(statusEl, strings.scanner_active, 'info');
                rafId = requestFrame(scanFrame);
            }).catch(function () {
                updateStatus(statusEl, strings.scanner_error, 'error');
                if (fallback) {
                    fallback.hidden = false;
                    fallback.textContent = strings.scanner_error;
                }
            });
        };

        if (!supported) {
            if (startButton) {
                startButton.disabled = true;
            }
            if (fallback) {
                fallback.hidden = false;
                fallback.textContent = strings.scanner_unsupported;
            }
            updateStatus(statusEl, strings.scanner_unsupported, 'warning');
            return {
                stop: stopScanning
            };
        }

        if (startButton) {
            startButton.addEventListener('click', function () {
                updateStatus(statusEl, strings.scanner_ready, 'info');
                startScanning();
            });
        }

        if (stopButton) {
            stopButton.addEventListener('click', function () {
                stopScanning();
                updateStatus(statusEl, strings.ready, 'info');
            });
        }

        return {
            stop: stopScanning
        };
    };

    var sendCheckin = function (container, payload, context) {
        if (!container || !payload) {
            return;
        }

        var strings = settings.strings;
        var statusEl = container.querySelector('[data-fbm-status]');

        updateStatus(statusEl, strings.loading, 'info');

        window.fetch(settings.restUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': settings.nonce
            },
            body: JSON.stringify(payload)
        }).then(function (response) {
            if (!response || !response.ok) {
                throw new Error('request failed');
            }
            return response.json();
        }).then(function (data) {
            if (!data || typeof data.status !== 'string') {
                throw new Error('invalid');
            }

            var statusKey = data.status;
            var message = '';
            var tone = 'info';

            applyScheduleFromResponse(data, strings);

            if (statusKey === 'success') {
                if (context && context.override === true) {
                    message = strings.override_success || strings.success;
                    tone = 'success';
                    updateCounters(container, 'override');
                } else {
                    message = strings.success;
                    tone = 'success';
                    updateCounters(container, 'success');
                }
                hideOverride(container);
            } else if (statusKey === 'duplicate_day') {
                message = strings.duplicate_day;
                tone = 'warning';
                updateCounters(container, 'duplicate');
                hideOverride(container);
            } else if (statusKey === 'recent_warning') {
                message = strings.recent_warning;
                tone = 'warning';
                if (context && context.reference) {
                    showOverride(container, {
                        reference: context.reference,
                        mode: context.mode,
                        method: context.method
                    }, strings);
                }
            } else if (statusKey === 'out_of_window') {
                message = resolveWindowNotice(strings);
                tone = 'warning';
                hideOverride(container);
            } else if (Object.prototype.hasOwnProperty.call(strings, statusKey) && typeof strings[statusKey] === 'string') {
                message = strings[statusKey];
                tone = statusKey === 'error' ? 'error' : 'info';
            } else if (data && typeof data.message === 'string') {
                message = data.message;
                tone = 'info';
            } else {
                message = strings.error;
                tone = 'error';
            }

            updateStatus(statusEl, message, tone);

            if (context && context.input && statusKey === 'success') {
                if (typeof context.input.value === 'string') {
                    context.input.value = '';
                }
                if (typeof context.input.focus === 'function') {
                    context.input.focus();
                }
            }
        }).catch(function () {
            updateStatus(statusEl, strings.error, 'error');
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        var containers = document.querySelectorAll('[data-fbm-staff-dashboard]');
        if (!containers || !containers.length) {
            return;
        }

        forEach.call(containers, function (container) {
            updateCounters(container);

            var statusEl = container.querySelector('[data-fbm-status]');
            updateStatus(statusEl, settings.strings.ready, 'info');

            hideOverride(container);

            var scannerSection = container.querySelector('[data-fbm-scanner]');
            if (scannerSection) {
                prepareScanner(scannerSection, statusEl, settings.strings, function (token) {
                    if (!token) {
                        return;
                    }

                    sendCheckin(container, {
                        token: token,
                        method: 'qr'
                    }, {
                        reference: token,
                        mode: 'token',
                        method: 'qr'
                    });
                });
            }

            var manualForm = container.querySelector('[data-fbm-manual]');
            if (manualForm) {
                manualForm.addEventListener('submit', function (event) {
                    if (event && typeof event.preventDefault === 'function') {
                        event.preventDefault();
                    }

                    var input = manualForm.querySelector('[data-fbm-reference]');
                    var reference = '';

                    if (input && typeof input.value === 'string') {
                        reference = input.value.trim();
                    }

                    if (!reference) {
                        updateStatus(statusEl, settings.strings.reference_required, 'error');
                        if (input && typeof input.focus === 'function') {
                            input.focus();
                        }
                        return;
                    }

                    sendCheckin(container, {
                        manual_code: reference,
                        method: 'manual'
                    }, {
                        input: input,
                        reference: reference,
                        mode: 'manual',
                        method: 'manual'
                    });
                });
            }

            var overrideConfirm = container.querySelector('[data-fbm-confirm-override]');
            if (overrideConfirm) {
                overrideConfirm.addEventListener('click', function () {
                    var state = container.__fbmState || {};
                    var context = state.overrideContext;
                    if (!context || !context.reference) {
                        updateStatus(statusEl, settings.strings.error, 'error');
                        return;
                    }

                    var note = container.querySelector('[data-fbm-override-note]');
                    var noteValue = '';
                    if (note && typeof note.value === 'string') {
                        noteValue = note.value.trim();
                    }

                    if (!noteValue) {
                        updateStatus(statusEl, settings.strings.override_note_required, 'error');
                        if (note && typeof note.focus === 'function') {
                            note.focus();
                        }
                        return;
                    }

                    var payload = {
                        method: context.method || 'manual',
                        override: true,
                        override_note: noteValue
                    };

                    if (context.mode === 'token') {
                        payload.token = context.reference;
                    } else {
                        payload.manual_code = context.reference;
                    }

                    sendCheckin(container, payload, {
                        override: true,
                        reference: context.reference,
                        mode: context.mode,
                        method: context.method
                    });
                });
            }

            var overrideCancel = container.querySelector('[data-fbm-cancel-override]');
            if (overrideCancel) {
                overrideCancel.addEventListener('click', function () {
                    hideOverride(container);
                    updateStatus(statusEl, settings.strings.ready, 'info');
                });
            }
        });
    });
})(window, document);
