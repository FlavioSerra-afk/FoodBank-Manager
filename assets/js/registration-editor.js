(function ( $ ) {
        'use strict';

        if ( typeof window.fbmRegistrationEditor === 'undefined' ) {
                return;
        }

        var settings = window.fbmRegistrationEditor;
        var Conditions = window.fbmRegistrationConditions || null;
        var rootForm = document.querySelector( '.fbm-registration-editor__form' );

        if ( ! rootForm ) {
                return;
        }

        var textareaId = settings.textareaId || '';
        var textarea = textareaId ? document.getElementById( textareaId ) : null;
        var codeEditor = null;
        var i18n = settings.i18n || {};
        var idCounter = 0;

        var format = function ( template ) {
                if ( typeof template !== 'string' ) {
                        return '';
                }

                var output = template;
                var replacements = Array.prototype.slice.call( arguments, 1 );

                replacements.forEach( function ( value, index ) {
                        var token = '%s';
                        if ( -1 !== template.indexOf( '%1$s' ) ) {
                                token = '%' + ( index + 1 ) + '$s';
                        }

                        output = output.replace( token, value );
                } );

                return output;
        };

        var uniqueId = function ( prefix ) {
                idCounter += 1;

                return ( prefix || 'fbm' ) + '-' + idCounter;
        };

        var formatTimestamp = function ( timestamp ) {
                if ( ! timestamp ) {
                        return '';
                }

                var date = new Date( timestamp * 1000 );

                if ( window.Intl && window.Intl.DateTimeFormat ) {
                        try {
                                return new window.Intl.DateTimeFormat( undefined, {
                                        year: 'numeric',
                                        month: 'short',
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                } ).format( date );
                        } catch ( err ) {
                                // Fall through to default.
                        }
                }

                return date.toISOString();
        };

        var toBool = function ( value ) {
                if ( 'string' === typeof value ) {
                        return value === '1' || value === 'true';
                }

                return !! value;
        };

        var ensureArray = function ( value ) {
                return Array.isArray( value ) ? value : [];
        };

        var notifyDirty = function () {};

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

        var setTemplateValue = function ( value ) {
                var content = value || '';

                if ( codeEditor ) {
                        codeEditor.setValue( content );
                        codeEditor.refresh();

                        return;
                }

                if ( textarea ) {
                        textarea.value = content;
                }
        };

        var ToolbarShortcuts = ( function () {
                var toggle = document.querySelector( '[data-fbm-shortcuts-toggle]' );
                var popover = document.querySelector( '[data-fbm-shortcuts-popover]' );

                if ( ! toggle || ! popover ) {
                        return {
                                close: function () {},
                        };
                }

                var isOpen = false;

                var close = function () {
                        if ( ! isOpen ) {
                                return;
                        }

                        popover.setAttribute( 'hidden', 'hidden' );
                        toggle.setAttribute( 'aria-expanded', 'false' );
                        isOpen = false;
                };

                var open = function () {
                        if ( isOpen ) {
                                return;
                        }

                        popover.removeAttribute( 'hidden' );
                        toggle.setAttribute( 'aria-expanded', 'true' );
                        isOpen = true;
                };

                toggle.addEventListener( 'click', function ( event ) {
                        event.preventDefault();

                        if ( isOpen ) {
                                close();
                        } else {
                                open();
                        }
                } );

                document.addEventListener( 'click', function ( event ) {
                        if ( ! isOpen ) {
                                return;
                        }

                        if ( event.target === toggle || toggle.contains( event.target ) ) {
                                return;
                        }

                        if ( popover.contains( event.target ) ) {
                                return;
                        }

                        close();
                } );

                document.addEventListener( 'keydown', function ( event ) {
                        if ( 'Escape' === event.key ) {
                                close();
                        }
                } );

                return {
                        close: close,
                };
        }() );
        var ConditionManager = function ( root, fieldList, options ) {
                this.root = root;
                this.fields = Array.isArray( fieldList ) ? fieldList.slice( 0 ) : [];
                this.fieldMap = {};
                this.storage = root.querySelector( '[data-fbm-conditions-storage]' );
                this.groupsWrap = root.querySelector( '[data-fbm-conditions-groups]' );
                this.emptyState = root.querySelector( '[data-fbm-conditions-empty]' );
                this.addGroupButton = root.querySelector( '[data-fbm-conditions-add-group]' );
                this.validateButton = root.querySelector( '[data-fbm-conditions-validate]' );
                this.status = root.querySelector( '[data-fbm-conditions-status]' );
                this.report = root.querySelector( '[data-fbm-conditions-report]' );
                this.reportList = this.report ? this.report.querySelector( 'ul' ) : null;
                this.enabledToggle = root.querySelector( '[data-fbm-conditions-enabled]' );
                this.onChange = options && options.onChange ? options.onChange : function () {};
                this.i18n = options && options.i18n ? options.i18n : {};
                this.groups = [];
                this.enabled = true;
                this.disableUi = this.fields.length === 0;

                for ( var i = 0; i < this.fields.length; i += 1 ) {
                        var field = this.fields[ i ];
                        if ( field && field.name ) {
                                this.fieldMap[ field.name ] = field;
                        }
                }

                this.bind();
        };

        ConditionManager.prototype.bind = function () {
                var self = this;

                if ( this.addGroupButton ) {
                        this.addGroupButton.addEventListener( 'click', function ( event ) {
                                event.preventDefault();

                                if ( self.disableUi ) {
                                        return;
                                }

                                self.addGroup();
                        } );

                        if ( this.disableUi ) {
                                this.addGroupButton.setAttribute( 'disabled', 'disabled' );
                        }
                }

                if ( this.validateButton ) {
                        this.validateButton.addEventListener( 'click', function ( event ) {
                                event.preventDefault();
                                self.validate();
                        } );
                }

                if ( this.enabledToggle ) {
                        this.enabledToggle.addEventListener( 'change', function () {
                                self.enabled = self.enabledToggle.checked;
                                self.onChange();
                        } );
                }
        };

        ConditionManager.prototype.setInitialState = function ( groups, enabled ) {
                this.enabled = !! enabled;
                if ( this.enabledToggle ) {
                        this.enabledToggle.checked = this.enabled;
                }

                this.groups = this.normalizeGroups( groups );
                this.render();
                this.updateStorage();
        };

        ConditionManager.prototype.normalizeGroups = function ( groups ) {
                var normalized = [];
                if ( ! Array.isArray( groups ) ) {
                        return normalized;
                }

                for ( var i = 0; i < groups.length; i += 1 ) {
                        var group = groups[ i ];
                        if ( ! group || 'object' !== typeof group ) {
                                continue;
                        }

                        var operator = ( group.operator || 'and' ).toLowerCase();
                        if ( 'or' !== operator ) {
                                operator = 'and';
                        }

                        var conditions = ensureArray( group.conditions ).filter( function ( condition ) {
                                return condition && condition.field;
                        } ).map( function ( condition ) {
                                return {
                                        id: uniqueId( 'condition' ),
                                        field: condition.field,
                                        operator: ( condition.operator || 'equals' ).toLowerCase(),
                                        value: condition.value || '',
                                };
                        } ).filter( function ( condition ) {
                                return !! condition.field && !! this.fieldMap[ condition.field ];
                        }.bind( this ) );

                        var actions = ensureArray( group.actions ).filter( function ( action ) {
                                return action && action.target;
                        } ).map( function ( action ) {
                                return {
                                        id: uniqueId( 'action' ),
                                        type: ( action.type || 'show' ).toLowerCase(),
                                        target: action.target,
                                };
                        } ).filter( function ( action ) {
                                return !! action.target && !! this.fieldMap[ action.target ];
                        }.bind( this ) );

                        if ( conditions.length === 0 || actions.length === 0 ) {
                                continue;
                        }

                        normalized.push( {
                                id: uniqueId( 'group' ),
                                operator: operator,
                                conditions: conditions,
                                actions: actions,
                        } );
                }

                return normalized;
        };

        ConditionManager.prototype.render = function () {
                if ( ! this.groupsWrap ) {
                        return;
                }

                this.groupsWrap.innerHTML = '';

                if ( this.groups.length === 0 ) {
                        if ( this.emptyState ) {
                                this.emptyState.removeAttribute( 'hidden' );
                        }

                        return;
                }

                if ( this.emptyState ) {
                        this.emptyState.setAttribute( 'hidden', 'hidden' );
                }

                for ( var i = 0; i < this.groups.length; i += 1 ) {
                        this.groupsWrap.appendChild( this.renderGroup( this.groups[ i ], i ) );
                }
        };

        ConditionManager.prototype.renderGroup = function ( group, index ) {
                var self = this;
                var wrapper = document.createElement( 'div' );
                wrapper.className = 'fbm-registration-editor__conditions-group';
                wrapper.setAttribute( 'data-fbm-group', group.id );

                var header = document.createElement( 'div' );
                header.className = 'fbm-registration-editor__conditions-group-header';

                var title = document.createElement( 'div' );
                title.className = 'fbm-registration-editor__conditions-group-title';
                title.textContent = format( this.i18n.groupLabel || 'Group %s', index + 1 );

                header.appendChild( title );

                var operatorWrap = document.createElement( 'div' );
                operatorWrap.className = 'fbm-registration-editor__conditions-operator-select';

                var operatorLabel = document.createElement( 'label' );
                operatorLabel.textContent = this.i18n.groupOperatorLabel || 'Match when';
                operatorWrap.appendChild( operatorLabel );

                var operatorSelect = document.createElement( 'select' );
                var operatorOptions = [
                        { value: 'and', label: this.i18n.groupOperatorAnd || 'All conditions match' },
                        { value: 'or', label: this.i18n.groupOperatorOr || 'Any condition matches' },
                ];

                operatorOptions.forEach( function ( option ) {
                        var opt = document.createElement( 'option' );
                        opt.value = option.value;
                        opt.textContent = option.label;
                        if ( option.value === group.operator ) {
                                opt.selected = true;
                        }
                        operatorSelect.appendChild( opt );
                } );

                operatorSelect.addEventListener( 'change', function () {
                        group.operator = operatorSelect.value === 'or' ? 'or' : 'and';
                        self.onChange();
                } );

                operatorWrap.appendChild( operatorSelect );
                header.appendChild( operatorWrap );

                var groupActions = document.createElement( 'div' );
                groupActions.className = 'fbm-registration-editor__conditions-group-actions';

                var addCondition = document.createElement( 'button' );
                addCondition.type = 'button';
                addCondition.className = 'button';
                addCondition.textContent = this.i18n.addCondition || 'Add condition';
                addCondition.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        self.addCondition( group );
                } );
                groupActions.appendChild( addCondition );

                var addAction = document.createElement( 'button' );
                addAction.type = 'button';
                addAction.className = 'button';
                addAction.textContent = this.i18n.addAction || 'Add action';
                addAction.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        self.addAction( group );
                } );
                groupActions.appendChild( addAction );

                var removeGroup = document.createElement( 'button' );
                removeGroup.type = 'button';
                removeGroup.className = 'button button-link-delete fbm-registration-editor__conditions-remove-group';
                removeGroup.textContent = this.i18n.removeGroup || 'Remove group';
                removeGroup.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        self.removeGroup( group.id );
                } );
                groupActions.appendChild( removeGroup );

                header.appendChild( groupActions );
                wrapper.appendChild( header );

                var conditionList = document.createElement( 'div' );
                conditionList.className = 'fbm-registration-editor__conditions-list';

                for ( var c = 0; c < group.conditions.length; c += 1 ) {
                        conditionList.appendChild( this.renderConditionRow( group, group.conditions[ c ] ) );
                }

                wrapper.appendChild( conditionList );

                var actionList = document.createElement( 'div' );
                actionList.className = 'fbm-registration-editor__conditions-list';

                for ( var a = 0; a < group.actions.length; a += 1 ) {
                        actionList.appendChild( this.renderActionRow( group, group.actions[ a ] ) );
                }

                wrapper.appendChild( actionList );

                return wrapper;
        };
        ConditionManager.prototype.operatorOptions = function ( fieldName ) {
                var options = [
                        { value: 'equals', label: this.i18n.operatorEquals || 'is' },
                        { value: 'not_equals', label: this.i18n.operatorNotEquals || 'is not' },
                        { value: 'contains', label: this.i18n.operatorContains || 'contains' },
                        { value: 'empty', label: this.i18n.operatorEmpty || 'is empty' },
                        { value: 'not_empty', label: this.i18n.operatorNotEmpty || 'is not empty' },
                        { value: 'lt', label: this.i18n.operatorLt || 'less than' },
                        { value: 'lte', label: this.i18n.operatorLte || 'less than or equal' },
                        { value: 'gt', label: this.i18n.operatorGt || 'greater than' },
                        { value: 'gte', label: this.i18n.operatorGte || 'greater than or equal' },
                ];

                var field = this.fieldMap[ fieldName ];
                if ( field && field.type && 'date' === field.type.toLowerCase() ) {
                        return options.filter( function ( option ) {
                                return [ 'equals', 'not_equals', 'empty', 'not_empty', 'lt', 'lte', 'gt', 'gte' ].indexOf( option.value ) !== -1;
                        } );
                }

                return options;
        };

        ConditionManager.prototype.renderConditionRow = function ( group, condition ) {
                var self = this;
                var row = document.createElement( 'div' );
                row.className = 'fbm-registration-editor__conditions-row';
                row.setAttribute( 'data-fbm-condition', condition.id );

                var fieldWrap = document.createElement( 'div' );
                fieldWrap.className = 'fbm-registration-editor__conditions-field';
                var fieldLabel = document.createElement( 'label' );
                fieldLabel.textContent = this.i18n.conditionFieldLabel || 'Field';
                fieldWrap.appendChild( fieldLabel );

                var fieldSelect = document.createElement( 'select' );
                this.fields.forEach( function ( field ) {
                        var opt = document.createElement( 'option' );
                        opt.value = field.name;
                        opt.textContent = field.label || field.name;
                        if ( field.name === condition.field ) {
                                opt.selected = true;
                        }
                        fieldSelect.appendChild( opt );
                } );

                fieldSelect.addEventListener( 'change', function () {
                        condition.field = fieldSelect.value;
                        if ( ! self.fieldMap[ condition.field ] ) {
                                condition.field = '';
                        }
                        self.render();
                        self.updateStorage();
                        self.onChange();
                } );

                fieldWrap.appendChild( fieldSelect );
                row.appendChild( fieldWrap );

                var operatorWrap = document.createElement( 'div' );
                operatorWrap.className = 'fbm-registration-editor__conditions-operator';
                var operatorLabel = document.createElement( 'label' );
                operatorLabel.textContent = this.i18n.conditionOperatorLabel || 'Operator';
                operatorWrap.appendChild( operatorLabel );

                var operatorSelect = document.createElement( 'select' );
                var operatorOptions = this.operatorOptions( condition.field );
                for ( var i = 0; i < operatorOptions.length; i += 1 ) {
                        var option = operatorOptions[ i ];
                        var opt = document.createElement( 'option' );
                        opt.value = option.value;
                        opt.textContent = option.label;
                        if ( option.value === condition.operator ) {
                                opt.selected = true;
                        }
                        operatorSelect.appendChild( opt );
                }

                operatorSelect.addEventListener( 'change', function () {
                        condition.operator = operatorSelect.value;
                        if ( 'empty' === condition.operator || 'not_empty' === condition.operator ) {
                                condition.value = '';
                        }
                        self.render();
                        self.updateStorage();
                        self.onChange();
                } );

                operatorWrap.appendChild( operatorSelect );
                row.appendChild( operatorWrap );

                var valueWrap = document.createElement( 'div' );
                valueWrap.className = 'fbm-registration-editor__conditions-value';
                var valueLabel = document.createElement( 'label' );
                valueLabel.textContent = this.i18n.conditionValueLabel || 'Value';
                valueWrap.appendChild( valueLabel );

                var fieldType = ( this.fieldMap[ condition.field ] && this.fieldMap[ condition.field ].type ) ? this.fieldMap[ condition.field ].type.toLowerCase() : 'text';
                var valueInput = document.createElement( 'input' );
                valueInput.type = ( 'number' === fieldType ) ? 'number' : ( 'date' === fieldType ? 'date' : 'text' );
                valueInput.value = condition.value || '';
                valueInput.placeholder = this.i18n.conditionValuePlaceholder || 'Enter a value';

                if ( 'empty' === condition.operator || 'not_empty' === condition.operator ) {
                        valueWrap.setAttribute( 'data-fbm-value-hidden', '1' );
                        valueInput.value = '';
                        valueInput.setAttribute( 'disabled', 'disabled' );
                        valueWrap.style.display = 'none';
                }

                valueInput.addEventListener( 'input', function () {
                        condition.value = valueInput.value;
                        self.updateStorage();
                        self.onChange();
                } );

                valueWrap.appendChild( valueInput );
                row.appendChild( valueWrap );

                var remove = document.createElement( 'button' );
                remove.type = 'button';
                remove.className = 'button button-link-delete fbm-registration-editor__conditions-remove';
                remove.textContent = this.i18n.removeCondition || 'Remove';
                if ( group.conditions.length <= 1 ) {
                        remove.setAttribute( 'disabled', 'disabled' );
                }
                remove.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        self.removeCondition( group, condition.id );
                } );
                row.appendChild( remove );

                return row;
        };

        ConditionManager.prototype.renderActionRow = function ( group, action ) {
                var self = this;
                var row = document.createElement( 'div' );
                row.className = 'fbm-registration-editor__conditions-action';
                row.setAttribute( 'data-fbm-action', action.id );

                var typeWrap = document.createElement( 'div' );
                typeWrap.className = 'fbm-registration-editor__conditions-action-type';
                var typeLabel = document.createElement( 'label' );
                typeLabel.textContent = this.i18n.actionTypeLabel || 'Action';
                typeWrap.appendChild( typeLabel );

                var typeSelect = document.createElement( 'select' );
                var types = [
                        { value: 'show', label: this.i18n.actionShow || 'Show' },
                        { value: 'hide', label: this.i18n.actionHide || 'Hide' },
                        { value: 'require', label: this.i18n.actionRequire || 'Require' },
                        { value: 'optional', label: this.i18n.actionOptional || 'Optional' },
                ];

                types.forEach( function ( option ) {
                        var opt = document.createElement( 'option' );
                        opt.value = option.value;
                        opt.textContent = option.label;
                        if ( option.value === action.type ) {
                                opt.selected = true;
                        }
                        typeSelect.appendChild( opt );
                } );

                typeSelect.addEventListener( 'change', function () {
                        action.type = typeSelect.value;
                        self.updateStorage();
                        self.onChange();
                } );

                typeWrap.appendChild( typeSelect );
                row.appendChild( typeWrap );

                var targetWrap = document.createElement( 'div' );
                targetWrap.className = 'fbm-registration-editor__conditions-action-target';
                var targetLabel = document.createElement( 'label' );
                targetLabel.textContent = this.i18n.actionTargetLabel || 'Field';
                targetWrap.appendChild( targetLabel );

                var targetSelect = document.createElement( 'select' );
                this.fields.forEach( function ( field ) {
                        var opt = document.createElement( 'option' );
                        opt.value = field.name;
                        opt.textContent = field.label || field.name;
                        if ( field.name === action.target ) {
                                opt.selected = true;
                        }
                        targetSelect.appendChild( opt );
                } );

                targetSelect.addEventListener( 'change', function () {
                        action.target = targetSelect.value;
                        if ( ! self.fieldMap[ action.target ] ) {
                                action.target = '';
                        }
                        self.updateStorage();
                        self.onChange();
                } );

                targetWrap.appendChild( targetSelect );
                row.appendChild( targetWrap );

                var remove = document.createElement( 'button' );
                remove.type = 'button';
                remove.className = 'button button-link-delete fbm-registration-editor__conditions-remove';
                remove.textContent = this.i18n.removeAction || 'Remove';
                if ( group.actions.length <= 1 ) {
                        remove.setAttribute( 'disabled', 'disabled' );
                }
                remove.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        self.removeAction( group, action.id );
                } );
                row.appendChild( remove );

                return row;
        };

        ConditionManager.prototype.addGroup = function () {
                var defaultField = this.fields.length > 0 ? this.fields[ 0 ].name : '';
                var group = {
                        id: uniqueId( 'group' ),
                        operator: 'and',
                        conditions: [
                                {
                                        id: uniqueId( 'condition' ),
                                        field: defaultField,
                                        operator: 'equals',
                                        value: '',
                                },
                        ],
                        actions: [
                                {
                                        id: uniqueId( 'action' ),
                                        type: 'show',
                                        target: defaultField,
                                },
                        ],
                };

                this.groups.push( group );
                this.render();
                this.updateStorage();
                this.onChange();
        };
        ConditionManager.prototype.removeGroup = function ( groupId ) {
                this.groups = this.groups.filter( function ( group ) {
                        return group.id !== groupId;
                } );

                this.render();
                this.updateStorage();
                this.onChange();
        };

        ConditionManager.prototype.addCondition = function ( group ) {
                var defaultField = this.fields.length > 0 ? this.fields[ 0 ].name : '';
                group.conditions.push( {
                        id: uniqueId( 'condition' ),
                        field: defaultField,
                        operator: 'equals',
                        value: '',
                } );
                this.render();
                this.updateStorage();
                this.onChange();
        };

        ConditionManager.prototype.removeCondition = function ( group, conditionId ) {
                if ( group.conditions.length <= 1 ) {
                        return;
                }

                group.conditions = group.conditions.filter( function ( condition ) {
                        return condition.id !== conditionId;
                } );

                this.render();
                this.updateStorage();
                this.onChange();
        };

        ConditionManager.prototype.addAction = function ( group ) {
                var defaultField = this.fields.length > 0 ? this.fields[ 0 ].name : '';
                group.actions.push( {
                        id: uniqueId( 'action' ),
                        type: 'show',
                        target: defaultField,
                } );
                this.render();
                this.updateStorage();
                this.onChange();
        };

        ConditionManager.prototype.removeAction = function ( group, actionId ) {
                if ( group.actions.length <= 1 ) {
                        return;
                }

                group.actions = group.actions.filter( function ( action ) {
                        return action.id !== actionId;
                } );

                this.render();
                this.updateStorage();
                this.onChange();
        };

        ConditionManager.prototype.updateStorage = function () {
                if ( ! this.storage ) {
                        return;
                }

                var data = this.getCanonical();

                try {
                        this.storage.value = JSON.stringify( data );
                } catch ( err ) {
                        this.storage.value = '[]';
                }
        };

        ConditionManager.prototype.getCanonical = function () {
                return this.groups.map( function ( group ) {
                        return {
                                operator: group.operator,
                                conditions: group.conditions.map( function ( condition ) {
                                        return {
                                                field: condition.field,
                                                operator: condition.operator,
                                                value: condition.value,
                                        };
                                } ),
                                actions: group.actions.map( function ( action ) {
                                        return {
                                                type: action.type,
                                                target: action.target,
                                        };
                                } ),
                        };
                } );
        };

        ConditionManager.prototype.isEnabled = function () {
                return this.enabled;
        };

        ConditionManager.prototype.setEnabled = function ( enabled ) {
                this.enabled = !! enabled;
                if ( this.enabledToggle ) {
                        this.enabledToggle.checked = this.enabled;
                }
                this.onChange();
        };

        ConditionManager.prototype.replace = function ( groups, enabled ) {
                this.groups = this.normalizeGroups( groups );
                this.setEnabled( enabled );
                this.render();
                this.updateStorage();
        };

        ConditionManager.prototype.getFieldLabel = function ( name ) {
                return this.fieldMap[ name ] ? ( this.fieldMap[ name ].label || name ) : name;
        };

        ConditionManager.prototype.getFieldType = function ( name ) {
                return this.fieldMap[ name ] && this.fieldMap[ name ].type ? this.fieldMap[ name ].type.toLowerCase() : 'text';
        };

        ConditionManager.prototype.validate = function () {
                var messages = [];
                if ( ! Array.isArray( this.groups ) || this.groups.length === 0 ) {
                        if ( this.status ) {
                                this.status.textContent = this.i18n.validationEmpty || '';
                        }

                        if ( this.report ) {
                                this.report.setAttribute( 'hidden', 'hidden' );
                        }

                        return messages;
                }

                for ( var i = 0; i < this.groups.length; i += 1 ) {
                        var group = this.groups[ i ];
                        var label = format( this.i18n.groupLabel || 'Group %s', i + 1 );
                        var fieldUsage = {};

                        for ( var c = 0; c < group.conditions.length; c += 1 ) {
                                var condition = group.conditions[ c ];
                                var field = condition.field;
                                if ( ! this.fieldMap[ field ] ) {
                                        messages.push( format( this.i18n.validationMissingField || '%1$s references a missing field (%2$s).', label, field || '?' ) );
                                        continue;
                                }

                                if ( ! fieldUsage[ field ] ) {
                                        fieldUsage[ field ] = { equals: {}, empty: false, notEmpty: false };
                                }

                                if ( 'equals' === condition.operator ) {
                                        fieldUsage[ field ].equals[ condition.value ] = true;
                                }

                                if ( 'empty' === condition.operator ) {
                                        fieldUsage[ field ].empty = true;
                                }

                                if ( 'not_empty' === condition.operator ) {
                                        fieldUsage[ field ].notEmpty = true;
                                }
                        }

                        var actionTargets = {};
                        for ( var a = 0; a < group.actions.length; a += 1 ) {
                                var action = group.actions[ a ];
                                if ( ! this.fieldMap[ action.target ] ) {
                                        messages.push( format( this.i18n.validationMissingTarget || '%1$s targets an unknown field (%2$s).', label, action.target || '?' ) );
                                }

                                if ( this.fieldMap[ action.target ] ) {
                                        actionTargets[ action.target ] = true;
                                }
                        }

                        Object.keys( actionTargets ).forEach( function ( target ) {
                                if ( fieldUsage[ target ] ) {
                                        messages.push( format( this.i18n.validationCircular || '%1$s both listens to and targets %2$s.', label, this.getFieldLabel( target ) ) );
                                }
                        }.bind( this ) );

                        if ( 'and' === group.operator ) {
                                Object.keys( fieldUsage ).forEach( function ( fieldName ) {
                                        var usage = fieldUsage[ fieldName ];
                                        var equalsValues = Object.keys( usage.equals );
                                        if ( equalsValues.length > 1 ) {
                                                messages.push( format( this.i18n.validationUnreachable || '%1$s contains conditions that cannot all be true for %2$s.', label, this.getFieldLabel( fieldName ) ) );
                                        }

                                        if ( usage.empty && usage.notEmpty ) {
                                                messages.push( format( this.i18n.validationUnreachable || '%1$s contains conditions that cannot all be true for %2$s.', label, this.getFieldLabel( fieldName ) ) );
                                        }
                                }.bind( this ) );
                        }
                }

                if ( this.status ) {
                        if ( messages.length === 0 ) {
                                this.status.textContent = this.i18n.validationPassed || 'No issues found.';
                        } else {
                                this.status.textContent = format( this.i18n.validationHasIssues || '%s issues found.', messages.length );
                        }
                }

                if ( this.report && this.reportList ) {
                        if ( messages.length === 0 ) {
                                this.report.setAttribute( 'hidden', 'hidden' );
                                this.reportList.innerHTML = '';
                        } else {
                                this.reportList.innerHTML = '';
                                for ( var m = 0; m < messages.length; m += 1 ) {
                                        var item = document.createElement( 'li' );
                                        item.textContent = messages[ m ];
                                        this.reportList.appendChild( item );
                                }
                                this.report.removeAttribute( 'hidden' );
                        }
                }

                return messages;
        };

        var conditionsRoot = document.querySelector( '[data-fbm-conditions]' );
        var conditionManager = null;
        var autosaveManager = null;
        if ( conditionsRoot ) {
                var initialFields = Array.isArray( settings.fields ) ? settings.fields : [];
                conditionManager = new ConditionManager( conditionsRoot, initialFields, {
                        onChange: function () {
                                notifyDirty();
                        },
                        i18n: i18n,
                } );

                var initialGroups = settings.conditions && Array.isArray( settings.conditions.groups ) ? settings.conditions.groups : [];
                var enabled = settings.conditions && settings.conditions.enabled;
                conditionManager.setInitialState( initialGroups, enabled );
        }

        var AutosaveManager = function ( config ) {
                this.endpoint = config.endpoint || '';
                this.restoreBase = config.restoreBase || '';
                this.revisionsEndpoint = config.revisionsEndpoint || '';
                this.nonce = config.nonce || '';
                this.interval = config.interval || 30000;
                this.statusEl = document.querySelector( '[data-fbm-autosave-status]' );
                this.revisionSelect = document.querySelector( '[data-fbm-revision-select]' );
                this.restoreButton = document.querySelector( '[data-fbm-revision-restore]' );
                this.payload = config.payload || null;
                this.revisions = Array.isArray( config.revisions ) ? config.revisions : [];
                this.dirty = false;
                this.timer = null;
                this.isSaving = false;
                this.keepaliveSupported = true;
                this.onApply = config.onApply || function () {};
                this.i18n = config.i18n || {};
                this.init();
        };

        AutosaveManager.prototype.init = function () {
                var self = this;

                this.populateRevisions( this.revisions, this.payload );

                if ( this.restoreButton ) {
                        this.restoreButton.addEventListener( 'click', function ( event ) {
                                event.preventDefault();
                                self.restoreSelection();
                        } );
                }

                if ( this.revisionSelect ) {
                        this.revisionSelect.addEventListener( 'change', function () {
                                if ( ! self.restoreButton ) {
                                        return;
                                }

                                if ( self.revisionSelect.value ) {
                                        self.restoreButton.removeAttribute( 'disabled' );
                                } else {
                                        self.restoreButton.setAttribute( 'disabled', 'disabled' );
                                }
                        } );
                }
        };

        AutosaveManager.prototype.formatRevisionLabel = function ( revision ) {
                var timestamp = revision.timestamp ? formatTimestamp( revision.timestamp ) : '';
                var user = revision.user_name || '';

                if ( timestamp && user ) {
                        return user + ' — ' + timestamp;
                }

                if ( timestamp ) {
                        return timestamp;
                }

                return user || this.i18n.revisionUnknown || 'Untitled revision';
        };

        AutosaveManager.prototype.populateRevisions = function ( revisions, autosavePayload ) {
                this.revisions = Array.isArray( revisions ) ? revisions : [];
                this.payload = autosavePayload || null;

                if ( ! this.revisionSelect ) {
                        return;
                }

                this.revisionSelect.innerHTML = '';
                var placeholder = document.createElement( 'option' );
                placeholder.value = '';
                placeholder.textContent = this.i18n.revisionPlaceholder || 'Restore revision…';
                this.revisionSelect.appendChild( placeholder );

                if ( this.payload && this.payload.timestamp ) {
                                var autoOption = document.createElement( 'option' );
                                autoOption.value = '__autosave__';
                                autoOption.textContent = format( this.i18n.revisionAutosave || 'Autosave — %s', formatTimestamp( this.payload.timestamp ) );
                                this.revisionSelect.appendChild( autoOption );
                }

                for ( var i = 0; i < this.revisions.length; i += 1 ) {
                        var revision = this.revisions[ i ];
                        if ( ! revision || ! revision.id ) {
                                continue;
                        }
                        var option = document.createElement( 'option' );
                        option.value = revision.id;
                        option.textContent = this.formatRevisionLabel( revision );
                        this.revisionSelect.appendChild( option );
                }

                if ( this.restoreButton ) {
                        this.restoreButton.setAttribute( 'disabled', 'disabled' );
                }
        };

        AutosaveManager.prototype.setStatus = function ( message ) {
                if ( this.statusEl ) {
                        this.statusEl.textContent = message || '';
                }
        };

        AutosaveManager.prototype.markDirty = function () {
                this.dirty = true;
                if ( this.timer ) {
                        window.clearTimeout( this.timer );
                }

                var self = this;
                this.timer = window.setTimeout( function () {
                        self.save();
                }, this.interval );
        };

        AutosaveManager.prototype.collectPayload = function () {
                return {
                        template: getTemplate(),
                        settings: collectSettings(),
                };
        };

        AutosaveManager.prototype.save = function ( options ) {
                if ( ! this.endpoint || this.isSaving || ! this.dirty ) {
                        return Promise.resolve();
                }

                var payload = this.collectPayload();
                this.isSaving = true;
                this.dirty = false;
                this.setStatus( this.i18n.autosaveSaving || 'Saving…' );

                var fetchOptions = {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                                'Content-Type': 'application/json',
                        },
                        body: JSON.stringify( payload ),
                };

                if ( this.nonce ) {
                        fetchOptions.headers['X-WP-Nonce'] = this.nonce;
                }

                if ( options && options.keepalive ) {
                        fetchOptions.keepalive = true;
                }

                var self = this;

                return window.fetch( this.endpoint, fetchOptions ).then( function ( response ) {
                        if ( ! response.ok ) {
                                throw new Error( 'Request failed' );
                        }

                        return response.json();
                } ).then( function ( body ) {
                        if ( body && body.revisions ) {
                                self.populateRevisions( body.revisions, body.payload || null );
                        }

                        self.setStatus( self.i18n.autosaveSaved || 'Saved.' );
                } ).catch( function () {
                        self.dirty = true;
                        self.setStatus( self.i18n.autosaveError || 'Autosave failed.' );
                } ).finally( function () {
                        self.isSaving = false;
                } );
        };

        AutosaveManager.prototype.flush = function () {
                return this.save( { keepalive: true } );
        };

        AutosaveManager.prototype.restoreSelection = function () {
                if ( ! this.revisionSelect ) {
                        return;
                }

                var value = this.revisionSelect.value;
                if ( ! value ) {
                        return;
                }

                var self = this;

                if ( '__autosave__' === value ) {
                        if ( this.payload ) {
                                this.applyPayload( this.payload );
                                this.setStatus( this.i18n.autosaveRestored || 'Autosave restored.' );
                        }

                        return;
                }

                if ( ! this.restoreBase ) {
                        return;
                }

                this.setStatus( this.i18n.autosaveRestoring || 'Restoring…' );

                var requestOptions = {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {},
                };

                if ( this.nonce ) {
                        requestOptions.headers['X-WP-Nonce'] = this.nonce;
                }

                window.fetch( this.restoreBase + encodeURIComponent( value ), requestOptions ).then( function ( response ) {
                        if ( ! response.ok ) {
                                throw new Error( 'Request failed' );
                        }

                        return response.json();
                } ).then( function ( body ) {
                        self.applyPayload( body );
                        self.setStatus( self.i18n.autosaveRestored || 'Revision restored.' );
                } ).catch( function () {
                        self.setStatus( self.i18n.autosaveError || 'Autosave failed.' );
                } );
        };

        AutosaveManager.prototype.applyPayload = function ( payload ) {
                if ( ! payload ) {
                        return;
                }

                this.payload = payload;

                if ( payload.template ) {
                        setTemplateValue( payload.template );
                }

                if ( payload.settings ) {
                        applySettingsToForm( payload.settings );
                }

                this.markDirty();
        };
        var settingsFieldName = settings.settingsField || '';

        var collectSettings = function () {
                var data = {
                        uploads: {},
                        honeypot: '0',
                        editor: {},
                        messages: {},
                        conditions: {},
                };

                if ( ! settingsFieldName ) {
                        return data;
                }

                var maxSizeInput = document.querySelector( '[name="' + settingsFieldName + '[uploads][max_size_mb]"]' );
                if ( maxSizeInput ) {
                        data.uploads.max_size_mb = maxSizeInput.value || '';
                }

                var mimeInput = document.querySelector( '[name="' + settingsFieldName + '[uploads][allowed_mime_types]"]' );
                if ( mimeInput ) {
                        data.uploads.allowed_mime_types = mimeInput.value || '';
                }

                var honeypotInput = document.querySelector( '[name="' + settingsFieldName + '[honeypot]"]' );
                if ( honeypotInput ) {
                        data.honeypot = honeypotInput.checked ? '1' : '0';
                }

                var themeSelect = document.querySelector( '[name="' + settingsFieldName + '[editor][theme]"]' );
                if ( themeSelect ) {
                        data.editor.theme = themeSelect.value || 'light';
                }

                var successAuto = document.querySelector( '[name="' + settingsFieldName + '[messages][success_auto]"]' );
                if ( successAuto ) {
                        data.messages.success_auto = successAuto.value || '';
                }

                var successPending = document.querySelector( '[name="' + settingsFieldName + '[messages][success_pending]"]' );
                if ( successPending ) {
                        data.messages.success_pending = successPending.value || '';
                }

                data.conditions.enabled = conditionManager && conditionManager.isEnabled() ? '1' : '0';
                data.conditions.groups = conditionManager ? conditionManager.getCanonical() : [];

                return data;
        };

        var applySettingsToForm = function ( payload ) {
                if ( ! payload || ! settingsFieldName ) {
                        return;
                }

                var uploads = payload.uploads || {};
                var maxSizeInput = document.querySelector( '[name="' + settingsFieldName + '[uploads][max_size_mb]"]' );
                if ( maxSizeInput ) {
                        var maxSizeMb = uploads.max_size_mb || '';
                        if ( ! maxSizeMb && uploads.max_size ) {
                                var bytes = Number( uploads.max_size );
                                if ( ! Number.isNaN( bytes ) && bytes > 0 ) {
                                        maxSizeMb = Math.max( 1, Math.round( bytes / 1048576 ) );
                                }
                        }
                        maxSizeInput.value = maxSizeMb;
                }

                var mimeInput = document.querySelector( '[name="' + settingsFieldName + '[uploads][allowed_mime_types]"]' );
                if ( mimeInput ) {
                        if ( Array.isArray( uploads.allowed_mime_types ) ) {
                                mimeInput.value = uploads.allowed_mime_types.join( ', ' );
                        } else if ( uploads.allowed_mime_types ) {
                                mimeInput.value = uploads.allowed_mime_types;
                        }
                }

                var honeypotInput = document.querySelector( '[name="' + settingsFieldName + '[honeypot]"]' );
                if ( honeypotInput ) {
                        honeypotInput.checked = toBool( payload.honeypot );
                }

                var themeSelect = document.querySelector( '[name="' + settingsFieldName + '[editor][theme]"]' );
                if ( themeSelect && payload.editor ) {
                        themeSelect.value = payload.editor.theme || 'light';
                }

                var successAuto = document.querySelector( '[name="' + settingsFieldName + '[messages][success_auto]"]' );
                if ( successAuto && payload.messages ) {
                        successAuto.value = payload.messages.success_auto || '';
                }

                var successPending = document.querySelector( '[name="' + settingsFieldName + '[messages][success_pending]"]' );
                if ( successPending && payload.messages ) {
                        successPending.value = payload.messages.success_pending || '';
                }

                if ( conditionManager && payload.conditions ) {
                        var groups = Array.isArray( payload.conditions.groups ) ? payload.conditions.groups : [];
                        conditionManager.replace( groups, payload.conditions.enabled );
                }
        };

        var autosaveConfig = settings.autosave || {};
        if ( autosaveConfig.endpoint ) {
                autosaveManager = new AutosaveManager( {
                        endpoint: autosaveConfig.endpoint || '',
                        restoreBase: autosaveConfig.restoreBase || '',
                        revisionsEndpoint: autosaveConfig.revisionsEndpoint || '',
                        nonce: settings.restNonce || settings.previewNonce || '',
                        interval: autosaveConfig.interval || 30000,
                        payload: autosaveConfig.payload || null,
                        revisions: autosaveConfig.revisions || [],
                        i18n: i18n,
                        onApply: function () {
                                notifyDirty();
                        },
                } );

                notifyDirty = function () {
                        if ( autosaveManager ) {
                                autosaveManager.markDirty();
                        }
                };
        }
        if ( codeEditor && codeEditor.on ) {
                codeEditor.on( 'change', function () {
                        notifyDirty();
                } );
        } else if ( textarea ) {
                textarea.addEventListener( 'input', notifyDirty );
        }

        if ( settingsFieldName ) {
                var registerListener = function ( selector, eventName ) {
                        var element = document.querySelector( selector );
                        if ( ! element ) {
                                return;
                        }

                        element.addEventListener( eventName || 'input', notifyDirty );
                };

                registerListener( '[name="' + settingsFieldName + '[uploads][max_size_mb]"]', 'input' );
                registerListener( '[name="' + settingsFieldName + '[uploads][allowed_mime_types]"]', 'input' );
                registerListener( '[name="' + settingsFieldName + '[editor][theme]"]', 'change' );
                registerListener( '[name="' + settingsFieldName + '[messages][success_auto]"]', 'input' );
                registerListener( '[name="' + settingsFieldName + '[messages][success_pending]"]', 'input' );

                var honeypotToggle = document.querySelector( '[name="' + settingsFieldName + '[honeypot]"]' );
                if ( honeypotToggle ) {
                        honeypotToggle.addEventListener( 'change', notifyDirty );
                }
        }

        $( document ).on( 'click', '.fbm-registration-editor__snippet', function ( event ) {
                event.preventDefault();

                var snippet = $( this ).data( 'fbm-snippet' );
                if ( ! snippet ) {
                        return;
                }

                if ( codeEditor ) {
                        codeEditor.replaceSelection( snippet + '\n' );
                        codeEditor.focus();
                        notifyDirty();

                        return;
                }

                if ( textarea ) {
                        var start = textarea.selectionStart || 0;
                        var end = textarea.selectionEnd || 0;
                        var value = textarea.value || '';
                        textarea.value = value.slice( 0, start ) + snippet + '\n' + value.slice( end );
                        textarea.focus();
                        notifyDirty();
                }
        } );
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

        var collectPreviewState = function ( root ) {
                var map = {};
                var defaults = {};
                var values = {};

                if ( ! root ) {
                        return { values: values, defaults: defaults };
                }

                var containers = root.querySelectorAll( '[data-fbm-field]' );
                Array.prototype.forEach.call( containers, function ( container ) {
                        var name = container.getAttribute( 'data-fbm-field' );
                        if ( ! name ) {
                                return;
                        }

                        var type = ( container.getAttribute( 'data-fbm-field-type' ) || 'text' ).toLowerCase();
                        var required = container.getAttribute( 'data-fbm-field-required' ) === '1';
                        var controls = Array.prototype.slice.call( container.querySelectorAll( 'input, select, textarea' ) );

                        map[ name ] = {
                                container: container,
                                controls: controls,
                                type: type,
                                required: required,
                        };

                        defaults[ name ] = {
                                required: required,
                        };
                } );

                Object.keys( map ).forEach( function ( name ) {
                        values[ name ] = readFieldValue( map[ name ] );
                } );

                return {
                        values: values,
                        defaults: defaults,
                        entries: map,
                };
        };

        var modal = document.querySelector( '[data-fbm-preview-modal]' );
        var dialog = modal ? modal.querySelector( '[data-fbm-preview-dialog]' ) : null;
        var content = modal ? modal.querySelector( '[data-fbm-preview-content]' ) : null;
        var warningsWrapper = modal ? modal.querySelector( '[data-fbm-preview-warnings]' ) : null;
        var warningsList = warningsWrapper ? warningsWrapper.querySelector( 'ul' ) : null;
        var note = modal ? modal.querySelector( '[data-fbm-preview-note]' ) : null;
        var debugToggle = modal ? modal.querySelector( '[data-fbm-preview-debug-toggle]' ) : null;
        var debugPanel = modal ? modal.querySelector( '[data-fbm-preview-debug]' ) : null;
        var debugList = debugPanel ? debugPanel.querySelector( '[data-fbm-preview-debug-groups]' ) : null;
        var debugEmpty = debugPanel ? debugPanel.querySelector( '[data-fbm-preview-debug-empty]' ) : null;
        var lastFocused = null;
        var keydownHandler = null;

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

        var focusDialog = function () {
                if ( ! dialog ) {
                        return;
                }

                var focusable = dialog.querySelectorAll( 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])' );
                if ( focusable.length > 0 ) {
                        focusable[ 0 ].focus();
                } else {
                        dialog.focus();
                }
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

                if ( debugPanel ) {
                        debugPanel.setAttribute( 'hidden', 'hidden' );
                }

                if ( debugToggle ) {
                        debugToggle.setAttribute( 'aria-expanded', 'false' );
                        debugToggle.textContent = i18n.debugToggleShow || 'Show rule debugger';
                }

                resetWarnings();

                if ( lastFocused && typeof lastFocused.focus === 'function' ) {
                        lastFocused.focus();
                }

                lastFocused = null;
        };
        var renderDebugPanel = function () {
                if ( ! debugPanel || ! debugList ) {
                        return;
                }

                if ( ! conditionManager || ! Conditions || ! Conditions.normalizeGroups ) {
                        debugPanel.setAttribute( 'hidden', 'hidden' );
                        return;
                }

                var groups = conditionManager.getCanonical();
                var normalized = Conditions.normalizeGroups( groups );

                if ( normalized.length === 0 ) {
                        debugList.innerHTML = '';
                        if ( debugEmpty ) {
                                debugEmpty.removeAttribute( 'hidden' );
                        }
                        return;
                }

                if ( debugEmpty ) {
                        debugEmpty.setAttribute( 'hidden', 'hidden' );
                }

                var stateData = collectPreviewState( content );
                var values = stateData.values;
                var defaults = stateData.defaults;

                var evaluationState = Conditions.evaluate ? Conditions.evaluate( normalized, values, defaults ) : {};

                debugList.innerHTML = '';

                normalized.forEach( function ( group, index ) {
                        var item = document.createElement( 'li' );
                        var heading = document.createElement( 'div' );
                        heading.className = 'fbm-registration-editor__preview-debug-heading';
                        var matched = Conditions.groupMatches ? Conditions.groupMatches( group, values ) : false;
                        heading.textContent = format( i18n.debugGroupTitle || 'Group %1$s (%2$s)', index + 1, 'or' === group.operator ? ( i18n.debugOperatorOr || 'Any match' ) : ( i18n.debugOperatorAnd || 'All match' ) );

                        var status = document.createElement( 'span' );
                        status.className = matched ? 'fbm-registration-editor__preview-debug-status--match' : 'fbm-registration-editor__preview-debug-status--no-match';
                        status.textContent = matched ? ( i18n.debugGroupMatched || 'Matched' ) : ( i18n.debugGroupNotMatched || 'Not matched' );
                        heading.appendChild( status );
                        item.appendChild( heading );

                        var conditionsList = document.createElement( 'ul' );
                        conditionsList.className = 'fbm-registration-editor__preview-debug-conditions';

                        group.conditions.forEach( function ( condition ) {
                                var conditionItem = document.createElement( 'li' );
                                var conditionMatched = Conditions.conditionMatches ? Conditions.conditionMatches( condition, values ) : false;
                                var operatorLabel = i18n[ 'debugOp_' + condition.operator ] || condition.operator;
                                conditionItem.textContent = format( i18n.debugConditionRow || '%1$s %2$s %3$s — %4$s', conditionManager.getFieldLabel( condition.field ), operatorLabel, condition.value || '', conditionMatched ? ( i18n.debugConditionMatched || 'matched' ) : ( i18n.debugConditionNotMatched || 'not matched' ) );
                                conditionsList.appendChild( conditionItem );
                        } );

                        item.appendChild( conditionsList );

                        if ( group.actions.length > 0 ) {
                                var actionsHeading = document.createElement( 'div' );
                                actionsHeading.textContent = i18n.debugActionsHeading || 'Actions';
                                item.appendChild( actionsHeading );

                                var actionsList = document.createElement( 'ul' );
                                actionsList.className = 'fbm-registration-editor__preview-debug-actions';

                                group.actions.forEach( function ( action ) {
                                        var actionItem = document.createElement( 'li' );
                                        var actionLabelKey = 'debugAction_' + action.type;
                                        var actionLabel = i18n[ actionLabelKey ] || action.type;
                                        var finalState = evaluationState[ action.target ] || {};
                                        var finalDescriptor = '';
                                        if ( 'show' === action.type || 'hide' === action.type ) {
                                                finalDescriptor = finalState.visible === false ? ( i18n.debugFinalHidden || 'final state: hidden' ) : ( i18n.debugFinalVisible || 'final state: visible' );
                                        } else if ( 'require' === action.type || 'optional' === action.type ) {
                                                finalDescriptor = finalState.required === false ? ( i18n.debugFinalOptional || 'final state: optional' ) : ( i18n.debugFinalRequired || 'final state: required' );
                                        }
                                        actionItem.textContent = format( i18n.debugActionRow || '%1$s — %2$s (%3$s)', actionLabel, conditionManager.getFieldLabel( action.target ), finalDescriptor );
                                        actionsList.appendChild( actionItem );
                                } );

                                item.appendChild( actionsList );
                        }

                        debugList.appendChild( item );
                } );
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

                if ( note && i18n.modalDescription ) {
                        note.textContent = i18n.modalDescription;
                }

                if ( nonce ) {
                        modal.setAttribute( 'data-fbm-preview-nonce', nonce );
                }

                if ( debugPanel ) {
                        debugPanel.setAttribute( 'hidden', 'hidden' );
                }

                if ( debugToggle ) {
                        debugToggle.setAttribute( 'aria-expanded', 'false' );
                        debugToggle.textContent = i18n.debugToggleShow || 'Show rule debugger';
                }

                modal.removeAttribute( 'hidden' );
                document.body.classList.add( 'fbm-registration-editor--modal-open' );

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

                        var focusable = Array.prototype.slice.call( dialog.querySelectorAll( 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])' ) );

                        if ( focusable.length === 0 ) {
                                event.preventDefault();
                                dialog.focus();
                                return;
                        }

                        var first = focusable[ 0 ];
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
                focusDialog();
        };
        if ( modal ) {
                $( document ).on( 'click', '[data-fbm-preview-close]', function ( event ) {
                        event.preventDefault();
                        closeModal();
                } );

                modal.addEventListener( 'click', function ( event ) {
                        if ( event.target === modal ) {
                                closeModal();
                        }
                } );
        }

        var previewInFlight = false;

        var loadPreview = function () {
                if ( previewInFlight ) {
                        return;
                }

                if ( ! settings.previewUrl || ! settings.previewNonce ) {
                        window.alert( i18n.previewError || 'Unable to load the preview. Please save first or try again.' );
                        return;
                }

                previewInFlight = true;
                var payload = {
                        template: getTemplate(),
                        settings: collectSettings(),
                };

                window.fetch( settings.previewUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': settings.previewNonce,
                        },
                        body: JSON.stringify( payload ),
                } ).then( function ( response ) {
                        if ( ! response.ok ) {
                                throw new Error( 'Request failed' );
                        }

                        return response.json();
                } ).then( function ( body ) {
                        if ( ! body || ! body.markup ) {
                                throw new Error( 'Invalid response' );
                        }

                        openModal( body.markup, Array.isArray( body.warnings ) ? body.warnings : [], body.nonce || '' );
                } ).catch( function () {
                        window.alert( i18n.previewError || 'Unable to load the preview. Please save first or try again.' );
                } ).finally( function () {
                        previewInFlight = false;
                        if ( debugToggle ) {
                                debugToggle.setAttribute( 'aria-expanded', 'false' );
                                debugToggle.textContent = i18n.debugToggleShow || 'Show rule debugger';
                        }
                } );
        };

        if ( debugToggle ) {
                debugToggle.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        if ( ! debugPanel ) {
                                return;
                        }

                        if ( debugPanel.hasAttribute( 'hidden' ) ) {
                                renderDebugPanel();
                                debugPanel.removeAttribute( 'hidden' );
                                debugToggle.setAttribute( 'aria-expanded', 'true' );
                                debugToggle.textContent = i18n.debugToggleHide || 'Hide rule debugger';
                        } else {
                                debugPanel.setAttribute( 'hidden', 'hidden' );
                                debugToggle.setAttribute( 'aria-expanded', 'false' );
                                debugToggle.textContent = i18n.debugToggleShow || 'Show rule debugger';
                        }
                } );
        }

        var previewButton = document.querySelector( '.fbm-registration-editor__preview' );
        if ( previewButton ) {
                previewButton.addEventListener( 'click', function ( event ) {
                        event.preventDefault();
                        loadPreview();
                } );
        }

        var focusToolbar = function () {
                var firstSnippet = document.querySelector( '.fbm-registration-editor__toolbar .fbm-registration-editor__snippet' );
                if ( firstSnippet ) {
                        firstSnippet.focus();
                        return;
                }

                if ( previewButton ) {
                        previewButton.focus();
                }
        };

        document.addEventListener( 'keydown', function ( event ) {
                if ( ! event.ctrlKey && ! event.metaKey ) {
                        return;
                }

                var key = event.key.toLowerCase();

                if ( 's' === key ) {
                        event.preventDefault();
                        ToolbarShortcuts.close();
                        if ( rootForm && rootForm.requestSubmit ) {
                                rootForm.requestSubmit();
                        } else if ( rootForm ) {
                                rootForm.submit();
                        }

                        return;
                }

                if ( 'enter' === key ) {
                        event.preventDefault();
                        loadPreview();
                        return;
                }

                if ( 'i' === key ) {
                        event.preventDefault();
                        focusToolbar();
                }
        } );

        window.addEventListener( 'beforeunload', function () {
                        if ( autosaveManager ) {
                                autosaveManager.flush();
                        }
        } );

        if ( debugPanel && modal ) {
                modal.addEventListener( 'transitionend', function () {
                        if ( isModalOpen() && debugToggle && debugToggle.getAttribute( 'aria-expanded' ) === 'true' ) {
                                renderDebugPanel();
                        }
                } );
        }

}( jQuery ));
