(function ( $ ) {
        'use strict';

        if ( typeof window.fbmRegistrationEditor === 'undefined' ) {
                return;
        }

        const settings = window.fbmRegistrationEditor;
        const textarea = document.getElementById( settings.textareaId || '' );
        let codeEditor = null;

        if ( textarea && window.wp && window.wp.codeEditor ) {
                const editorSettings = settings.codeEditor || {};
                editorSettings.codemirror = editorSettings.codemirror || {};
                if ( settings.editorTheme ) {
                        editorSettings.codemirror.theme = settings.editorTheme;
                }

                const editor = window.wp.codeEditor.initialize( textarea, editorSettings );
                if ( editor && editor.codemirror ) {
                        codeEditor = editor.codemirror;
                }
        }

        const getTemplate = function () {
                if ( codeEditor ) {
                        return codeEditor.getValue();
                }

                return textarea ? textarea.value : '';
        };

        const modal = document.querySelector( '[data-fbm-preview-modal]' );
        const dialog = modal ? modal.querySelector( '[data-fbm-preview-dialog]' ) : null;
        const content = modal ? modal.querySelector( '[data-fbm-preview-content]' ) : null;
        const warningsWrapper = modal ? modal.querySelector( '[data-fbm-preview-warnings]' ) : null;
        const warningsList = warningsWrapper ? warningsWrapper.querySelector( 'ul' ) : null;
        const note = modal ? modal.querySelector( '[data-fbm-preview-note]' ) : null;
        let lastFocused = null;
        let keydownHandler = null;
        const focusableSelector = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

        const resetWarnings = function () {
                if ( warningsList ) {
                        warningsList.innerHTML = '';
                }

                if ( warningsWrapper ) {
                        warningsWrapper.setAttribute( 'hidden', 'hidden' );
                }
        };

        const applyWarnings = function ( messages ) {
                resetWarnings();

                if ( ! warningsWrapper || ! warningsList || ! Array.isArray( messages ) ) {
                        return;
                }

                messages.forEach( function ( message ) {
                        if ( typeof message !== 'string' ) {
                                return;
                        }

                        const trimmed = message.trim();
                        if ( '' === trimmed ) {
                                return;
                        }

                        const item = document.createElement( 'li' );
                        item.textContent = trimmed;
                        warningsList.appendChild( item );
                } );

                if ( warningsList.childElementCount > 0 ) {
                        warningsWrapper.removeAttribute( 'hidden' );
                }
        };

        const disablePreviewControls = function () {
                if ( ! content ) {
                        return;
                }

                const controls = content.querySelectorAll( 'input, select, textarea, button' );
                controls.forEach( function ( control ) {
                        control.setAttribute( 'disabled', 'disabled' );
                        control.setAttribute( 'aria-disabled', 'true' );
                        control.setAttribute( 'tabindex', '-1' );
                } );
        };

        const isModalOpen = function () {
                return modal && ! modal.hasAttribute( 'hidden' );
        };

        const closeModal = function () {
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

        const focusDialog = function () {
                if ( ! dialog ) {
                        return;
                }

                const focusable = dialog.querySelectorAll( focusableSelector );
                if ( focusable.length > 0 ) {
                        focusable[0].focus();
                } else {
                        dialog.focus();
                }
        };

        const bindKeydown = function () {
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

                        const focusable = Array.prototype.slice.call( dialog.querySelectorAll( focusableSelector ) );

                        if ( focusable.length === 0 ) {
                                event.preventDefault();
                                dialog.focus();

                                return;
                        }

                        const first = focusable[0];
                        const last = focusable[ focusable.length - 1 ];

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

        const openModal = function ( markup, warnings, nonce ) {
                if ( ! modal || ! dialog || ! content ) {
                        const previewWindow = window.open( '', 'fbmRegistrationPreview' );
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
                        modal.querySelectorAll( '[data-fbm-preview-close]' ).forEach( function ( element ) {
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

                const snippet = $( this ).data( 'fbm-snippet' );
                if ( ! snippet ) {
                        return;
                }

                if ( codeEditor ) {
                        codeEditor.replaceSelection( snippet + '\n' );
                        codeEditor.focus();
                        return;
                }

                if ( textarea ) {
                        const start = textarea.selectionStart || 0;
                        const end = textarea.selectionEnd || 0;
                        const value = textarea.value || '';
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

                        const warnings = Array.isArray( body.warnings ) ? body.warnings : [];
                        openModal( body.markup, warnings, body.nonce || '' );
                } ).catch( function () {
                        window.alert( settings.i18n && settings.i18n.previewError ? settings.i18n.previewError : 'Preview unavailable.' );
                } );
        } );
})( window.jQuery );
