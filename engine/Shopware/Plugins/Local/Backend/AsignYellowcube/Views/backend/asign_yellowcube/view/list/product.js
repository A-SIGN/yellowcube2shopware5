/**
 * This file creates the listing window for Product
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.list.Product
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.list.Product', {
    extend: 'Ext.grid.Panel',
    border: 0,
    alias:  'widget.asignyellowcube-listing-grid',
    region: 'center',
    id: 'asigngrid',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        tooltipEdit:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/edit}Edit and save additional information{/s}',
        tooltipOpen:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/open}Open selected article details.{/s}',
        tooltipAllProduct:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/sendall}Send articles that were changed and are not send yet to Yellowcube.{/s}',

        columnNumber:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/number}Product Number{/s}',
        columnName:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/name}Product Name{/s}',
        columnStock:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/stock}Stock(s){/s}',
        columnESD:          '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/esd}Has ESD{/s}',
        columnDetails:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/params}Params Set?{/s}',
        columnActive:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/colact}Active{/s}',
        columnReference:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/reference}Reference No.{/s}',
        columnChanged:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/changed}Last Updated/Changed{/s}',

        textSearch:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/search}Search...{/s}',
        textUpdate:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/update}Update products{/s}',
        textAllProduct:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/articles/all}Send All products{/s}'
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.registerEvents();

        me.dockedItems = [];
        me.store = me.artStore;
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
             * Event will be fired when the user clicks the edit icon in the
             * action column
             *
             * @event editItem
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'editItem',

            /**
             * Event will be fired when the user clicks the open icon in the
             * action column
             *
             * @event openArticle
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'openArticle'
        );

        return true;
    },

    getColumns: function () {
        var me = this,
            buttons = new Array();

        // open article
        buttons.push({
            iconCls: 'sprite-inbox-image',
            tooltip: me.snippets.tooltipOpen,
            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                        record = store.getAt(rowIndex);
                me.fireEvent('openArticle', record);
            }
        });

        // overview
        buttons.push({
            iconCls: 'sprite-gear',
            tooltip: me.snippets.tooltipEdit,
            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                        record = store.getAt(rowIndex);
                me.fireEvent('editItem', record);
            }
        });

        var columns = [
                {
                    renderer: me.activeColumnRenderer,
                    dataIndex: 'active',
                    align: 'center',
                    width: 20,
                    flex: 0
                },
                {
                    header: me.snippets.columnNumber,
                    dataIndex: 'ordernumber',
                    flex: 1
                },
                {
                    header: me.snippets.columnName,
                    dataIndex: 'name',
                    width: 300,
                    flex: 2
                },
                {
                    renderer: me.paramsColumnRenderer,
                    header: me.snippets.columnDetails,
                    dataIndex: 'blhasdetails',
                    align: 'center',
                    width: 80
                },
                {
                    renderer: me.esdColumnRenderer,
                    header: me.snippets.columnESD,
                    dataIndex: 'esdid',
                    align: 'center',
                    width: 80
                },
                {
                    header: me.snippets.columnStock,
                    dataIndex: 'instock',
                    align: 'center',
                    width: 80
                },
                {
                    header: me.snippets.columnReference,
                    align: 'center',
                    dataIndex: 'ycReference',
                    width: 100
                },
                {
                    header: me.snippets.columnChanged,
                    dataIndex: 'createDate',
                    flex: 1,
                    type: 'date'
                },
                {
                    xtype: 'actioncolumn',
                    align: 'center',
                    width: 80,
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
            action: 'searchArticles',
            width: 170,
            enableKeyEvents: true,
            emptyText: me.snippets.search,
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
            tooltip: me.snippets.tooltipAllProduct,
            iconCls: 'sprite-blue-folders-stack',
            text: me.snippets.textAllProduct,
            disabled: false,
            action: 'sendAll'
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
    },

    /**
     * @param [object] - value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick-small"  style="width: 25px;margin:0 auto;">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px;margin:0 auto;">&nbsp;</div>';
        }
    },

    /**
     * @param [object] - value
     */
    esdColumnRenderer: function(value) {
        if (value > 0) {
            return '<div class="sprite-tick-small"  style="width: 25px;margin:0 auto;">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px;margin:0 auto;">&nbsp;</div>';
        }
    },

    /**
     * @param [object] - value
     */
    paramsColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick-small"  style="width: 25px;margin:0 auto;">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px;margin:0 auto;">&nbsp;</div>';
        }
    }
});
