/**
 * This file creates the listing window for errorlogs
 *
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.list.Errorlogs
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.list.Errorlogs', {
    extend: 'Ext.grid.Panel',
    border: 0,
    alias:  'widget.asignyellowcube-logs-listing-grid',
    region: 'center',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        tooltipMore:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/tooltip/more}View developer level logs for debugging.{/s}',

        columnCreate:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/logs/date}Log Date{/s}',
        columnType:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/logs/type}Type{/s}',
        columnMessage:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/logs/message}Mesage{/s}',
        columnMore:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/logs/more}More Logs{/s}',

        textSearch:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/logs/search}Search...{/s}',
        textDelete:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/grid/logs/delete}Delete marked entries{/s}'
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.registerEvents();

        me.dockedItems = [];
        me.store = me.logStore;

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
             * Event will be fired when the user clicks the view icon in the
             * action column
             *
             * @event showItem
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'showItem'
        );

        return true;
    },

    getColumns: function () {
        var me = this,
            buttons = new Array();

        // view logs
        buttons.push({
            iconCls: 'sprite-magnifier',
            tooltip: me.snippets.tooltipMore,
            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                        record = store.getAt(rowIndex);
                me.fireEvent('showItem', record);
            }
        });

        var columns = [
                {
                    header: me.snippets.columnCreate,
                    dataIndex: 'createdon',
                    width: 150,
                    type: 'date',
                    flex: 0
                },
                {
                    header: me.snippets.columnType,
                    dataIndex: 'type',
                    width: 150,
                    flex: 0
                },
                {
                    header: me.snippets.columnMessage,
                    dataIndex: 'message',
                    width: 300,
                    flex: 4
                },
                {
                    header: me.snippets.columnMore,
                    xtype: 'actioncolumn',
                    items: buttons,
                    align: 'center',
                    width: 60,
                    flex: 0
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
            action: 'searchLogs',
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

    dateColumn: function (value, metaData, record) {
        if (value === Ext.undefined) {
            return value;
        }

        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
    }
});
