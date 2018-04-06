/**
 * This file creates the listing window for Orders
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.list.Order
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.list.Order', {
    extend: 'Ext.grid.Panel',
    border: 0,
    alias:  'widget.asignyellowcube-order-listing-grid',
    region: 'center',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        tooltipEdit:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/edit}View Order response from Yellowcube{/s}',
        tooltipOpen:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/open}Open selected order details.{/s}',

        columnNumber:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/number}Order Number{/s}',
        columnAmount:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/name}Amount{/s}',
        columnPayment:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/payment}Payment{/s}',
        columnShipping:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/shipping}Shipping{/s}',
        columnOrdTime:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/ordertime}Order Time{/s}',
        columnStatus:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/status}Status{/s}',
        columnReference: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/reference}Reference No.{/s}',
        columnEori:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/eori}EORI{/s}',
        columnChanged:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/changed}Last Updated/Changed{/s}',

        textSearch:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/search}Search...{/s}',
        textUpdate:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/update}Update orders{/s}',
        textPrepaid:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/orders/active}Send Prepaid orders{/s}'
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.registerEvents();

        me.dockedItems = [];
        me.store = me.ordStore;
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

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the open icon in the
             * action column
             *
             * @event openOrder
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'openOrder'
        );

        return true;
    },

    getColumns: function () {
        var me = this,
            buttons = new Array();

        // open order
        buttons.push({
            iconCls: 'sprite-sticky-notes-pin customers--orders',
            tooltip: me.snippets.tooltipOpen,
            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                        record = store.getAt(rowIndex);
                me.fireEvent('openOrder', record);
            }
        });

        var columns = [
                {
                    header: me.snippets.columnOrdTime,
                    dataIndex: 'timestamp',
                    type: 'date',
                    flex: 1
                },
                {
                    header: me.snippets.columnNumber,
                    dataIndex: 'orderNumber',
                    flex: 1
                },
                {
                    header: me.snippets.columnAmount,
                    dataIndex: 'amount',
                    flex: 1
                },
                {
                    header: me.snippets.columnPayment,
                    dataIndex: 'payment',
                    flex: 1
                },
                {
                    header: me.snippets.columnShipping,
                    dataIndex: 'shipping',
                    flex: 1
                },
                {
                    header: me.snippets.columnStatus,
                    dataIndex: 'status',
                    flex: 1
                },
                {
                    header: me.snippets.columnEori,
                    dataIndex: 'eori',
                    flex: 1
                },
                {
                    header: me.snippets.columnReference,
                    dataIndex: 'ycReference',
                    flex: 1
                },
                {
                    xtype: 'actioncolumn',
                    align: 'center',
                    items: buttons
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
            action: 'searchOrders',
            width: 170,
            enableKeyEvents: true,
            emptyText: me.snippets.textSearch,
            listeners: {
                buffer: 500,
                keyup: function () {
                    if (this.getValue().length >= 3 || this.getValue().length < 1) {
                        this.fireEvent('fieldchange', this);
                    }
                }
            }
        });

        searchField.addEvents('fieldchange');

        var items = [];

        items.push(Ext.create('Ext.button.Button', {
            iconCls: 'sprite-moneys',
            text: me.snippets.textPrepaid,
            disabled: false,
            action: 'ordPrepaid'
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
