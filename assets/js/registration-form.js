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
        var raf = window.requestAnimationFrame || function ( callback ) {
                return window.setTimeout( callback, 16 );
        };
        var debounce = function ( fn, wait ) {
                var timer = null;

                return function () {
                        var context = this;
                        var args = arguments;

                        if ( timer ) {
                                window.clearTimeout( timer );
                        }

                        timer = window.setTimeout( function () {
                                timer = null;
                                fn.apply( context, args );
                        }, wait );
                };
        };
        var defer = function ( fn ) {
                if ( 'function' !== typeof fn ) {
                        return null;
                }

                if ( 'function' === typeof window.requestIdleCallback ) {
                        return window.requestIdleCallback( fn, { timeout: 300 } );
                }

                return window.setTimeout( fn, 0 );
        };
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
                                control.setAttribute( 'data-fbm-field-control', name );
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

                var fieldNames = Object.keys( fieldMap );
                if ( fieldNames.length === 0 ) {
                        return;
                }

                var filteredGroups = normalizedGroups.map( function ( group ) {
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

                if ( filteredGroups.length === 0 ) {
                        return;
                }

                var processedGroups = filteredGroups.map( function ( group ) {
                        var dependenciesMap = {};
                        group.conditions.forEach( function ( condition ) {
                                dependenciesMap[ condition.field ] = true;
                        } );

                        return {
                                original: group,
                                dependencies: Object.keys( dependenciesMap ),
                                actions: group.actions
                        };
                } );

                var showDefaults = {};
                processedGroups.forEach( function ( group ) {
                        group.actions.forEach( function ( action ) {
                                if ( action.type === 'show' ) {
                                        showDefaults[ action.target ] = true;
                                }
                        } );
                } );

                var valuesEqual = function ( left, right ) {
                        if ( Array.isArray( left ) || Array.isArray( right ) ) {
                                var arrayLeft = Array.isArray( left ) ? left.slice().sort() : ( '' === left || left === undefined ? [] : [ left ] );
                                var arrayRight = Array.isArray( right ) ? right.slice().sort() : ( '' === right || right === undefined ? [] : [ right ] );

                                if ( arrayLeft.length !== arrayRight.length ) {
                                        return false;
                                }

                                for ( var i = 0; i < arrayLeft.length; i += 1 ) {
                                        if ( arrayLeft[ i ] !== arrayRight[ i ] ) {
                                                return false;
                                        }
                                }

                                return true;
                        }

                        return left === right;
                };

                var ensureState = function ( map, name ) {
                        if ( ! map[ name ] ) {
                                var defaults = defaultsMap[ name ] || { required: false };
                                map[ name ] = {
                                        visible: ! showDefaults[ name ],
                                        required: !! defaults.required
                                };
                        }

                        return map[ name ];
                };

                var intersects = function ( dependencies, changed ) {
                        if ( ! Array.isArray( dependencies ) || dependencies.length === 0 ) {
                                return false;
                        }

                        for ( var i = 0; i < dependencies.length; i += 1 ) {
                                if ( changed.indexOf( dependencies[ i ] ) !== -1 ) {
                                        return true;
                                }
                        }

                        return false;
                };

                var recomputeState = function () {
                        var state = {};

                        fieldNames.forEach( function ( name ) {
                                state[ name ] = {
                                        visible: ! showDefaults[ name ],
                                        required: !! ( defaultsMap[ name ] && defaultsMap[ name ].required )
                                };
                        } );

                        processedGroups.forEach( function ( group, index ) {
                                if ( ! groupMatches[ index ] ) {
                                        return;
                                }

                                group.actions.forEach( function ( action ) {
                                        var entry = ensureState( state, action.target );

                                        switch ( action.type ) {
                                                case 'show':
                                                        entry.visible = true;
                                                        break;
                                                case 'hide':
                                                        entry.visible = false;
                                                        break;
                                                case 'require':
                                                        entry.required = true;
                                                        break;
                                                case 'optional':
                                                        entry.required = false;
                                                        break;
                                                default:
                                                        break;
                                        }
                                } );
                        } );

                        return state;
                };

                var appliedState = {};
                var pendingState = null;
                var applyHandle = null;
                var lastValues = readValues( fieldMap );
                var groupMatches = processedGroups.map( function ( group ) {
                        return Conditions.groupMatches( group.original, lastValues );
                } );
                var initialized = false;

                var applyDom = function ( nextState ) {
                        Object.keys( fieldMap ).forEach( function ( name ) {
                                var entry = fieldMap[ name ];
                                var previous = appliedState[ name ] || {
                                        visible: true,
                                        required: entry.defaultRequired
                                };
                                var current = nextState[ name ] || {
                                        visible: true,
                                        required: entry.defaultRequired
                                };

                                if ( previous.visible !== current.visible ) {
                                        if ( current.visible ) {
                                                entry.container.classList.remove( hiddenClass );
                                                entry.container.removeAttribute( 'hidden' );
                                                entry.container.removeAttribute( 'aria-hidden' );

                                                entry.controls.forEach( function ( control ) {
                                                        if ( control.getAttribute( 'data-fbm-disabled' ) === '1' ) {
                                                                control.disabled = false;
                                                                control.removeAttribute( 'data-fbm-disabled' );
                                                        }
                                                } );
                                        } else {
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
                                        }
                                }

                                if ( previous.required !== current.required ) {
                                        entry.controls.forEach( function ( control ) {
                                                if ( current.required ) {
                                                        control.setAttribute( 'required', 'required' );
                                                } else {
                                                        control.removeAttribute( 'required' );
                                                }
                                        } );
                                }

                                entry.container.setAttribute( 'data-fbm-required-state', current.required ? 'required' : 'optional' );
                        } );
                };

                var evaluate = function ( changedList ) {
                        var newValues = Object.assign( {}, lastValues );
                        var changedFields = Array.isArray( changedList ) ? changedList.slice() : fieldNames.slice();
                        var effectiveChanges = [];

                        if ( Array.isArray( changedList ) && changedList.length > 0 ) {
                                changedFields = [];
                                changedList.forEach( function ( name ) {
                                        if ( ! fieldMap[ name ] ) {
                                                return;
                                        }

                                        var latest = readFieldValue( fieldMap[ name ] );
                                        if ( ! valuesEqual( lastValues[ name ], latest ) ) {
                                                newValues[ name ] = latest;
                                                effectiveChanges.push( name );
                                        }
                                } );

                                if ( effectiveChanges.length === 0 && initialized ) {
                                        return;
                                }
                        } else {
                                effectiveChanges = fieldNames.slice();
                                Object.keys( fieldMap ).forEach( function ( name ) {
                                        newValues[ name ] = readFieldValue( fieldMap[ name ] );
                                } );
                        }

                        var dirty = ! initialized;
                        processedGroups.forEach( function ( group, index ) {
                                if ( ! dirty && effectiveChanges.length > 0 && ! intersects( group.dependencies, effectiveChanges ) ) {
                                        return;
                                }

                                var matches = Conditions.groupMatches( group.original, newValues );
                                if ( matches !== groupMatches[ index ] ) {
                                        dirty = true;
                                        groupMatches[ index ] = matches;
                                }
                        } );

                        lastValues = newValues;

                        if ( ! dirty && initialized ) {
                                return;
                        }

                        var nextState = recomputeState();
                        pendingState = nextState;

                        if ( applyHandle ) {
                                return;
                        }

                        applyHandle = raf( function () {
                                applyHandle = null;

                                if ( ! pendingState ) {
                                        return;
                                }

                                applyDom( pendingState );
                                appliedState = pendingState;
                                pendingState = null;
                        } );
                        initialized = true;
                };

                var scheduleEvaluate = debounce( function ( fieldName ) {
                        if ( typeof fieldName === 'string' && fieldName ) {
                                evaluate( [ fieldName ] );
                        } else {
                                evaluate();
                        }
                }, settings.debounce || 180 );

                scheduleEvaluate();

                var handleEvent = function ( event ) {
                        var container = event.target.closest( '[data-fbm-field]' );
                        var name = container ? container.getAttribute( 'data-fbm-field' ) : '';
                        scheduleEvaluate( name );
                };

                defer( function () {
                        form.addEventListener( 'change', handleEvent );
                        form.addEventListener( 'input', handleEvent );
                } );
        } );
})();
