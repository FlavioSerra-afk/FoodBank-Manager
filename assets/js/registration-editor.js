(function ( $ ) {
        'use strict';

        if ( typeof window.fbmRegistrationEditor === 'undefined' ) {
                return;
        }

        var settings = window.fbmRegistrationEditor;
        var textarea = document.getElementById( settings.textareaId || '' );
        var codeEditor = null;

        if ( textarea && window.wp && window.wp.codeEditor ) {
                var editorSettings = settings.codeEditor || {};
                editorSettings.codemirror = editorSettings.codemirror || {};
                if ( settings.editorTheme ) {
                        editorSettings.codemirror.theme = settings.editorTheme;
                }

                var editor = window.wp.codeEditor.initialize( textarea, editorSettings );
                if ( editor && editor.codemirror ) {
                        codeEditor = editor.codemirror;
                }
        }

        var getTemplate = function () {
                if ( codeEditor ) {
                        return codeEditor.getValue();
                }

                return textarea ? textarea.value : '';
        };

        var modal = document.querySelector( '[data-fbm-preview-modal]' );
        var dialog = modal ? modal.querySelector( '[data-fbm-preview-dialog]' ) : null;
        var content = modal ? modal.querySelector( '[data-fbm-preview-content]' ) : null;
        var warningsWrapper = modal ? modal.querySelector( '[data-fbm-preview-warnings]' ) : null;
        var warningsList = warningsWrapper ? warningsWrapper.querySelector( 'ul' ) : null;
        var note = modal ? modal.querySelector( '[data-fbm-preview-note]' ) : null;
        var lastFocused = null;
        var keydownHandler = null;
        var focusableSelector = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

        var resetWarnings = function () {
                if ( warningsList ) {
                        warningsList.innerHTML = '';
                }

                if ( warningsWrapper ) {
                        warningsWrapper.setAttribute( 'hidden', 'hidden' );
                }
        };

        var applyWarnings = function ( messages ) {
                resetWarnings();

                if ( ! warningsWrapper || ! warningsList || ! Array.isArray( messages ) ) {
                        return;
                }

                messages.forEach( function ( message ) {
                        if ( typeof message !== 'string' ) {
                                return;
                        }

                        var trimmed = message.trim();
                        if ( '' === trimmed ) {
                                return;
                        }

                        var item = document.createElement( 'li' );
                        item.textContent = trimmed;
                        warningsList.appendChild( item );
                } );

                if ( warningsList.childElementCount > 0 ) {
                        warningsWrapper.removeAttribute( 'hidden' );
                }
        };

        var disablePreviewControls = function () {
                if ( ! content ) {
                        return;
                }

                var controls = content.querySelectorAll( 'input, select, textarea, button' );
                Array.prototype.forEach.call( controls, function ( control ) {
                        control.setAttribute( 'disabled', 'disabled' );
                        control.setAttribute( 'aria-disabled', 'true' );
                        control.setAttribute( 'tabindex', '-1' );
                } );
        };

        var isModalOpen = function () {
                return modal && ! modal.hasAttribute( 'hidden' );
        };

        var closeModal = function () {
                if ( ! isModalOpen() ) {
                        return;
                }

                modal.setAttribute( 'hidden', 'hidden' );
                modal.removeAttribute( 'data-fbm-preview-nonce' );
                document.body.classList.remove( 'fbm-registration-editor--modal-open' );

                if ( keydownHandler ) {
                        document.removeEventListener( 'keydown', keydownHandler );
                        keydownHandler = null;
                }

                if ( content ) {
                        content.innerHTML = '';
                }

                resetWarnings();

                if ( lastFocused && typeof lastFocused.focus === 'function' ) {
                        lastFocused.focus();
                }

                lastFocused = null;
        };

        var focusDialog = function () {
                if ( ! dialog ) {
                        return;
                }

                var focusable = dialog.querySelectorAll( focusableSelector );
                if ( focusable.length > 0 ) {
                        focusable[0].focus();
                } else {
                        dialog.focus();
                }
        };

        var bindKeydown = function () {
                if ( keydownHandler ) {
                        return;
                }

                keydownHandler = function ( event ) {
                        if ( ! isModalOpen() ) {
                                return;
                        }

                        if ( 'Escape' === event.key ) {
                                event.preventDefault();
                                closeModal();

                                return;
                        }

                        if ( 'Tab' !== event.key || ! dialog ) {
                                return;
                        }

                        var focusable = Array.prototype.slice.call( dialog.querySelectorAll( focusableSelector ) );

                        if ( focusable.length === 0 ) {
                                event.preventDefault();
                                dialog.focus();

                                return;
                        }

                        var first = focusable[0];
                        var last = focusable[ focusable.length - 1 ];

                        if ( event.shiftKey ) {
                                if ( document.activeElement === first ) {
                                        event.preventDefault();
                                        last.focus();
                                }

                                return;
                        }

                        if ( document.activeElement === last ) {
                                event.preventDefault();
                                first.focus();
                        }
                };

                document.addEventListener( 'keydown', keydownHandler );
        };

        var openModal = function ( markup, warnings, nonce ) {
                if ( ! modal || ! dialog || ! content ) {
                        var previewWindow = window.open( '', 'fbmRegistrationPreview' );
                        if ( previewWindow ) {
                                previewWindow.document.open();
                                previewWindow.document.write( markup );
                                previewWindow.document.close();
                        }

                        return;
                }

                lastFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;

                content.innerHTML = markup;
                disablePreviewControls();
                applyWarnings( warnings );

                if ( note && settings.i18n && settings.i18n.modalDescription ) {
                        note.textContent = settings.i18n.modalDescription;
                }

                if ( nonce ) {
                        modal.setAttribute( 'data-fbm-preview-nonce', nonce );
                }

                if ( settings.i18n && settings.i18n.closeLabel ) {
                        var closeButtons = modal.querySelectorAll( '[data-fbm-preview-close]' );
                        Array.prototype.forEach.call( closeButtons, function ( element ) {
                                element.setAttribute( 'aria-label', settings.i18n.closeLabel );
                        } );
                }

                modal.removeAttribute( 'hidden' );
                document.body.classList.add( 'fbm-registration-editor--modal-open' );
                bindKeydown();
                focusDialog();
        };

        $( document ).on( 'click', '.fbm-registration-editor__snippet', function ( event ) {
                event.preventDefault();

                var snippet = $( this ).data( 'fbm-snippet' );
                if ( ! snippet ) {
                        return;
                }

                if ( codeEditor ) {
                        codeEditor.replaceSelection( snippet + '\n' );
                        codeEditor.focus();
                        return;
                }

                if ( textarea ) {
                        var start = textarea.selectionStart || 0;
                        var end = textarea.selectionEnd || 0;
                        var value = textarea.value || '';
                        textarea.value = value.slice( 0, start ) + snippet + '\n' + value.slice( end );
                }
        } );

        $( document ).on( 'click', '[data-fbm-preview-close]', function ( event ) {
                event.preventDefault();

                closeModal();
        } );

        $( document ).on( 'click', '.fbm-registration-editor__preview', function ( event ) {
                event.preventDefault();

                if ( ! settings.previewUrl || ! settings.previewNonce ) {
                        window.alert( settings.i18n && settings.i18n.previewError ? settings.i18n.previewError : 'Preview unavailable.' );
                        return;
                }

                window.fetch( settings.previewUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': settings.previewNonce,
                        },
                        body: JSON.stringify( {
                                template: getTemplate(),
                        } ),
                } ).then( function ( response ) {
                        if ( ! response.ok ) {
                                throw new Error( 'Request failed' );
                        }

                        return response.json();
                } ).then( function ( body ) {
                        if ( ! body || ! body.markup ) {
                                throw new Error( 'Invalid response' );
                        }

                        var warnings = Array.isArray( body.warnings ) ? body.warnings : [];
                        openModal( body.markup, warnings, body.nonce || '' );
                } ).catch( function () {
                        window.alert( settings.i18n && settings.i18n.previewError ? settings.i18n.previewError : 'Preview unavailable.' );
                } );
        } );

        var initializeConditions = function () {
                var root = document.querySelector( '[data-fbm-conditions]' );

                if ( ! root ) {
                        return;
                }

                var storage = root.querySelector( '[data-fbm-conditions-storage]' );
                var list = root.querySelector( '[data-fbm-conditions-list]' );
                var emptyState = root.querySelector( '[data-fbm-conditions-empty]' );
                var addButton = root.querySelector( '[data-fbm-conditions-add]' );
                var enabledToggle = root.querySelector( '[data-fbm-conditions-enabled]' );

                if ( ! storage || ! list || ! addButton || ! enabledToggle ) {
                        return;
                }

                var i18n = settings.i18n || {};
                var availableFields = Array.isArray( settings.fields ) ? settings.fields.filter( function ( field ) {
                        return field && field.name;
                } ) : [];

                var state = {
                        fields: availableFields,
                        rules: Array.isArray( settings.conditions && settings.conditions.rules ) ? settings.conditions.rules.slice( 0 ) : [],
                        enabled: !! ( settings.conditions && settings.conditions.enabled ),
                };

                var normalizeRule = function ( rule ) {
                        var defaults = {
                                if_field: state.fields.length > 0 ? state.fields[0].name : '',
                                operator: 'equals',
                                value: '',
                                action: 'show',
                                target: state.fields.length > 0 ? state.fields[0].name : '',
                        };

                        if ( ! rule || 'object' !== typeof rule ) {
                                return defaults;
                        }

                        return {
                                if_field: rule.if_field || defaults.if_field,
                                operator: rule.operator || defaults.operator,
                                value: rule.value || '',
                                action: rule.action || defaults.action,
                                target: rule.target || defaults.target,
                        };
                };

                state.rules = state.rules.map( normalizeRule ).filter( function ( rule ) {
                        if ( ! rule.if_field || ! rule.target ) {
                                return false;
                        }

                        var hasSource = state.fields.some( function ( field ) {
                                return field.name === rule.if_field;
                        } );

                        var hasTarget = state.fields.some( function ( field ) {
                                return field.name === rule.target;
                        } );

                        return hasSource && hasTarget;
                } );

                var operatorOptions = [
                        { value: 'equals', label: i18n.operatorEquals || 'is' },
                        { value: 'not_equals', label: i18n.operatorNotEquals || 'is not' },
                        { value: 'contains', label: i18n.operatorContains || 'contains' },
                        { value: 'empty', label: i18n.operatorEmpty || 'is empty' },
                        { value: 'not_empty', label: i18n.operatorNotEmpty || 'is not empty' },
                ];

                var actionOptions = [
                        { value: 'show', label: i18n.actionShow || 'show' },
                        { value: 'hide', label: i18n.actionHide || 'hide' },
                ];

                var createOption = function ( value, label, selectedValue ) {
                        var option = document.createElement( 'option' );
                        option.value = value;
                        option.textContent = label;
                        if ( value === selectedValue ) {
                                option.selected = true;
                        }

                        return option;
                };

                var toggleValueInput = function ( row, operator ) {
                        var valueWrap = row.querySelector( '[data-fbm-conditions-value-wrap]' );

                        if ( ! valueWrap ) {
                                return;
                        }

                        var shouldHide = 'empty' === operator || 'not_empty' === operator;
                        var input = valueWrap.querySelector( 'input' );

                        if ( shouldHide ) {
                                valueWrap.setAttribute( 'data-fbm-conditions-value-disabled', '1' );
                                valueWrap.style.display = 'none';
                                if ( input ) {
                                        input.value = '';
                                }
                        } else {
                                valueWrap.removeAttribute( 'data-fbm-conditions-value-disabled' );
                                valueWrap.style.display = '';
                        }
                };

                var renderRules = function () {
                        list.innerHTML = '';

                        if ( state.rules.length === 0 ) {
                                return;
                        }

                        state.rules.forEach( function ( rule, index ) {
                                var row = document.createElement( 'div' );
                                row.className = 'fbm-registration-editor__conditions-rule';
                                row.setAttribute( 'data-index', String( index ) );

                                var group = function ( labelText, control ) {
                                        var wrapper = document.createElement( 'div' );
                                        wrapper.className = 'fbm-registration-editor__conditions-field';
                                        var label = document.createElement( 'label' );
                                        label.className = 'fbm-registration-editor__conditions-label';
                                        label.textContent = labelText;
                                        label.appendChild( control );
                                        wrapper.appendChild( label );
                                        return wrapper;
                                };

                                var ifSelect = document.createElement( 'select' );
                                ifSelect.className = 'fbm-registration-editor__conditions-input';
                                ifSelect.setAttribute( 'data-role', 'if_field' );
                                state.fields.forEach( function ( field ) {
                                        ifSelect.appendChild( createOption( field.name, field.label || field.name, rule.if_field ) );
                                } );

                                var operatorSelect = document.createElement( 'select' );
                                operatorSelect.className = 'fbm-registration-editor__conditions-input';
                                operatorSelect.setAttribute( 'data-role', 'operator' );
                                operatorOptions.forEach( function ( option ) {
                                        operatorSelect.appendChild( createOption( option.value, option.label, rule.operator ) );
                                } );

                                var valueWrap = document.createElement( 'div' );
                                valueWrap.setAttribute( 'data-fbm-conditions-value-wrap', '1' );
                                var valueInput = document.createElement( 'input' );
                                valueInput.type = 'text';
                                valueInput.className = 'fbm-registration-editor__conditions-input';
                                valueInput.setAttribute( 'data-role', 'value' );
                                valueInput.placeholder = i18n.conditionsEmptyPlaceholder || 'Enter a value';
                                valueInput.value = rule.value || '';
                                valueWrap.appendChild( valueInput );

                                var actionSelect = document.createElement( 'select' );
                                actionSelect.className = 'fbm-registration-editor__conditions-input';
                                actionSelect.setAttribute( 'data-role', 'action' );
                                actionOptions.forEach( function ( option ) {
                                        actionSelect.appendChild( createOption( option.value, option.label, rule.action ) );
                                } );

                                var targetSelect = document.createElement( 'select' );
                                targetSelect.className = 'fbm-registration-editor__conditions-input';
                                targetSelect.setAttribute( 'data-role', 'target' );
                                state.fields.forEach( function ( field ) {
                                        targetSelect.appendChild( createOption( field.name, field.label || field.name, rule.target ) );
                                } );

                                row.appendChild( group( i18n.conditionsIfField || 'If field', ifSelect ) );
                                row.appendChild( group( i18n.conditionsOperator || 'Operator', operatorSelect ) );
                                var valueGroup = group( i18n.conditionsValue || 'Value', valueWrap );
                                row.appendChild( valueGroup );
                                row.appendChild( group( i18n.conditionsThen || 'Then', actionSelect ) );
                                row.appendChild( group( i18n.conditionsTarget || 'Field', targetSelect ) );

                                var removeButton = document.createElement( 'button' );
                                removeButton.type = 'button';
                                removeButton.className = 'button-link fbm-registration-editor__conditions-remove';
                                removeButton.setAttribute( 'data-role', 'remove' );
                                removeButton.textContent = i18n.conditionsRemove || 'Remove';
                                row.appendChild( removeButton );

                                list.appendChild( row );

                                toggleValueInput( row, rule.operator );
                        } );
                };

                var sync = function () {
                        if ( state.fields.length === 0 ) {
                                state.rules = [];
                                state.enabled = false;
                        }

                        if ( state.rules.length === 0 ) {
                                state.enabled = false;
                        }

                        if ( enabledToggle.checked !== state.enabled ) {
                                enabledToggle.checked = state.enabled;
                        }

                        storage.value = JSON.stringify( state.rules );

                        if ( emptyState ) {
                                if ( state.rules.length === 0 ) {
                                        emptyState.removeAttribute( 'hidden' );
                                } else {
                                        emptyState.setAttribute( 'hidden', 'hidden' );
                                }
                        }

                        addButton.disabled = state.fields.length === 0;
                        enabledToggle.disabled = state.fields.length === 0;
                };

                renderRules();
                sync();

                addButton.addEventListener( 'click', function () {
                        if ( state.fields.length === 0 ) {
                                return;
                        }

                        var defaults = normalizeRule( {} );
                        if ( state.fields.length > 1 ) {
                                defaults.target = state.fields[ state.fields.length - 1 ].name;
                        }

                        state.rules.push( defaults );
                        renderRules();
                        sync();

                        var lastRow = list.querySelector( '[data-index="' + ( state.rules.length - 1 ) + '"]' );
                        if ( lastRow ) {
                                var firstInput = lastRow.querySelector( 'select, input' );
                                if ( firstInput && typeof firstInput.focus === 'function' ) {
                                        firstInput.focus();
                                }
                        }
                } );

                enabledToggle.addEventListener( 'change', function () {
                        state.enabled = enabledToggle.checked;
                        if ( state.rules.length === 0 ) {
                                state.enabled = false;
                                enabledToggle.checked = false;
                        }
                        sync();
                } );

                list.addEventListener( 'change', function ( event ) {
                        var row = event.target.closest( '[data-index]' );
                        if ( ! row ) {
                                return;
                        }

                        var index = parseInt( row.getAttribute( 'data-index' ), 10 );
                        if ( isNaN( index ) || ! state.rules[ index ] ) {
                                return;
                        }

                        var role = event.target.getAttribute( 'data-role' );

                        if ( 'operator' === role ) {
                                toggleValueInput( row, event.target.value );
                        }

                        var ifFieldSelect = row.querySelector( '[data-role="if_field"]' );
                        var operatorSelect = row.querySelector( '[data-role="operator"]' );
                        var valueInput = row.querySelector( '[data-role="value"]' );
                        var actionSelect = row.querySelector( '[data-role="action"]' );
                        var targetSelect = row.querySelector( '[data-role="target"]' );

                        state.rules[ index ] = normalizeRule( {
                                if_field: ifFieldSelect ? ifFieldSelect.value : state.rules[ index ].if_field,
                                operator: operatorSelect ? operatorSelect.value : state.rules[ index ].operator,
                                value: valueInput ? valueInput.value : '',
                                action: actionSelect ? actionSelect.value : state.rules[ index ].action,
                                target: targetSelect ? targetSelect.value : state.rules[ index ].target,
                        } );

                        sync();
                } );

                list.addEventListener( 'input', function ( event ) {
                        if ( 'value' !== event.target.getAttribute( 'data-role' ) ) {
                                return;
                        }

                        var row = event.target.closest( '[data-index]' );
                        if ( ! row ) {
                                return;
                        }

                        var index = parseInt( row.getAttribute( 'data-index' ), 10 );
                        if ( isNaN( index ) || ! state.rules[ index ] ) {
                                return;
                        }

                        state.rules[ index ].value = event.target.value || '';
                        sync();
                } );

                list.addEventListener( 'click', function ( event ) {
                        if ( event.target.getAttribute( 'data-role' ) !== 'remove' ) {
                                return;
                        }

                        event.preventDefault();
                        var row = event.target.closest( '[data-index]' );
                        if ( ! row ) {
                                return;
                        }

                        var index = parseInt( row.getAttribute( 'data-index' ), 10 );
                        if ( isNaN( index ) ) {
                                return;
                        }

                        state.rules.splice( index, 1 );
                        renderRules();
                        sync();
                } );

                if ( state.rules.length === 0 && state.fields.length > 1 ) {
                        addButton.removeAttribute( 'disabled' );
                }
        };

        initializeConditions();
})( window.jQuery );
