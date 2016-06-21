/**
 * This file creates the listing window for Inventory
 *
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.list.Inventory
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.list.Inventory', {
    extend: 'Ext.grid.Panel',
    border: 0,
    alias:  'widget.asignyellowcube-inventory-listing-grid',
    region: 'center',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        columnYcNumber:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/ycnumber}Ycube Artnum{/s}',
        columnNumber:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/number}Article Nr.{/s}',
        columnDesc:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/payment}Article Desc.{/s}',
        columnStocks:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/stocks}Stock(s){/s}',
        columnUpdated:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/changed}Last Updated/Changed{/s}',

        textSearch:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/search}Search...{/s}',
        textUpdate:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/inventory/update}Update Inventory{/s}'
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.dockedItems = [];
        me.store = me.invStore;
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.dockedItems.push(me.toolbar);

        // Add paging toolbar to the bottom of the grid panel
        me.dockedItems.push({
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        });

        me.callParent(arguments);
    },

    getColumns: function () {
        var me = this;
        var columns = [
                {
                    header: me.snippets.columnYcNumber,
                    dataIndex: 'ycarticlenr',
                    flex: 1
                },
                {
                    header: me.snippets.columnNumber,
                    dataIndex: 'articlenr',
                    flex: 1
                },
                {
                    header: me.snippets.columnDesc,
                    dataIndex: 'artdesc',
                    flex: 1
                },
                {
                    header: me.snippets.columnStocks,
                    dataIndex: 'stockvalue',
                    flex: 1
                },
                {
                    header: me.snippets.columnUpdated,
                    dataIndex: 'createdon',
                    type: 'date',
                    flex: 1
                }
        ];

        return columns;
    },

    /**
     * Creates the toolbar with a save-button, a delete-button and a textfield to search for articles
     */
    getToolbar: function () {
        var me = this,
            searchField = Ext.create('Ext.form.field.Text', {
            name: 'searchfield',
            cls: 'searchfield',
            action: 'searchInventory',
            width: 170,
            enableKeyEvents: true,
            emptyText: me.snippets.textSearch,
            listeners: {
                buffer: 500,
                keyup: function () {
                    if (this.getValue().length >= 3 || this.getValue().length < 1) {
                        /**
                         * @param this Contains the searchfield
                         */
                        this.fireEvent('fieldchange', this);
                    }
                }
            }
        });

        searchField.addEvents('fieldchange');

        var items = [];

        items.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-arrow-circle-double-135',
            text: me.snippets.textUpdate,
            disabled: false,
            action: 'updInventory'
        }));

        items.push('->');
        items.push(searchField);
        items.push({
            xtype: 'tbspacer',
            width: 6
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: items
        });
        return toolbar;
    }
});
