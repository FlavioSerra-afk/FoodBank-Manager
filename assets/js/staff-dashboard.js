(function (window, document) {
    'use strict';

    const { __, _x, _n, sprintf } = wp.i18n;
    const requestFailedMessage = __('Request failed.', 'foodbank-manager');
    const invalidResponseMessage = __('Invalid response.', 'foodbank-manager');

    if (!window || !document) {
        return;
    }

    var settings = window.fbmStaffDashboard;
    if (!settings || !settings.strings || !settings.restUrl) {
        return;
    }

    var parseBool = function (value, fallback) {
        if (typeof value === 'boolean') {
            return value;
        }

        if (typeof value === 'number') {
            return value !== 0;
        }

        if (typeof value === 'string') {
            var normalized = value.toLowerCase().trim();
            if (normalized === '1' || normalized === 'true' || normalized === 'yes' || normalized === 'on') {
                return true;
            }
            if (normalized === '0' || normalized === 'false' || normalized === 'no' || normalized === 'off') {
                return false;
            }
        }

        return fallback;
    };

    var clampInt = function (value, fallback, min, max) {
        var parsed = parseInt(value, 10);

        if (isNaN(parsed)) {
            parsed = typeof fallback === 'number' ? fallback : 0;
        }

        if (typeof min === 'number' && parsed < min) {
            parsed = min;
        }

        if (typeof max === 'number' && parsed > max) {
            parsed = max;
        }

        return parsed;
    };

    var baseConfig = {
        showCounters: true,
        allowOverride: true,
        scanner: {
            preferTorch: false,
            roi: 80,
            decodeDebounce: 1200
        }
    };

    var localizedConfig = settings.config;
    if (localizedConfig && typeof localizedConfig === 'object') {
        if (Object.prototype.hasOwnProperty.call(localizedConfig, 'show_counters')) {
            baseConfig.showCounters = parseBool(localizedConfig.show_counters, baseConfig.showCounters);
        }
        if (Object.prototype.hasOwnProperty.call(localizedConfig, 'allow_override')) {
            baseConfig.allowOverride = parseBool(localizedConfig.allow_override, baseConfig.allowOverride);
        }
        if (localizedConfig.scanner && typeof localizedConfig.scanner === 'object') {
            var scanner = localizedConfig.scanner;
            if (Object.prototype.hasOwnProperty.call(scanner, 'prefer_torch')) {
                baseConfig.scanner.preferTorch = parseBool(scanner.prefer_torch, baseConfig.scanner.preferTorch);
            }
            if (Object.prototype.hasOwnProperty.call(scanner, 'roi')) {
                baseConfig.scanner.roi = clampInt(scanner.roi, baseConfig.scanner.roi, 30, 100);
            }
            if (Object.prototype.hasOwnProperty.call(scanner, 'decode_debounce')) {
                baseConfig.scanner.decodeDebounce = clampInt(scanner.decode_debounce, baseConfig.scanner.decodeDebounce, 0, 5000);
            }
        }
    }

    var cloneConfig = function () {
        return {
            showCounters: baseConfig.showCounters,
            allowOverride: baseConfig.allowOverride,
            scanner: {
                preferTorch: baseConfig.scanner.preferTorch,
                roi: baseConfig.scanner.roi,
                decodeDebounce: baseConfig.scanner.decodeDebounce
            }
        };
    };

    var resolveRoot = function (element) {
        if (!element) {
            return null;
        }

        if (typeof element.hasAttribute === 'function' && element.hasAttribute('data-fbm-staff-dashboard')) {
            return element;
        }

        if (element.dataset && element.dataset.fbmStaffDashboard) {
            return element;
        }

        if (typeof element.closest === 'function') {
            var candidate = element.closest('[data-fbm-staff-dashboard]');
            if (candidate) {
                return candidate;
            }
        }

        return null;
    };

    var applyDatasetConfig = function (config, root) {
        if (!root || !root.dataset) {
            return;
        }

        var data = root.dataset;

        if (Object.prototype.hasOwnProperty.call(data, 'fbmShowCounters')) {
            config.showCounters = parseBool(data.fbmShowCounters, config.showCounters);
        }

        if (Object.prototype.hasOwnProperty.call(data, 'fbmAllowOverride')) {
            config.allowOverride = parseBool(data.fbmAllowOverride, config.allowOverride);
        }

        if (Object.prototype.hasOwnProperty.call(data, 'fbmScannerTorch')) {
            config.scanner.preferTorch = parseBool(data.fbmScannerTorch, config.scanner.preferTorch);
        }

        if (Object.prototype.hasOwnProperty.call(data, 'fbmScannerRoi')) {
            config.scanner.roi = clampInt(data.fbmScannerRoi, config.scanner.roi, 30, 100);
        }

        if (Object.prototype.hasOwnProperty.call(data, 'fbmScannerDebounce')) {
            config.scanner.decodeDebounce = clampInt(data.fbmScannerDebounce, config.scanner.decodeDebounce, 0, 5000);
        }
    };

    var getContainerConfig = function (element) {
        var root = resolveRoot(element);

        if (!root) {
            return cloneConfig();
        }

        if (!root.__fbmConfig) {
            var merged = cloneConfig();
            applyDatasetConfig(merged, root);
            root.__fbmConfig = merged;
        }

        return root.__fbmConfig;
    };

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

        var config = getContainerConfig(container);
        if (!config.showCounters) {
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

        var config = getContainerConfig(container);
        if (!config.allowOverride) {
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
        if (message && strings && typeof strings.override_prompt === 'string' && context && context.reference) {
            var prompt = strings.override_prompt;
            if (prompt.indexOf('%s') !== -1) {
                prompt = sprintf(prompt, context.reference);
            }
            if (strings.override_requirements && typeof strings.override_requirements === 'string') {
                prompt = prompt + ' ' + strings.override_requirements;
            }
            message.textContent = prompt;
        }

        var note = container.querySelector('[data-fbm-override-note]');
        if (note && typeof note.focus === 'function') {
            note.focus();
        }
    };

    var prepareScanner = function (section, statusEl, strings, onScan) {
        if (!section) {
            return;
        }

        var root = resolveRoot(section) || section;
        var config = getContainerConfig(root);
        var scannerOptions = config.scanner || {};
        var roi = clampInt(scannerOptions.roi, baseConfig.scanner.roi, 30, 100);
        var decodeDelay = clampInt(scannerOptions.decodeDebounce, baseConfig.scanner.decodeDebounce, 0, 5000);
        var preferTorch = !!scannerOptions.preferTorch;

        var startButton = section.querySelector('[data-fbm-scanner-start]');
        var stopButton = section.querySelector('[data-fbm-scanner-stop]');
        var wrapper = section.querySelector('[data-fbm-scanner-wrapper]');
        var video = section.querySelector('[data-fbm-scanner-video]');
        var fallback = section.querySelector('[data-fbm-scanner-fallback]');
        var overlay = section.querySelector('[data-fbm-scanner-overlay]');
        var frame = section.querySelector('[data-fbm-scanner-frame]');

        if (overlay) {
            overlay.style.width = roi + '%';
            overlay.style.height = roi + '%';
        }

        if (frame && frame.style && typeof frame.style.setProperty === 'function') {
            frame.style.setProperty('--fbm-scanner-roi', roi + '%');
        }

        var supported = typeof window.BarcodeDetector === 'function';
        var detector = null;
        var scanning = false;
        var rafId = null;
        var mediaStream = null;
        var currentTrack = null;
        var lastResult = '';
        var lastDecodeAt = 0;

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
            currentTrack = null;
            lastDecodeAt = 0;

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
                startButton.disabled = false;
            }

            if (fallback) {
                fallback.hidden = true;
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

            var now = Date.now();
            if (decodeDelay > 0 && now - lastDecodeAt < decodeDelay) {
                rafId = requestFrame(scanFrame);
                return;
            }
            lastDecodeAt = now;

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
                currentTrack = stream.getVideoTracks && stream.getVideoTracks().length ? stream.getVideoTracks()[0] : null;

                if (video) {
                    video.srcObject = stream;
                    if (typeof video.play === 'function') {
                        video.play();
                    }
                }

                if (preferTorch && currentTrack && typeof currentTrack.getCapabilities === 'function' && typeof currentTrack.applyConstraints === 'function') {
                    try {
                        var capabilities = currentTrack.getCapabilities();
                        if (capabilities && Object.prototype.hasOwnProperty.call(capabilities, 'torch') && capabilities.torch) {
                            currentTrack.applyConstraints({ advanced: [{ torch: true }] }).catch(function () {
                                // Ignore torch preference failures.
                            });
                        }
                    } catch (torchError) {
                        // Ignore capability errors.
                    }
                }

                scanning = true;
                lastResult = '';
                lastDecodeAt = 0;

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
        var config = getContainerConfig(container);

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
            if (!response) {
                throw new Error(requestFailedMessage);
            }

            return response.json().catch(function () {
                return null;
            }).then(function (data) {
                if (!data || typeof data.status !== 'string') {
                    if (!response.ok) {
                        throw new Error(requestFailedMessage);
                    }

                    throw new Error(invalidResponseMessage);
                }

                data.__responseOk = !!response.ok;

                return data;
            });
        }).then(function (data) {
            if (!data || typeof data.status !== 'string') {
                throw new Error(invalidResponseMessage);
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
            } else if (statusKey === 'already') {
                message = strings.already || strings.duplicate_day;
                tone = 'warning';
                updateCounters(container, 'duplicate');
                hideOverride(container);
            } else if (statusKey === 'recent_warning') {
                message = strings.recent_warning;
                tone = 'warning';
                if (config.allowOverride && context && context.reference) {
                    showOverride(container, {
                        reference: context.reference,
                        mode: context.mode,
                        method: context.method
                    }, strings);
                } else {
                    message = strings.override_disabled || message;
                    hideOverride(container);
                }
            } else if (statusKey === 'throttled') {
                message = (data && typeof data.message === 'string') ? data.message : (strings.throttled || strings.error);
                tone = 'warning';
                hideOverride(container);
            } else if (statusKey === 'invalid') {
                if (data && typeof data.message === 'string' && data.message) {
                    message = data.message;
                } else if (strings.invalid) {
                    message = strings.invalid;
                } else {
                    message = resolveWindowNotice(strings);
                }
                tone = 'error';
                hideOverride(container);
            } else if (statusKey === 'revoked') {
                message = (data && typeof data.message === 'string') ? data.message : (strings.revoked || strings.error);
                tone = 'error';
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
            var config = getContainerConfig(container);
            updateCounters(container);

            var statusEl = container.querySelector('[data-fbm-status]');
            updateStatus(statusEl, settings.strings.ready, 'info');

            hideOverride(container);

            var scannerSection = container.querySelector('[data-fbm-scanner-module]');
            if (scannerSection) {
                prepareScanner(scannerSection, statusEl, settings.strings, function (token) {
                    if (!token) {
                        return;
                    }

                    sendCheckin(container, {
                        code: token,
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
                    if (!config.allowOverride) {
                        updateStatus(statusEl, settings.strings.override_disabled || settings.strings.error, 'error');
                        return;
                    }

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
                        payload.code = context.reference;
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
