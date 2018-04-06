/**
 * This file defines sidebar view for inventory
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

Ext.define('Shopware.apps.AsignYellowcube.view.sidebar.Inventory', {
    extend: 'Ext.panel.Panel',
    collapsed: true,
    collapsible: true,    
    region: 'east',
    width: 300,
    alias: 'widget.asignyellowcube-sidebar-inventory-detail',

    /**
     * Contains all snippets for this component
     */
    snippets: {        
        textTitle:          '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/inventory/title}More Information{/s}',
        textNothingSelected:'{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/inventory/nothing}No item selected!!{/s}'
    },

    /**
     * Init the main detail component, add components
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.textTitle;
        me.items = [ me.createContainer() ];

        me.callParent(arguments);
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
            invStore: me.invStore,
            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            items: [ me.createInventoryFieldset() ]
        });
    },

    /**
     * Creates an form fieldset for showing response
     *
     * @return Ext.form.FieldSet
     */
    createInventoryFieldset: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            id: 'fldInv',
            flex: 1,
            padding: 10,
            collapsible: true,
            title: me.snippets.textRequestStat,
            autoScroll: true,
            layout: 'anchor',
            items: [ me.createInventoryText() ]
        });
    },

    /**
     * Creates and returns response text on the sidebar
     * @return Ext.form.Label
     */
    createInventoryText: function()  {
        var me = this;

        me.invLabel = Ext.create('Ext.form.Label', {
            id: 'invlabel',
            text: me.snippets.textNothingSelected,
            padding: '0 0 5 0',
            resizable: true
        });
        return me.invLabel;
    }
});
