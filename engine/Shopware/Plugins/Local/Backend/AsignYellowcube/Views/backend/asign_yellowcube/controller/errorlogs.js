/**
 * Controller Error logs backend view
 *
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.controller.Errorlogs
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.controller.Errorlogs', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init: function() {
        var me = this;
        me.control({
            // search field functionality added..
            'asignyellowcube-logs-listing-grid textfield[action=searchLogs]': {
                fieldchange: me.onSearch
            },

            // shwo more information
            'asignyellowcube-logs-listing-grid':{
                showItem: me.onOpenShowWindow
            }
        });

        me.callParent(arguments);
    },

    /**
    * Search feature implemented
    * @param [object] field object
    * @return void
    */
    onSearch: function (field) {
        var me = this,
            store = me.subApplication.logStore;

        //If the search-value is empty, reset the filter
        if (field.getValue().length == 0) {
            store.clearFilter();
        } else {
            //This won't reload the store
            store.filters.clear();

            //Loads the store with a special filter
            store.filter('searchValue', field.getValue());
        }
    },

    /**
     * The user wants to edit an article
     * @event render
     * @param [object] record object
     * @return void
     */
    onOpenShowWindow : function (record) {
        //Create edit-window
        this.getView('detail.errorlogs.More').create({ record: record});
    }
});
