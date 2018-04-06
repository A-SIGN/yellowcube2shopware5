/**
 * This file defines controller for orders
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.controller.Order
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.controller.Order', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        messageEoriSaved:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/eorisave}EORI details saved for selected Order!!{/s}',
        messageFailed:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/failed}Communication Error: {/s}',
        messageSend:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/send}Selected order(s) sent successfully to Yellowcube. Total: {/s}',
        messageManual:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/manual}No initial response recorded. Send this order manually to Yellowcube.{/s}',
        messageUpdate:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/update}Latest update received from Yellowcube!!{/s}',
        messageWab:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/wab}Customer Order Status (WAB) information received!!{/s}',
        messageWar:         '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/war}Yellowcube Customer Order Reply (WAR) received!!{/s}',
        messageCheckCon:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/checkcon}Check plugin configurations or connection settings.{/s}',
        messageNoPrepaid:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/prepaid}No prepaid orders found!!{/s}',
        messageNoResponse:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/response}No proper response received yet!! Possibly request pending approval!{/s}',
        messageZipNomatch:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/nomatch}Zipcode format not matching with address country!!!{/s}',
        messageZipInvalid:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/invalid}Zipcode format is not correct and is invalid!!!{/s}',
        messageEoriEmpty:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/eoriempty}Please specify a value for EORI!!{/s}',

        textConnError:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/connect}Connection Error!{/s}',
        textWarning:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/warning}Attention! An error has occurred{/s}',
        textSuccess:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/orders/warning}Success!{/s}'
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
            'asignyellowcube-order-listing-grid textfield[action=searchOrders]': {
                fieldchange: me.onSearch
            },

            //sends on pre-paid orders
            'asignyellowcube-order-listing-grid button[action=ordPrepaid]':{
                click: me.onPrepaidOrders
            },

            // edit single value
            'asignyellowcube-order-listing-grid':{
                openOrder: me.onOpenOrderWindow,
                selectionchange: me.onSelectionChange
            },

            // action triggered for getting Wab response
            'asignyellowcube-sidebar-order-detail':{
                saveEori:       me.onSaveEoriEvent,
                sendOrder:      me.onSendOrderEvent,
                getPreStatus:   me.onGetPreStatus,
                getWarStatus:   me.onGetWarStatus
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the selection in the order store changed
     * Will show/hide the components in the details view depending on the number of selected records
     * If one record was selected, the displaySidebarFields() function will be called
     * @param record
     */
    onSelectionChange: function(sm, selections) {
        if (selections[0]) {
            var me = this,
            store = me.subApplication.ordStore,
            record = selections[0],
            warcount = record.get('ycWarCount'),
            ismanual = record.get('ismanual'),
            finalInit = null, finalWab = null, finalWar = null,
            orderId = record.get('ordid');

            // frame the response for initial, wab and war
            if (ismanual && record.get('ycReference') == 0) {
                Ext.getCmp('btnManual').show();
                Ext.getCmp('btnInit').hide();
                Ext.getCmp('fldWab').hide();
                Ext.getCmp('fldWar').hide();
                Ext.getCmp('resplabel').setText(me.snippets.messageManual, false);
            } else {
                finalInit = me.frameInitialResponse(record.get('ycResponse'), record.get('iswabaccepted'));
            }

            finalWab = me.frameWabResponse(record.get('ycWabResponse'), record.get('iswaraccepted'));

            if (warcount !== 0) {
                finalWar  = me.frameWarResponse(record.get('ycWarResponse'));
            }

            // imprint them on sidebar panel
            me.displaySidebarFields(finalInit, finalWab, finalWar, orderId);
        }
    },

    /**
    * frames and returns initial response as string with html and response data
    * @param [object] ycresponse object
    * @param [integer] iswabaccepted integer
    * @return string
    */
    frameInitialResponse: function(ycresponse, iswabaccepted) {
        var response = Ext.JSON.decode(ycresponse);

        // if the response in !empty?
        if (response != null)  {
            var finaltext = '<table width=100%>';

            Ext.Object.each(response, function(key, value, allset) {
                if (key === 'EventTimestamp') {
                    value = Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
                }

                // if its an E?
                if (key === 'StatusType' && value === 'E') {
                    var errstyle = 'style="color: #F00;"';
                }
                finaltext += '<tr><td ' + errstyle + '><b>' + key + ':</b></td><td ' + errstyle + '>' + value + '</td></tr>';
            });
            finaltext += '</table><br />';

            // if the response is true?
            if (iswabaccepted == 1) {
                Ext.getCmp('btnInit').setDisabled(false);
            }
        }

        return finaltext;
    },

    /**
    * Frames and returns WAB response as string with html and response data
    * @param [object] record object
    * @param [integer] iswaraccepted integer
    * @return string
    */
    frameWabResponse: function(ycwabresponse, iswaraccepted) {
        var wabResponse = Ext.JSON.decode(ycwabresponse);

        // if the WAB Response in !empty?
        if (wabResponse != null)  {
            var finalWab = '<table width=100%>';

            Ext.Object.each(wabResponse, function(key, value, allset) {
                if (key === 'EventTimestamp') {
                    value = Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
                }

                // if its an E?
                if (key === 'StatusType' && value === 'E') {
                    var errstyle = 'style="color: #F00;"';
                }
                finalWab += '<tr><td ' + errstyle + '><b>' + key + ':</b></td><td ' + errstyle + '>' + value + '</td></tr>';
            });
            finalWab += '</table><br />';

            // if the response is true?
            if (iswaraccepted == 1) {
                Ext.getCmp('btnInit').setDisabled(true);
                Ext.getCmp('btnWar').setDisabled(false);
            } else {
                // because the status is E not S. So asking for WAR response is pointlesss
                Ext.getCmp('btnWar').setDisabled(true);
            }
        } else {
            finalWab = null;
        }

        return finalWab;
    },

    /**
    * Frames and returns WAR response as string with html and response data
    * @param [object] record object
    * @return string
    */
    frameWarResponse: function(ycwarresponse) {
        var warResponse = Ext.JSON.decode(ycwarresponse),
        	finalWar = '', sResponse = '';

        // if the WAR Response in !empty?
        if (warResponse !== Ext.undefined)  {
        	Ext.Object.each(warResponse, function(key, child, allset) {
                sResponse += '<table width=100% border=0>';
                sResponse += '<tr><td colspan=2><b><u>' + key + '</u></b></td></tr>';

                // loop through GoodsIssueHeader, CustomerOrderHeader and CustomerOrderList
                Ext.Object.each(child, function(cky, value, ckset) {
                    if (key === 'CustomerOrderList') {
                        // loop through the CustomerOrderList
                        sResponse += '<tr><td><table width=100% border=0>';
                        Ext.Object.each(value, function(oky, oval, okset) {
                            // if the key param is quantityOUM then?
                            if (oky === 'QuantityUOM') {
                                sResponse += '<tr><td><b>' + oky + ':</b></td><td>' + oval._ + ' ' + oval.QuantityISO + '</td></tr>';
                            } else {
                                sResponse += '<tr><td><b>' + oky + ':</b></td><td>' + oval + '</td></tr>';
                            }
                        });
                        sResponse += '</table><br /></td></tr>';
                    } else {
                        sResponse += '<tr><td><b>' + cky + ':</b></td><td>' + value + '</td></tr>';
                    }
                });

                sResponse += '</table><hr />';
            });
            finalWar += sResponse;
        } else {
            finalWar = null;
        }

        return finalWar;
    },

    /**
    * Prints the framed response on Sidebar panel
    * @param [string] finaltext
    * @param [string] finalWab
    * @param [string] finalWar
    * @param [string] orderId
    * @return void
    */
    displaySidebarFields: function(finaltext, finalWab, finalWar, orderId) {
        Ext.getCmp('textoId').setValue(orderId);

        // print initial response
        if (finaltext != null) {
            Ext.getCmp('btnManual').hide(); // hide manual button
            Ext.getCmp('btnInit').show();
            Ext.getCmp('resplabel').setText(finaltext, false);
        }

        // print WAB response
        if (finalWab != null) {
            Ext.getCmp('btnManual').hide(); // hide manual button
            Ext.getCmp('fldWab').show();
            Ext.getCmp('wablabel').setText(finalWab, false);
        }

        // print WAR response
        if (finalWar != null) {
            Ext.getCmp('btnManual').hide(); // hide manual button
            Ext.getCmp('fldWar').show();
            Ext.getCmp('warlabel').setText(finalWar, false);
        }
    },

    /**
    * Search feature with AJax
    * @param [object] field object
    */
    onSearch: function (field) {
        var me = this,
            store = me.subApplication.ordStore;

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
     * Event listener method which fired when the user clicks the order button
     * in the order list to show the order detail page.
     *
     * @param [Ext.data.Model] record - The row record
     */
    onOpenOrderWindow: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            action: 'detail',
            params: {
                orderId: record.get('ordid')
            }
        });
    },

    /**
     * Function to send only prepaid orders
     *
     * @event click
     * @param [object] btn Contains the clicked button
     * @return [boolean|null]
     */
    onPrepaidOrders: function(btn){
        var me = this;

       Ext.getCmp('mainWindow').setLoading(true);
        Ext.Ajax.request({
            url: '{url controller=AsignYellowcube action=sendPrepaid}',
            dataType: 'json',
            params: {
                vType: 2
            },
            success: function (result) {
                var response = Ext.JSON.decode(result.responseText),
                	message = response.message,
                	code = response.code;

                // if the success message..
                Ext.getCmp('mainWindow').setLoading(false);
                if(response.success) {
                    Ext.Msg.show({
                        title: me.snippets.textSuccess,
                        msg: me.snippets.messageSend + response.dcount,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });
                    me.subApplication.ordStore.load();
                } else {
                    // error message === 'Internal Error: null' or -1?
                    var wtitle = me.snippets.textWarning;
                    if (message === 'Internal Error: null') {
                        message = me.snippets.messageCheckCon;
                        wtitle = me.snippets.textConnError;
                    } else if (code === -1) {
                        message = me.snippets.messageNoPrepaid;
                    }

                    Ext.Msg.show({
                        title: wtitle,
                        msg: message,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING
                    });
                }
            },
            failure: function(e, options) {
                Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
            },
        });
    },

    /**
     * Function to send order to Yellowcube
     * @param [object] record object
     * @return void
     */
    onSendOrderEvent: function(record) {
        var me = this,
            rowid = Ext.getCmp('textoId').getValue(),
            statusCode = 0;

        Ext.getCmp('mainWindow').setLoading(true);
        Ext.Ajax.request({
            url: "{url controller=AsignYellowcube action=createOrder}",
            params: {
                ordid: rowid
            },
            success: function (response) {
                var response = Ext.JSON.decode(response.responseText),
                	message = response.message, code = response.code;

                // if the success message..
                Ext.getCmp('mainWindow').setLoading(false);
                if(response.success) {
                    Ext.Msg.show({
                        title: me.snippets.textSuccess,
                        msg: me.snippets.messageSend + response.dcount,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });

                    me.subApplication.ordStore.load();
                    if (response.statcode == 10) {
                        statusCode = 1;
                    }

                    // frame and set the response (temp)
                    resultString = me.frameInitialResponse(response.dataresult, statusCode);
                    me.displaySidebarFields(resultString, null, null, rowid); // show/hide fields

                    // enable the button if the status == 10
                    if (response.statcode != 100) {
                        Ext.getCmp('btnInit').setDisabled(false);
                    } else {
                        Ext.getCmp('btnInit').setDisabled(true);
                    }
                } else {
                    // error message === 'Internal Error: null' or -1?
                    var wtitle = me.snippets.textWarning, errmsg ;
                    if (message === 'Internal Error: null') {
                        message = me.snippets.messageCheckCon;
                        wtitle = me.snippets.textConnError;
                    } else if (code === -1) {
                        message = me.snippets.messageNoResponse;
                    } else if (code === -2) {
                        message = me.snippets.messageZipNomatch;
                    } else if (code === -3) {
                        message = me.snippets.messageZipInvalid;
                    } else {
                        message = response.message;
                    }

                    Ext.Msg.show({
                        title: wtitle,
                        msg: message,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.ERROR
                    });
                    me.subApplication.ordStore.load();
                }
            },
            failure: function(e, options) {
                Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
            }
        });
    },

    /**
     * Function to get WAB response
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onGetPreStatus: function(btn) {
        var me = this,
            rowid   = Ext.getCmp('textoId').getValue();
            optmode = 'WAB';

            // get Order response
            me.updateYcResponse(optmode, rowid);
    },

    /**
     * Function to get WAR response
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onGetWarStatus: function(btn) {
        var me = this,
            rowid   = Ext.getCmp('textoId').getValue();
            optmode = 'WAR';

            // get Order response
            me.updateYcResponse(optmode, rowid);
    },

    /**
     * Function executes the request based on data
     *
     * @event click
     * @param [string] optval of request WAB or WAR
     * @param [int]    rowid Contains the row-index
     *
     * @return mixed
     */
    updateYcResponse: function (optval, rowid) {
        var me = this, urlrec = '', statusCode = 0;

        Ext.getCmp('mainWindow').setLoading(true);
        Ext.Ajax.request({
            url: '{url controller=AsignYellowcube action=createOrder}',
            params: {
                ordid:   rowid,
                mode:    optval
            },
            success: function (response) {
                var response = Ext.JSON.decode(response.responseText),
                	code = response.code;

                // if the success message..
                Ext.getCmp('mainWindow').setLoading(false);
                if(response.success) {
                    var message = me.snippets.messageSend;

                    if (optval == 'WAB') {
                        message = me.snippets.messageWab;
                        Ext.getCmp('btnInit').setDisabled(true); // hide the WAB button

                        // frame and set the response (temp)
                        if (response.statcode == 10) {
                            statusCode = 1;
                        }
                        resultString = me.frameWabResponse(response.dataresult, statusCode);
                        me.displaySidebarFields(null, resultString, null, rowid); // show/hide fields
                    } else if(optval == 'WAR') {
                        message = me.snippets.messageWar;
                        Ext.getCmp('btnWar').setDisabled(true); // hide the WAR button

                        // frame and set the response (temp)
                        if (response.statcode == 10) {
                            statusCode = 1;
                        }
                        resultString = me.frameWarResponse(response.dataresult);
                        me.displaySidebarFields(null, null, resultString, rowid); // show/hide fields
                    }

                    Ext.Msg.show({
                        title: me.snippets.textSuccess,
                        msg: message,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });

                    me.subApplication.ordStore.load();
                } else {
                    // error message === 'Internal Error: null' or -1?
                    var wtitle = me.snippets.textWarning;
                    if (message === 'Internal Error: null') {
                        message = me.snippets.messageCheckCon;
                        wtitle = me.snippets.textConnError;
                    } else if (code === -1) {
                        message = me.snippets.messageNoResponse;
                    } else {
                        message = response.message;
                    }
                    Ext.Msg.show({
                        title: wtitle,
                        msg: message,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.ERROR
                    });
                    me.subApplication.ordStore.load();
                }
            },
            failure: function(e, options) {
                Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
            }
        });
    }
});
