/**
 * This file defines sidebar view for order
 * 
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.sidebar.Inventory
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.sidebar.Orders', {
    extend: 'Ext.panel.Panel',
    collapsed: true,
    collapsible: true,    
    region: 'east',
    width: 300,
    alias: 'widget.asignyellowcube-sidebar-order-detail',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        textSave:               '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/save}Save{/s}',
        textTitle:              '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/title}Yellowcube Response{/s}',
        textNothingSelected:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/nothing}No order selected{/s}',
        textInitialWab:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/initial}Initial WAB Response{/s}',        
        textStatus:             '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/status}Get Order Status{/s}',
        textManual:             '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/mansend}Manually send Order{/s}',        
        textCustomer:           '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/customer}Yellowcube Customer Order Status (WAB){/s}',
        textReply:              '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/reply}Get Customer Order Reply{/s}',
        textWarResponse:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/details/orders/response}Yellowcube WAR Response{/s}'
    },

    /**
     * Init the main detail component, add components
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.textTitle;
        me.registerEvents();        
        me.items = [ me.createContainer() ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the send Manual button
             * action column
             *
             * @event sendOrder
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'sendOrder',

            /**
             * Event will be fired when the user clicks WAB button
             * action column
             *
             * @event getPreStatus
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'getPreStatus',

            /**
             * Event will be fired when the user WAR status button
             * action column
             *
             * @event getWarStatus
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'getWarStatus'
        );

        return true;
    },

    /**
     * Creates the main container, sets layout and adds the components needed
     * @return Ext.container.Container
     */
    createContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            border: false,
            padding: 5,
            maxHeight: 498,
            autoScroll: true,
            ordStore: me.ordStore,
            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            items: [               
                me.createInitialFieldset(),
                me.createWabFieldset(),
                me.createWarFieldset(),
                me.createOrderField()
            ]
        });
    },
    
    /**
     * Creates and returns hidden text field
     * @return Ext.form.Label
     */
    createOrderField: function()  {
        var me = this;

        me.hiddenField = Ext.create('Ext.form.field.Text', {
            id: 'textoId',
            name: 'orderId',
            hidden: true,
            border:0
        });

        return me.hiddenField;
    },

    /**
     * Creates an form fieldset for showing first response
     *
     * @return Ext.form.FieldSet
     */
    createInitialFieldset: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            id: 'fldInit',
            flex: 1,
            padding: 10,
            collapsible: true,
            title: me.snippets.textInitialWab,
            autoScroll: true,
            layout: 'anchor',
            items: [
                me.createInitResponseText(),
                me.createGetManualButton(),
                me.createGetWabButton()
            ]
        });
    },

    /**
     * Creates and returns response text on the sidebar
     * @return Ext.form.Label
     */
    createInitResponseText: function()  {
        var me = this;

        me.respLabel = Ext.create('Ext.form.Label', {
            id: 'resplabel',
            text: me.snippets.textNothingSelected,
            padding: '0 0 5 0'            
        });

        return me.respLabel;
    },

     /**
     * Creates and returns a button used to get status for the YC response
     * @return Ext.button.Button
     */
    createGetManualButton: function() {
        var me = this;

        me.getStatusButton = Ext.create('Ext.button.Button', {
            text: me.snippets.textManual,
            id: 'btnManual',
            cls: 'primary',
            hidden: true,            
            handler: function () {
                me.fireEvent('sendOrder');
            }
        });

        return me.getStatusButton;
    },

    /**
     * Creates and returns a button used to get status for the YC response
     * @return Ext.button.Button
     */
    createGetWabButton: function() {
        var me = this;

        me.getStatusButton = Ext.create('Ext.button.Button', {
            text: me.snippets.textStatus,
            id: 'btnInit',
            cls: 'primary',
            hidden: true,            
            handler: function () {
                me.fireEvent('getPreStatus');
            }
        });

        return me.getStatusButton;
    },

    /**
     * Creates an form fieldset for showing WAB response
     *
     * @return Ext.form.FieldSet
     */
    createWabFieldset: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            id: 'fldWab',
            flex: 1,
            padding: 10,
            collapsible: true,
            title: me.snippets.textCustomer,
            autoScroll: true,
            layout: 'anchor',
            hidden: true,
            items: [
                me.createWabText(),
                me.createGetWarButton()
            ]
        });
    },

    /**
     * Creates and returns WAB response text on the sidebar
     * @return Ext.form.Label
     */
    createWabText: function()  {
        var me = this;

        me.wabLabel = Ext.create('Ext.form.Label', {
            id: 'wablabel',
            padding: '0 0 5 0'            
        });

        return me.wabLabel;
    },

    /**
     * Creates and returns a button WAR response
     * @return Ext.button.Button
     */
    createGetWarButton: function() {
        var me = this;

        me.getStatusButton = Ext.create('Ext.button.Button', {
            text: me.snippets.textReply,
            id: 'btnWar',
            cls: 'primary',            
            handler: function () {
                me.fireEvent('getWarStatus');
            }
        });

        return me.getStatusButton;
    },

    /**
     * Creates an form fieldset for showing WAR response
     *
     * @return Ext.form.FieldSet
     */
    createWarFieldset: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            id: 'fldWar',
            flex: 1,
            padding: 10,
            collapsible: true,
            title: me.snippets.textWarResponse,
            autoScroll: true,
            layout: 'anchor',
            hidden: true,
            items: [
                me.createWarText()                
            ]
        });
    },

    /**
     * Creates and returns WAR response text on the sidebar
     * @return Ext.form.Label
     */
    createWarText: function() {
        var me = this;

        me.warLabel = Ext.create('Ext.form.Label', {
            id: 'warlabel',
            padding: '0 0 5 0',            
        });

        return me.warLabel;
    }
});
