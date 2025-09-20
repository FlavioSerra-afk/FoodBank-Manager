(function () {
        'use strict';

        if ( typeof window.fbmRegistrationForm === 'undefined' ) {
                return;
        }

        var settings = window.fbmRegistrationForm;
        var conditions = settings.conditions || {};
        var rules = Array.isArray( conditions.rules ) ? conditions.rules.slice( 0 ) : [];

        if ( ! conditions.enabled || rules.length === 0 ) {
                return;
        }

        var hiddenClass = settings.hiddenClass || 'fbm-field--hidden';
        var forms = document.querySelectorAll( '[data-fbm-component="registration-form"]' );

        if ( forms.length === 0 ) {
                return;
        }

        var normalizeRule = function ( rule ) {
                if ( ! rule || 'object' !== typeof rule ) {
                        return null;
                }

                var operator = rule.operator || 'equals';
                var action = rule.action || 'show';
                var ifField = rule.if_field || '';
                var target = rule.target || '';

                if ( ! ifField || ! target ) {
                        return null;
                }

                if ( [ 'equals', 'not_equals', 'contains', 'empty', 'not_empty' ].indexOf( operator ) === -1 ) {
                        return null;
                }

                if ( [ 'show', 'hide' ].indexOf( action ) === -1 ) {
                        return null;
                }

                return {
                        if_field: ifField,
                        operator: operator,
                        value: rule.value || '',
                        action: action,
                        target: target,
                };
        };

        var normalizeString = function ( value ) {
                if ( value === null || value === undefined ) {
                        return '';
                }

                return String( value ).trim().toLowerCase();
        };

        var valueAsArray = function ( value ) {
                if ( Array.isArray( value ) ) {
                        return value.map( normalizeString ).filter( function ( item ) {
                                return item !== '';
                        } );
                }

                var stringValue = normalizeString( value );

                if ( '' === stringValue ) {
                        return [];
                }

                return [ stringValue ];
        };

        var isEmptyValue = function ( value ) {
                if ( Array.isArray( value ) ) {
                        return value.length === 0;
                }

                if ( value === null || value === undefined ) {
                        return true;
                }

                if ( 'boolean' === typeof value ) {
                        return value === false;
                }

                if ( 'number' === typeof value ) {
                        return false;
                }

                return '' === normalizeString( value );
        };

        var ruleMatches = function ( rule, values ) {
                        var current = values[ rule.if_field ];

                        switch ( rule.operator ) {
                                case 'equals':
                                        return valueAsArray( current ).indexOf( normalizeString( rule.value ) ) !== -1;
                                case 'not_equals':
                                        return valueAsArray( current ).indexOf( normalizeString( rule.value ) ) === -1;
                                case 'contains': {
                                        var haystack = valueAsArray( current );
                                        var needle = normalizeString( rule.value );

                                        if ( haystack.length > 0 ) {
                                                return haystack.indexOf( needle ) !== -1;
                                        }

                                        var single = normalizeString( current );
                                        return '' !== needle && single.indexOf( needle ) !== -1;
                                }
                                case 'empty':
                                        return isEmptyValue( current );
                                case 'not_empty':
                                        return ! isEmptyValue( current );
                                default:
                                        return false;
                        }
        };

        var getFieldEntry = function ( fieldMap, name ) {
                return fieldMap[name] || null;
        };

        var evaluateVisibility = function ( fieldMap, activeRules, values ) {
                var visibility = {};
                var requiresShow = {};
                var forcedHidden = {};

                Object.keys( fieldMap ).forEach( function ( name ) {
                        visibility[ name ] = true;
                } );

                activeRules.forEach( function ( rule ) {
                        if ( 'show' === rule.action ) {
                                requiresShow[ rule.target ] = true;
                        }
                } );

                Object.keys( requiresShow ).forEach( function ( name ) {
                        visibility[ name ] = false;
                } );

                activeRules.forEach( function ( rule ) {
                        if ( ! ruleMatches( rule, values ) ) {
                                return;
                        }

                        if ( 'hide' === rule.action ) {
                                visibility[ rule.target ] = false;
                                forcedHidden[ rule.target ] = true;
                                return;
                        }

                        if ( 'show' === rule.action && ! forcedHidden[ rule.target ] ) {
                                visibility[ rule.target ] = true;
                        }
                } );

                return visibility;
        };

        Array.prototype.forEach.call( forms, function ( form ) {
                var fieldContainers = form.querySelectorAll( '[data-fbm-field]' );
                var fieldMap = {};

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

                        fieldMap[ name ] = {
                                container: container,
                                controls: controls,
                                type: container.getAttribute( 'data-fbm-field-type' ) || 'text',
                        };
                } );

                var activeRules = rules.map( normalizeRule ).filter( function ( rule ) {
                        if ( null === rule ) {
                                return false;
                        }

                        return getFieldEntry( fieldMap, rule.if_field ) && getFieldEntry( fieldMap, rule.target );
                } );

                if ( activeRules.length === 0 ) {
                        return;
                }

                var readValue = function ( entry ) {
                        if ( ! entry ) {
                                return '';
                        }

                        if ( 'checkbox' === entry.type ) {
                                var selected = [];
                                entry.controls.forEach( function ( control ) {
                                        if ( control.checked ) {
                                                selected.push( control.value );
                                        }
                                } );

                                return selected;
                        }

                        if ( 'radio' === entry.type ) {
                                for ( var i = 0; i < entry.controls.length; i += 1 ) {
                                        if ( entry.controls[ i ].checked ) {
                                                return entry.controls[ i ].value;
                                        }
                                }

                                return '';
                        }

                        if ( 'select' === entry.type ) {
                                var control = entry.controls[0];
                                if ( control && control.multiple ) {
                                        var values = [];
                                        Array.prototype.slice.call( control.options ).forEach( function ( option ) {
                                                if ( option.selected ) {
                                                        values.push( option.value );
                                                }
                                        } );
                                        return values;
                                }

                                return control ? control.value : '';
                        }

                        if ( entry.controls.length > 0 ) {
                                return entry.controls[0].value;
                        }

                        return '';
                };

                var readValues = function () {
                        var values = {};

                        Object.keys( fieldMap ).forEach( function ( name ) {
                                values[ name ] = readValue( fieldMap[ name ] );
                        } );

                        return values;
                };

                var applyVisibility = function () {
                        var values = readValues();
                        var visibility = evaluateVisibility( fieldMap, activeRules, values );

                        Object.keys( fieldMap ).forEach( function ( name ) {
                                var entry = fieldMap[ name ];
                                var shouldHide = visibility[ name ] === false;

                                if ( shouldHide ) {
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

                applyVisibility();

                form.addEventListener( 'change', applyVisibility );
                form.addEventListener( 'input', applyVisibility );
        } );
})();
