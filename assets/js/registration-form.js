(function () {
        'use strict';

        if ( typeof window.fbmRegistrationForm === 'undefined' ) {
                return;
        }

        if ( typeof window.fbmRegistrationConditions === 'undefined' ) {
                return;
        }

        var settings = window.fbmRegistrationForm;
        var conditions = settings.conditions || {};
        var groupsRaw = Array.isArray( conditions.groups ) ? conditions.groups : [];

        if ( ! conditions.enabled || groupsRaw.length === 0 ) {
                return;
        }

        var forms = document.querySelectorAll( '[data-fbm-component="registration-form"]' );

        if ( forms.length === 0 ) {
                return;
        }

        var Conditions = window.fbmRegistrationConditions;
        var normalizedGroups = Conditions.normalizeGroups( groupsRaw );

        var summary = document.querySelector( '[data-fbm-error-summary]' );
        if ( summary && typeof summary.focus === 'function' ) {
                summary.focus();
        }

        if ( summary ) {
                summary.addEventListener( 'click', function ( event ) {
                        var link = event.target.closest( 'a[data-fbm-error-link]' );
                        if ( ! link ) {
                                return;
                        }

                        event.preventDefault();
                        var targetId = link.getAttribute( 'href' ) || '';
                        if ( targetId.charAt( 0 ) === '#' ) {
                                targetId = targetId.slice( 1 );
                        }

                        if ( ! targetId ) {
                                return;
                        }

                        var target = document.getElementById( targetId );
                        if ( ! target ) {
                                return;
                        }

                        target.scrollIntoView( { behavior: 'smooth', block: 'start' } );

                        window.setTimeout( function () {
                                var focusable = target.querySelector( 'input, select, textarea, button' );
                                if ( focusable && typeof focusable.focus === 'function' ) {
                                        focusable.focus();
                                }
                        }, 120 );
                } );
        }

        var hiddenClass = settings.hiddenClass || 'fbm-field--hidden';

        var readFieldValue = function ( entry ) {
                if ( ! entry ) {
                        return '';
                }

                if ( entry.type === 'checkbox' ) {
                        var selected = [];
                        entry.controls.forEach( function ( control ) {
                                if ( control.checked ) {
                                        selected.push( control.value );
                                }
                        } );

                        return selected;
                }

                if ( entry.type === 'radio' ) {
                        for ( var i = 0; i < entry.controls.length; i += 1 ) {
                                if ( entry.controls[ i ].checked ) {
                                        return entry.controls[ i ].value;
                                }
                        }

                        return '';
                }

                if ( entry.type === 'select' ) {
                        var control = entry.controls[ 0 ];
                        if ( ! control ) {
                                return '';
                        }

                        if ( control.multiple ) {
                                var values = [];
                                Array.prototype.slice.call( control.options ).forEach( function ( option ) {
                                        if ( option.selected ) {
                                                values.push( option.value );
                                        }
                                } );

                                return values;
                        }

                        return control.value;
                }

                if ( entry.controls.length > 0 ) {
                        return entry.controls[ 0 ].value;
                }

                return '';
        };

        var readValues = function ( fieldMap ) {
                var values = {};

                Object.keys( fieldMap ).forEach( function ( name ) {
                        values[ name ] = readFieldValue( fieldMap[ name ] );
                } );

                return values;
        };

        Array.prototype.forEach.call( forms, function ( form ) {
                var fieldContainers = form.querySelectorAll( '[data-fbm-field]' );
                var fieldMap = {};
                var defaultsMap = {};

                Array.prototype.forEach.call( fieldContainers, function ( container ) {
                        var name = container.getAttribute( 'data-fbm-field' );
                        if ( ! name ) {
                                return;
                        }

                        var controls = Array.prototype.slice.call( container.querySelectorAll( 'input, select, textarea' ) );
                        controls.forEach( function ( control ) {
                                if ( control.disabled ) {
                                        control.setAttribute( 'data-fbm-original-disabled', '1' );
                                }
                        } );

                        var type = container.getAttribute( 'data-fbm-field-type' ) || 'text';
                        var requiredDefault = container.getAttribute( 'data-fbm-field-required' ) === '1';

                        fieldMap[ name ] = {
                                container: container,
                                controls: controls,
                                type: type,
                                defaultRequired: requiredDefault
                        };

                        defaultsMap[ name ] = {
                                required: requiredDefault
                        };
                } );

                if ( Object.keys( fieldMap ).length === 0 ) {
                        return;
                }

                var groups = normalizedGroups.map( function ( group ) {
                        return {
                                operator: group.operator,
                                conditions: group.conditions.filter( function ( condition ) {
                                        return !! fieldMap[ condition.field ];
                                } ),
                                actions: group.actions.filter( function ( action ) {
                                        return !! fieldMap[ action.target ];
                                } )
                        };
                } ).filter( function ( group ) {
                        return group.conditions.length > 0 && group.actions.length > 0;
                } );

                if ( groups.length === 0 ) {
                        return;
                }

                var applyState = function () {
                        var values = readValues( fieldMap );
                        var state = Conditions.evaluate( groups, values, defaultsMap );

                        Object.keys( fieldMap ).forEach( function ( name ) {
                                var entry = fieldMap[ name ];
                                var current = state[ name ] || {
                                        visible: true,
                                        required: entry.defaultRequired
                                };

                                entry.container.setAttribute( 'data-fbm-required-state', current.required ? 'required' : 'optional' );

                                if ( current.visible === false ) {
                                        entry.container.classList.add( hiddenClass );
                                        entry.container.setAttribute( 'hidden', 'hidden' );
                                        entry.container.setAttribute( 'aria-hidden', 'true' );

                                        entry.controls.forEach( function ( control ) {
                                                if ( control.getAttribute( 'data-fbm-original-disabled' ) === '1' ) {
                                                        return;
                                                }

                                                if ( ! control.disabled ) {
                                                        control.disabled = true;
                                                        control.setAttribute( 'data-fbm-disabled', '1' );
                                                }
                                        } );
                                } else {
                                        entry.container.classList.remove( hiddenClass );
                                        entry.container.removeAttribute( 'hidden' );
                                        entry.container.removeAttribute( 'aria-hidden' );

                                        entry.controls.forEach( function ( control ) {
                                                if ( control.getAttribute( 'data-fbm-disabled' ) === '1' ) {
                                                        control.disabled = false;
                                                        control.removeAttribute( 'data-fbm-disabled' );
                                                }
                                        } );
                                }
                        } );
                };

                applyState();

                form.addEventListener( 'change', applyState );
                form.addEventListener( 'input', applyState );
        } );
})();
