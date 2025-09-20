(function ( global ) {
        'use strict';

        var Conditions = {};

        var normalizeString = function ( value ) {
                if ( value === null || value === undefined ) {
                        return '';
                }

                if ( typeof value === 'boolean' ) {
                        return value ? '1' : '0';
                }

                if ( Array.isArray( value ) ) {
                        return '';
                }

                return String( value ).trim().toLowerCase();
        };

        var isNumeric = function ( value ) {
                if ( value === '' ) {
                        return false;
                }

                var number = Number( value );

                return ! Number.isNaN( number );
        };

        var normalizeNumericValues = function ( value ) {
                if ( Array.isArray( value ) ) {
                        var numbers = [];
                        value.forEach( function ( item ) {
                                var stringValue = normalizeString( item );
                                if ( isNumeric( stringValue ) ) {
                                        numbers.push( Number( stringValue ) );
                                }
                        } );

                        return numbers;
                }

                var normalized = normalizeString( value );
                if ( ! isNumeric( normalized ) ) {
                        return [];
                }

                return [ Number( normalized ) ];
        };

        var normalizeDateValue = function ( value ) {
                var normalized = normalizeString( value );

                if ( normalized === '' ) {
                        return null;
                }

                var timestamp = Date.parse( normalized );

                return Number.isNaN( timestamp ) ? null : timestamp;
        };

        var valueAsArray = function ( value ) {
                if ( Array.isArray( value ) ) {
                        return value.map( normalizeString ).filter( function ( item ) {
                                return item !== '';
                        } );
                }

                var normalized = normalizeString( value );

                return normalized === '' ? [] : [ normalized ];
        };

        var isEmptyValue = function ( value ) {
                if ( Array.isArray( value ) ) {
                        return value.every( function ( item ) {
                                return normalizeString( item ) === '';
                        } );
                }

                if ( value === null || value === undefined ) {
                        return true;
                }

                if ( typeof value === 'boolean' ) {
                                return value === false;
                }

                if ( typeof value === 'number' ) {
                        return false;
                }

                return normalizeString( value ) === '';
        };

        var compareNumbers = function ( operator, left, right ) {
                switch ( operator ) {
                        case 'lt':
                                return left < right;
                        case 'lte':
                                return left <= right;
                        case 'gt':
                                return left > right;
                        case 'gte':
                                return left >= right;
                        default:
                                return false;
                }
        };

        var compareNumericOrDate = function ( condition, values ) {
                var current = values[ condition.field ];
                var comparison = condition.value || '';
                var fieldType = ( condition.field_type || condition.fieldType || '' ).toLowerCase();

                if ( fieldType === 'date' ) {
                        var leftDate = normalizeDateValue( current );
                        var rightDate = normalizeDateValue( comparison );

                        if ( leftDate === null || rightDate === null ) {
                                return false;
                        }

                        return compareNumbers( condition.operator, leftDate, rightDate );
                }

                var numbers = normalizeNumericValues( current );
                if ( numbers.length === 0 ) {
                        return false;
                }

                var comparisonValue = normalizeString( comparison );
                if ( ! isNumeric( comparisonValue ) ) {
                        return false;
                }

                var right = Number( comparisonValue );

                for ( var i = 0; i < numbers.length; i += 1 ) {
                        if ( compareNumbers( condition.operator, numbers[ i ], right ) ) {
                                return true;
                        }
                }

                return false;
        };

        var matchCondition = function ( condition, values ) {
                var current = values[ condition.field ];
                var comparison = condition.value || '';
                var operator = condition.operator || 'equals';

                switch ( operator ) {
                        case 'equals':
                                return valueAsArray( current ).indexOf( normalizeString( comparison ) ) !== -1;
                        case 'not_equals':
                                return valueAsArray( current ).indexOf( normalizeString( comparison ) ) === -1;
                        case 'contains': {
                                var haystack = valueAsArray( current );
                                var needle = normalizeString( comparison );

                                if ( haystack.length > 0 ) {
                                        return haystack.indexOf( needle ) !== -1;
                                }

                                var single = normalizeString( current );
                                return needle !== '' && single !== '' && single.indexOf( needle ) !== -1;
                        }
                        case 'empty':
                                return isEmptyValue( current );
                        case 'not_empty':
                                return ! isEmptyValue( current );
                        case 'lt':
                        case 'lte':
                        case 'gt':
                        case 'gte':
                                return compareNumericOrDate( condition, values );
                        default:
                                return false;
                }
        };

        var groupMatches = function ( group, values ) {
                var operator = ( group.operator || 'and' ).toLowerCase();
                operator = operator === 'or' ? 'or' : 'and';
                var conditions = Array.isArray( group.conditions ) ? group.conditions : [];

                if ( conditions.length === 0 ) {
                        return false;
                }

                for ( var i = 0; i < conditions.length; i += 1 ) {
                        var condition = conditions[ i ];
                        if ( 'and' === operator && ! matchCondition( condition, values ) ) {
                                return false;
                        }

                        if ( 'or' === operator && matchCondition( condition, values ) ) {
                                return true;
                        }
                }

                return operator === 'and';
        };

        Conditions.evaluate = function ( groups, values, defaults ) {
                var state = {};
                var normalizedGroups = Array.isArray( groups ) ? groups : [];
                var defaultsMap = defaults || {};

                Object.keys( defaultsMap ).forEach( function ( name ) {
                        state[ name ] = {
                                visible: true,
                                required: !! defaultsMap[ name ].required
                        };
                } );

                if ( normalizedGroups.length === 0 ) {
                        return state;
                }

                var ensureEntry = function ( name ) {
                        if ( ! state[ name ] ) {
                                state[ name ] = {
                                        visible: true,
                                        required: defaultsMap[ name ] ? !! defaultsMap[ name ].required : false
                                };
                        }
                };

                normalizedGroups.forEach( function ( group ) {
                        if ( ! group || ! Array.isArray( group.actions ) ) {
                                return;
                        }

                        group.actions.forEach( function ( action ) {
                                if ( action && action.type === 'show' && action.target ) {
                                        ensureEntry( action.target );
                                        state[ action.target ].visible = false;
                                }
                        } );
                } );

                normalizedGroups.forEach( function ( group ) {
                        if ( ! group || ! Array.isArray( group.actions ) ) {
                                return;
                        }

                        if ( ! groupMatches( group, values ) ) {
                                return;
                        }

                        group.actions.forEach( function ( action ) {
                                if ( ! action || ! action.target ) {
                                        return;
                                }

                                ensureEntry( action.target );

                                switch ( action.type ) {
                                        case 'show':
                                                state[ action.target ].visible = true;
                                                break;
                                        case 'hide':
                                                state[ action.target ].visible = false;
                                                break;
                                        case 'require':
                                                state[ action.target ].required = true;
                                                break;
                                        case 'optional':
                                                state[ action.target ].required = false;
                                                break;
                                        default:
                                                break;
                                }
                        } );
                } );

                return state;
        };

        Conditions.normalizeGroups = function ( groups ) {
                if ( ! Array.isArray( groups ) ) {
                        return [];
                }

                return groups.map( function ( group ) {
                        var operator = ( group && typeof group.operator === 'string' ) ? group.operator.toLowerCase() : 'and';
                        operator = operator === 'or' ? 'or' : 'and';

                        var conditions = Array.isArray( group.conditions ) ? group.conditions.map( function ( condition ) {
                                return {
                                        field: condition.field || '',
                                        operator: ( condition.operator || 'equals' ).toLowerCase(),
                                        value: condition.value || '',
                                        field_type: ( condition.field_type || condition.fieldType || '' ).toLowerCase()
                                };
                        } ).filter( function ( condition ) {
                                return condition.field !== '';
                        } ) : [];

                        var actions = Array.isArray( group.actions ) ? group.actions.map( function ( action ) {
                                return {
                                        type: ( action.type || '' ).toLowerCase(),
                                        target: action.target || ''
                                };
                        } ).filter( function ( action ) {
                                return action.type !== '' && action.target !== '';
                        } ) : [];

                        return {
                                operator: operator,
                                conditions: conditions,
                                actions: actions
                        };
                } ).filter( function ( group ) {
                        return group.conditions.length > 0 && group.actions.length > 0;
                } );
        };

        Conditions.groupMatches = function ( group, values ) {
                if ( ! group || 'object' !== typeof group ) {
                        return false;
                }

                return groupMatches( group, values || {} );
        };

        Conditions.conditionMatches = function ( condition, values ) {
                if ( ! condition || 'object' !== typeof condition ) {
                        return false;
                }

                return matchCondition( condition, values || {} );
        };

        global.fbmRegistrationConditions = Conditions;
}( window ));
