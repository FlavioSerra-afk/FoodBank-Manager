(function (window, document) {
    'use strict';

    const { __, _x, _n, sprintf } = wp.i18n;
    const requestFailedMessage = __('Request failed.', 'foodbank-manager');
    const invalidResponseMessage = __('Invalid response.', 'foodbank-manager');

    if (!window || !document) {
        return;
    }

    var settings = window.fbmStaffDashboard;
    if (!settings || !settings.restUrl || !settings.strings) {
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

    var ZXing = window.ZXingBrowser || window.ZXing || null;
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

    var sendCheckin = function (container, payload, context) {
        if (!container || !payload) {
            return Promise.resolve(null);
        }

        var strings = settings.strings;
        var statusEl = container.querySelector('[data-fbm-status]');
        var config = getContainerConfig(container);

        updateStatus(statusEl, strings.loading, 'info');

        return window.fetch(settings.restUrl, {
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

            return data;
        }).catch(function (error) {
            updateStatus(statusEl, settings.strings.error, 'error');
            throw error;
        });
    };

    var createReader = function () {
        if (!ZXing || (typeof ZXing.BrowserMultiFormatReader !== 'function' && typeof ZXing.BrowserQRCodeReader !== 'function')) {
            return null;
        }

        var hints = null;
        if (typeof window.Map === 'function' && ZXing.DecodeHintType && ZXing.BarcodeFormat) {
            hints = new window.Map();
            hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [ZXing.BarcodeFormat.QR_CODE]);
            hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
        }

        try {
            if (typeof ZXing.BrowserMultiFormatReader === 'function') {
                return new ZXing.BrowserMultiFormatReader(hints || undefined);
            }
            if (typeof ZXing.BrowserQRCodeReader === 'function') {
                return new ZXing.BrowserQRCodeReader(undefined, hints || undefined);
            }
        } catch (error) {
            return null;
        }

        return null;
    };

    var initScanner = function (container, section) {
        if (!container || !section) {
            return;
        }

        if (section.__fbmScannerInit) {
            return;
        }
        section.__fbmScannerInit = true;

        var strings = settings.strings;
        var statusEl = container.querySelector('[data-fbm-status]');
        var startButton = section.querySelector('[data-fbm-scanner-start]');
        var stopButton = section.querySelector('[data-fbm-scanner-stop]');
        var wrapper = section.querySelector('[data-fbm-scanner-wrapper]');
        var video = section.querySelector('[data-fbm-scanner-video]');
        var fallbackEl = section.querySelector('[data-fbm-scanner-fallback]');
        var feedbackEl = section.querySelector('[data-fbm-scanner-feedback]');
        var controls = section.querySelector('[data-fbm-scanner-controls]');
        var selectWrapper = section.querySelector('[data-fbm-scanner-select-wrapper]');
        var select = section.querySelector('[data-fbm-scanner-select]');
        var torchButton = section.querySelector('[data-fbm-scanner-torch]');
        var frameEl = section.querySelector('[data-fbm-scanner-frame]');
        var overlayEl = section.querySelector('[data-fbm-scanner-overlay]');

        var config = getContainerConfig(container);
        var scannerOptions = config.scanner || {};
        var roi = clampInt(scannerOptions.roi, baseConfig.scanner.roi, 30, 100);
        var decodeDelay = clampInt(scannerOptions.decodeDebounce, baseConfig.scanner.decodeDebounce, 0, 5000);
        var preferTorch = !!scannerOptions.preferTorch;

        if (overlayEl) {
            overlayEl.style.width = roi + '%';
            overlayEl.style.height = roi + '%';
        }

        if (frameEl && frameEl.style && typeof frameEl.style.setProperty === 'function') {
            frameEl.style.setProperty('--fbm-scanner-roi', roi + '%');
        }

        var reader = createReader();
        var canvas = document.createElement('canvas');
        var context = canvas.getContext && canvas.getContext('2d', { willReadFrequently: true });
        var devicePixelRatio = window.devicePixelRatio || 1;
        var canvasSize = Math.round(Math.max(480, Math.min(640, 480 * devicePixelRatio)));
        canvas.width = canvasSize;
        canvas.height = canvasSize;

        var frameId = null;
        var decodePending = false;
        var active = false;
        var lastResult = '';
        var tipShown = false;
        var currentStream = null;
        var currentTrack = null;
        var currentDeviceId = '';
        var startPromise = null;
        var torchSupported = false;
        var torchEnabled = false;
        var lastDecodeAt = 0;

        if (video) {
            video.setAttribute('playsinline', 'true');
            video.setAttribute('muted', 'true');
        }

        var setFeedback = function (message) {
            if (feedbackEl) {
                feedbackEl.textContent = typeof message === 'string' ? message : '';
            }
        };

        var updateControlsVisibility = function () {
            var showSelect = !!(selectWrapper && !selectWrapper.hidden);
            var showTorch = !!(torchButton && !torchButton.hidden);
            if (controls) {
                controls.hidden = !showSelect && !showTorch;
            }
        };

        var updateTorchButton = function () {
            if (!torchButton) {
                return;
            }

            if (!torchSupported) {
                torchButton.hidden = true;
                torchButton.disabled = true;
                torchButton.setAttribute('aria-pressed', 'false');
                torchButton.textContent = strings.scanner_torch_on || '';
                updateControlsVisibility();
                return;
            }

            torchButton.hidden = false;
            torchButton.disabled = false;
            torchButton.setAttribute('aria-pressed', torchEnabled ? 'true' : 'false');
            torchButton.textContent = torchEnabled ? (strings.scanner_torch_off || '') : (strings.scanner_torch_on || '');
            updateControlsVisibility();
        };

        var stopTracks = function () {
            if (!currentStream || typeof currentStream.getTracks !== 'function') {
                return;
            }

            var tracks = currentStream.getTracks();
            if (!tracks || !tracks.length) {
                return;
            }

            forEach.call(tracks, function (track) {
                if (track && typeof track.stop === 'function') {
                    track.stop();
                }
            });
        };

        var stopScanning = function (options) {
            options = options || {};

            active = false;

            if (frameId) {
                cancelFrame(frameId);
                frameId = null;
            }

            decodePending = false;
            tipShown = false;
            lastDecodeAt = 0;

            if (reader && typeof reader.reset === 'function') {
                try {
                    reader.reset();
                } catch (error) {
                    // Ignore reset failures.
                }
            }

            stopTracks();
            currentStream = null;
            currentTrack = null;
            torchSupported = false;
            torchEnabled = false;

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

            if (torchButton) {
                torchButton.hidden = true;
                torchButton.disabled = true;
                torchButton.setAttribute('aria-pressed', 'false');
                torchButton.textContent = strings.scanner_torch_on || '';
            }

            updateControlsVisibility();

            if (!options.keepFeedback) {
                setFeedback('');
            }

            if (fallbackEl && !options.keepFallback) {
                fallbackEl.hidden = true;
            }

            if (options.showFallback && fallbackEl) {
                fallbackEl.hidden = false;
                fallbackEl.textContent = options.showFallback;
            }

            if (options.updateStatus === false) {
                return;
            }

            updateStatus(statusEl, strings.ready, 'info');
        };

        var refreshDevices = function () {
            if (!select || !navigator.mediaDevices || typeof navigator.mediaDevices.enumerateDevices !== 'function') {
                if (selectWrapper) {
                    selectWrapper.hidden = true;
                }
                updateControlsVisibility();
                return Promise.resolve();
            }

            return navigator.mediaDevices.enumerateDevices().then(function (devices) {
                var videoDevices = [];

                if (devices && devices.length) {
                    forEach.call(devices, function (device) {
                        if (device && device.kind === 'videoinput') {
                            videoDevices.push(device);
                        }
                    });
                }

                while (select.firstChild) {
                    select.removeChild(select.firstChild);
                }

                var defaultLabel = strings.scanner_camera_default || '';
                var defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = defaultLabel;
                select.appendChild(defaultOption);

                forEach.call(videoDevices, function (device, index) {
                    var option = document.createElement('option');
                    option.value = device.deviceId || '';
                    var label = device.label || '';
                    if (!label) {
                        label = defaultLabel;
                        if (videoDevices.length > 1) {
                            label += ' ' + (index + 1);
                        }
                    }
                    option.textContent = label;
                    if (currentDeviceId && device.deviceId === currentDeviceId) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                if (!currentDeviceId && videoDevices.length && videoDevices[0].deviceId) {
                    currentDeviceId = videoDevices[0].deviceId;
                }

                if (!select.value && currentDeviceId) {
                    select.value = currentDeviceId;
                }

                if (selectWrapper) {
                    selectWrapper.hidden = videoDevices.length < 2;
                }

                updateControlsVisibility();
            }).catch(function () {
                if (selectWrapper) {
                    selectWrapper.hidden = true;
                }
                updateControlsVisibility();
            });
        };

        var handleStartError = function (error) {
            var message = strings.scanner_error;

            if (error && (error.name === 'NotAllowedError' || error.name === 'SecurityError')) {
                message = strings.scanner_permission || strings.scanner_error;
            } else if (error && (error.name === 'NotFoundError' || error.name === 'OverconstrainedError')) {
                message = strings.scanner_unsupported || strings.scanner_error;
            }

            stopScanning({ updateStatus: false, keepFeedback: false, keepFallback: true, showFallback: message });
            setFeedback(message);
            updateStatus(statusEl, message, 'error');
            if (startButton) {
                startButton.disabled = false;
            }
        };

        var scheduleDecoding = function () {
            if (!reader || !context) {
                return;
            }

            var step = function () {
                if (!active) {
                    frameId = null;
                    return;
                }

                frameId = requestFrame(step);

                if (!video || video.readyState < 2) {
                    return;
                }

                if (decodePending) {
                    return;
                }

                var now = Date.now();
                if (decodeDelay > 0 && now - lastDecodeAt < decodeDelay) {
                    return;
                }

                var videoWidth = video.videoWidth || 0;
                var videoHeight = video.videoHeight || 0;

                if (!videoWidth || !videoHeight) {
                    return;
                }

                decodePending = true;
                lastDecodeAt = now;

                try {
                    var size = Math.min(videoWidth, videoHeight);
                    if (!size) {
                        decodePending = false;
                        return;
                    }
                    var roiRatio = roi / 100;
                    size = Math.max(40, Math.floor(size * roiRatio));
                    var offsetX = Math.max(0, Math.floor((videoWidth - size) / 2));
                    var offsetY = Math.max(0, Math.floor((videoHeight - size) / 2));
                    context.drawImage(video, offsetX, offsetY, size, size, 0, 0, canvas.width, canvas.height);
                } catch (drawError) {
                    decodePending = false;
                    return;
                }

                reader.decodeFromCanvas(canvas).then(function (result) {
                    decodePending = false;

                    if (!result || !active) {
                        return;
                    }

                    var value = '';
                    if (typeof result.getText === 'function') {
                        value = result.getText();
                    } else if (typeof result.text === 'string') {
                        value = result.text;
                    }

                    if (!value || value === lastResult) {
                        return;
                    }

                    lastResult = value;

                    stopScanning({ updateStatus: false, keepFeedback: true, keepFallback: true });
                    setFeedback('');
                    updateStatus(statusEl, strings.loading, 'info');

                    sendCheckin(container, {
                        code: value,
                        method: 'qr'
                    }, {
                        reference: value,
                        mode: 'token',
                        method: 'qr'
                    }).catch(function () {
                        // Errors are reported via updateStatus inside sendCheckin.
                    });
                }).catch(function (error) {
                    decodePending = false;

                    if (!active) {
                        return;
                    }

                    var name = error && error.name ? error.name : '';
                    if (ZXing && ZXing.NotFoundException && error instanceof ZXing.NotFoundException) {
                        return;
                    }
                    if (name === 'NotFoundException') {
                        return;
                    }
                    if (name === 'ChecksumException' || name === 'FormatException' || (ZXing && ZXing.ChecksumException && error instanceof ZXing.ChecksumException) || (ZXing && ZXing.FormatException && error instanceof ZXing.FormatException)) {
                        if (!tipShown && strings.scanner_hold_steady) {
                            setFeedback(strings.scanner_hold_steady);
                            tipShown = true;
                        }
                        return;
                    }
                });
            };

            frameId = requestFrame(step);
        };

        var startStream = function (deviceId) {
            var constraints = {
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            if (deviceId) {
                constraints.video.deviceId = { exact: deviceId };
                delete constraints.video.facingMode;
            }

            if (fallbackEl) {
                fallbackEl.hidden = true;
            }

            return navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
                currentStream = stream;
                var tracks = stream.getVideoTracks ? stream.getVideoTracks() : null;
                currentTrack = (tracks && tracks.length) ? tracks[0] : null;

                if (video) {
                    video.srcObject = stream;
                    if (typeof video.play === 'function') {
                        video.play().catch(function () {
                            // Autoplay issues are ignored.
                        });
                    }
                }

                if (currentTrack) {
                    var trackSettings = currentTrack.getSettings ? currentTrack.getSettings() : {};
                    if (trackSettings && typeof trackSettings.deviceId === 'string') {
                        currentDeviceId = trackSettings.deviceId;
                    }

                    var capabilities = null;
                    if (typeof currentTrack.getCapabilities === 'function') {
                        try {
                            capabilities = currentTrack.getCapabilities();
                        } catch (error) {
                            capabilities = null;
                        }
                    }

                    if (capabilities && Array.isArray(capabilities.focusMode) && capabilities.focusMode.indexOf('continuous') !== -1) {
                        try {
                            currentTrack.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
                        } catch (error) {
                            // Ignore focus constraint failures.
                        }
                    }

                    torchSupported = !!(capabilities && Object.prototype.hasOwnProperty.call(capabilities, 'torch') && capabilities.torch);
                    torchEnabled = false;
                    if (torchSupported && preferTorch && typeof currentTrack.applyConstraints === 'function') {
                        currentTrack.applyConstraints({ advanced: [{ torch: true }] }).then(function () {
                            torchEnabled = true;
                            updateTorchButton();
                        }).catch(function () {
                            torchSupported = false;
                            torchEnabled = false;
                            updateTorchButton();
                        });
                    }
                } else {
                    torchSupported = false;
                    torchEnabled = false;
                }

                updateTorchButton();

                if (wrapper) {
                    wrapper.hidden = false;
                }

                if (startButton) {
                    startButton.hidden = true;
                    startButton.disabled = false;
                }

                setFeedback(strings.scanner_active);
                updateStatus(statusEl, strings.scanner_active, 'info');

                active = true;
                lastResult = '';
                decodePending = false;
                tipShown = false;
                lastDecodeAt = 0;

                scheduleDecoding();

                return refreshDevices();
            });
        };

        var startCamera = function (deviceId) {
            if (!reader || !context) {
                var unsupported = strings.scanner_unsupported || strings.scanner_error;
                if (startButton) {
                    startButton.disabled = true;
                }
                stopScanning({ updateStatus: false, showFallback: unsupported });
                setFeedback(unsupported);
                updateStatus(statusEl, unsupported, 'warning');
                return;
            }

            if (startPromise) {
                return;
            }

            currentDeviceId = typeof deviceId === 'string' ? deviceId : currentDeviceId;
            if (!currentDeviceId) {
                currentDeviceId = '';
            }

            stopScanning({ updateStatus: false, keepFeedback: true, keepFallback: true });
            setFeedback(strings.scanner_ready);
            updateStatus(statusEl, strings.scanner_ready, 'info');

            if (startButton) {
                startButton.disabled = true;
            }

            startPromise = startStream(currentDeviceId).then(function () {
                startPromise = null;
            }).catch(function (error) {
                startPromise = null;
                handleStartError(error);
            });
        };

        var disableMessage = strings.scanner_unsupported || strings.scanner_error;
        if (!reader || !context || !navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            if (startButton) {
                startButton.disabled = true;
            }
            if (fallbackEl) {
                fallbackEl.hidden = false;
                fallbackEl.textContent = disableMessage;
            }
            setFeedback(disableMessage);
            updateStatus(statusEl, disableMessage, 'warning');
            updateControlsVisibility();
            return;
        }

        updateControlsVisibility();
        refreshDevices();

        if (startButton) {
            startButton.addEventListener('click', function () {
                var selected = '';
                if (select && select.value) {
                    selected = select.value;
                }
                startCamera(selected);
            });
        }

        if (stopButton) {
            stopButton.addEventListener('click', function () {
                stopScanning();
            });
        }

        if (select) {
            select.addEventListener('change', function () {
                currentDeviceId = select.value || '';
                if (active) {
                    startCamera(currentDeviceId);
                }
            });
        }

        if (torchButton) {
            torchButton.addEventListener('click', function () {
                if (!currentTrack || typeof currentTrack.applyConstraints !== 'function' || !torchSupported) {
                    return;
                }

                var desired = !torchEnabled;
                currentTrack.applyConstraints({ advanced: [{ torch: desired }] }).then(function () {
                    torchEnabled = desired;
                    updateTorchButton();
                }).catch(function () {
                    torchSupported = false;
                    torchEnabled = false;
                    updateTorchButton();
                });
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var containers = document.querySelectorAll('[data-fbm-staff-dashboard]');
        if (!containers || !containers.length) {
            return;
        }

        forEach.call(containers, function (container) {
            var section = container.querySelector('[data-fbm-scanner-module]');
            if (section) {
                initScanner(container, section);
            }
        });
    });
})(window, document);
