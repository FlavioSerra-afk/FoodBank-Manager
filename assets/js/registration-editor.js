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

                        const preview = window.open( '', 'fbmRegistrationPreview' );
                        if ( preview ) {
                                preview.document.open();
                                preview.document.write( body.markup );
                                preview.document.close();
                        }
                } ).catch( function () {
                        window.alert( settings.i18n && settings.i18n.previewError ? settings.i18n.previewError : 'Preview unavailable.' );
                } );
        } );
})( window.jQuery );
