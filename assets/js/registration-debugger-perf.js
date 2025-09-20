(function ( root, factory ) {
        if ( typeof module === 'object' && module.exports ) {
                module.exports = factory();
        } else {
                root.fbmRegistrationDebugTrace = factory();
        }
}( typeof self !== 'undefined' ? self : this, function () {
        'use strict';

        var clampWindow = function ( value, fallback ) {
                var size = parseInt( value, 10 );
                if ( Number.isNaN( size ) || size <= 0 ) {
                        return fallback;
                }

                return size;
        };

        var sanitizeDuration = function ( value ) {
                if ( typeof value !== 'number' || ! Number.isFinite( value ) ) {
                        return 0;
                }

                if ( value < 0 ) {
                        return 0;
                }

                return value;
        };

        var clonePhases = function ( phases ) {
                var output = {};
                Object.keys( phases ).forEach( function ( key ) {
                        output[ key ] = sanitizeDuration( phases[ key ] );
                } );

                return output;
        };

        var createTracker = function ( options ) {
                var maxSamples = clampWindow( options && options.maxSamples, 30 );
                var history = [];
                var totals = Object.create( null );
                var counts = Object.create( null );
                var minimums = Object.create( null );
                var maximums = Object.create( null );

                var trimHistory = function () {
                        while ( history.length > maxSamples ) {
                                history.shift();
                        }
                };

                var recompute = function () {
                        totals = Object.create( null );
                        counts = Object.create( null );
                        minimums = Object.create( null );
                        maximums = Object.create( null );

                        history.forEach( function ( sample ) {
                                Object.keys( sample.phases ).forEach( function ( phase ) {
                                        var duration = sanitizeDuration( sample.phases[ phase ] );

                                        if ( ! counts[ phase ] ) {
                                                counts[ phase ] = 0;
                                                totals[ phase ] = 0;
                                                minimums[ phase ] = duration;
                                                maximums[ phase ] = duration;
                                        }

                                        counts[ phase ] += 1;
                                        totals[ phase ] += duration;
                                        if ( duration < minimums[ phase ] ) {
                                                minimums[ phase ] = duration;
                                        }
                                        if ( duration > maximums[ phase ] ) {
                                                maximums[ phase ] = duration;
                                        }
                                } );
                        } );
                };

                var addSample = function ( sample ) {
                        history.push( {
                                timestamp: sample.timestamp,
                                groups: sample.groups,
                                phases: clonePhases( sample.phases )
                        } );
                        trimHistory();
                        recompute();
                };

                return {
                        record: function ( phases, meta ) {
                                if ( ! phases || 'object' !== typeof phases ) {
                                        return;
                                }

                                var normalized = {};
                                Object.keys( phases ).forEach( function ( key ) {
                                        normalized[ key ] = sanitizeDuration( phases[ key ] );
                                } );

                                addSample( {
                                        phases: normalized,
                                        timestamp: ( meta && meta.timestamp ) || Date.now(),
                                        groups: meta && meta.groups ? parseInt( meta.groups, 10 ) || 0 : 0
                                } );
                        },
                        stats: function () {
                                var summary = {};
                                Object.keys( counts ).forEach( function ( phase ) {
                                        if ( ! counts[ phase ] ) {
                                                return;
                                        }

                                        summary[ phase ] = {
                                                count: counts[ phase ],
                                                average: counts[ phase ] > 0 ? totals[ phase ] / counts[ phase ] : 0,
                                                min: minimums[ phase ],
                                                max: maximums[ phase ]
                                        };
                                } );

                                return summary;
                        },
                        history: function () {
                                return history.slice();
                        },
                        clear: function () {
                                history = [];
                                totals = Object.create( null );
                                counts = Object.create( null );
                                minimums = Object.create( null );
                                maximums = Object.create( null );
                        },
                        window: function () {
                                return maxSamples;
                        }
                };
        };

        return {
                createTracker: createTracker
        };
}));
