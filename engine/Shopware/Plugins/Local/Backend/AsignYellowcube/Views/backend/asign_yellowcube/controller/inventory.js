/**
 * Controller for Inventory backend view
 * 
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.controller.Inventory
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.controller.Inventory', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        messageImport:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/inventory/update}Inventory items imported from Yellowcube. Total: {/s}',
        messageCheckCon:    '{s namespace="backend/asign_yellowcube/main"  name=yellowcube/controller/inventory/checkcon}Check plugin configurations or connection settings.{/s}',
        messageNoResponse:  '{s namespace="backend/asign_yellowcube/main"  name=yellowcube/controller/inventory/response}No updates received for inventory items!{/s}',

        textConnError:      '{s namespace="backend/asign_yellowcube/main"  name=yellowcube/controller/inventory/connect}Connection Error!{/s}',
        textWarning:        '{s namespace="backend/asign_yellowcube/main"  name=yellowcube/controller/inventory/warning}Attention! An error has occurred{/s}',
        textSuccess:        '{s namespace="backend/asign_yellowcube/main"  name=yellowcube/controller/inventory/warning}Success!{/s}'        
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            // search field functionality added..
            'asignyellowcube-inventory-listing-grid textfield[action=searchInventory]': {
                fieldchange: me.onSearch
            },

            //The update-button on the toolbar
            'asignyellowcube-inventory-listing-grid button[action=updInventory]':{
                click: me.onUpdateInventory
            },

            //The selection event
            'asignyellowcube-inventory-listing-grid':{
                selectionchange: me.onSelectionChange
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the selection in the inventory store changed
     * Will show/hide the components in the details view depending on the number of selected records
     * If one record was selected, the displaySidebarFields() function will be called
     * @param record
     */
    onSelectionChange: function(sm, selections) {        
        if (selections[0]) {
            var me = this,
            store = me.subApplication.invStore,
            record = selections[0],
            response = Ext.decode(record.get('additional')),
            style = 'style= "height:25px;"';

            // if the response in !empty?
            if (response !== null)  {
                finaltext = '<table width=100%>';

                Ext.Object.each(response, function(key, value, allset) {
                    if (value !== null) {
                        if (key === 'BestBeforeDate') {
                            value = Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
                        }
                        finaltext += '<tr><td ' + style + '><b>' + key + ':</b></td><td>' + value + '</td></tr>';
                    }                
                });
                finaltext += '</table><br />';
            } else {
                finaltext = null;
                Ext.getCmp('invlabel').setText(me.snippets.messageNoResponse, false);
            }

            me.displaySidebarFields(finaltext); // show/hide fields
        }        
    },

    /**
    * Display sidebar content based on condition
    * @param [string] finaltext
    * @return void
    */
    displaySidebarFields: function(finaltext) {
        if (finaltext !== null ) {
            Ext.getCmp('invlabel').setHeight(300);
            Ext.getCmp('invlabel').setText(finaltext, false);
        }        
    },


    /**
    * Search feature implemented
    * @param [object] field object
    * @return void
    */
    onSearch: function (field) {
        var me = this,
            store = me.subApplication.invStore;

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
     * Function to update the inventory list
     *
     * @event click
     * @param [object] btn Contains the clicked button
     * @return [boolean|null]
     */
    onUpdateInventory: function(btn){
        var me = this,
            win = btn.up('window');

        win.setLoading(true);        
        Ext.Ajax.request({
            url: "{url controller=AsignYellowcube action=updateList}",
            success: function (response) {              
                response = Ext.JSON.decode(response.responseText);
                var message = response.message;
                var code = response.code;
                
                // if the success message..
                win.setLoading(false);
                if(response.success) {
                    Ext.Msg.show({
                        title: me.snippets.textSuccess,
                        msg: me.snippets.messageImport + response.dcount,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });            
                    me.subApplication.invStore.load();     
                } else {
                    // error message === 'Internal Error: null' or -1?
                    var wtitle = me.snippets.textWarning;
                    if (message === 'Internal Error: null') {
                        message = me.snippets.messageCheckCon;
                        wtitle = me.snippets.textConnError;
                    } else if (code === -1) {
                        message = me.snippets.messageNoResponse;
                    }
                    Ext.Msg.show({
                        title: wtitle,
                        msg: message,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.ERROR
                    });
                }
            },            
            failure: function(e, options) {
                Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
            }
        });
    }
});
