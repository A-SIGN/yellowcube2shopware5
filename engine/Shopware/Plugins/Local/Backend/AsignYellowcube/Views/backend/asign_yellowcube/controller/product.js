/**
 * This file defines controller for product
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1.3
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.controller.Product
 * @since     File available since Release 1.0
 */
Ext.define('Shopware.apps.AsignYellowcube.controller.Product', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        messageSaved:       '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/save}Additional information have been saved!!{/s}',
        messageFailed:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/failed}Communication Error: {/s}',
        messageSend:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/send}Selected article(s) sent successfully to Yellowcube. Total: {/s}',
        messageUpdate:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/update}Latest update received from Yellowcube!!{/s}',
        messageSelect:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/select}Please select mode of operation for this Product!!{/s}',
        messageCheckCon:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/checkcon}Check plugin configurations or connection settings.{/s}',
        messageNoResponse:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/response}No proper response received yet!! Possibly request pending approval!{/s}',
        messageHasEsd:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/hasesd}Selected article has ESD and cannot be sent.{/s}',

        textConnError:      '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/connect}Connection Error!{/s}',
        textWarning:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/warning}Attention! An error has occurred{/s}',
        textSuccess:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/controller/articles/warning}Success!{/s}'
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
            'asignyellowcube-listing-grid textfield[action=searchArticles]': {
                fieldchange: me.onSearch
            },

            //The send only active products
            'asignyellowcube-listing-grid button[action=sendAll]':{
                click: me.onSendAllEvent
            },

            // The save-button from the create-window
            'asignyellowcube-articles-detail-window button[action=saveAdditional]':{
                click: me.onCreateAdditional
            },

            // edit single value
            'asignyellowcube-listing-grid':{
                editItem: me.onOpenEditWindow,
                openArticle: me.onOpenArticleWindow,
                selectionchange: me.onSelectionChange
            },

            // The send article to YC
            'asignyellowcube-sidebar-article-detail':{
                sendArticle: me.onSendArticlesEvent,
                getStatus: me.onGetStatusEvent
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the selection in the article store changed
     * Will show/hide the components in the details view depending on the number of selected records
     * If one record was selected, the displaySidebarFields() function will be called
     * @param record
     */
    onSelectionChange: function(sm, selections) {
        if (selections[0]) {
            var me = this,
            store = me.subApplication.artStore,
            record = selections[0],
            articleId = record.get('artid');

            //check if the article is ESD
            var blAllowProductEsd = true;

            // has ESD?
            if (record.get('esdid') > 0 ) {
                blAllowProductEsd = false;
            }

            // has ESD option selected?
            if (record.get('incesd') == 1) {
                blAllowProductEsd = true;
            }

            // is ESD condition true?
            if (blAllowProductEsd === true) {
                if (record.get('ycResponse')) {
                    var response = Ext.decode(record.get('ycResponse')),
                    isAccepted = record.get('isaccepted');

                    // if the response in !empty?
                    finaltext = me.setResponseLayout(response);

                    // if the response is accepted and final ie. code=100
                    if (isAccepted == 1) {
                        Ext.getCmp('btnStat').setDisabled(true);
                        Ext.getCmp('btnStat').hide();
                    } else if (isAccepted == 2) {
                        Ext.getCmp('btnStat').setDisabled(false);
                    }
                } else {
                    finaltext = null;
                    Ext.getCmp('infolabel').setText(me.snippets.messageNoResponse, false);
                    Ext.getCmp('btnStat').hide();
                }
            } else {
                finaltext = null;
                Ext.getCmp('infolabel').setText(me.snippets.messageHasEsd, false);
                Ext.getCmp('fldCombo').hide();
                Ext.getCmp('btnStat').hide();
            }

            me.displaySidebarFields(finaltext, articleId, blAllowProductEsd); // show/hide fields
        }
    },

    /**
     * Function to frame and set response
     * @param [array] response
     * @return void
     */
    setResponseLayout: function(response){
        var errstyle = '', style = 'height:25px;',
            responseString = '<table width=100%>';

        Ext.Object.each(response, function(key, value, allset) {
            if (key === 'EventTimestamp') {
                value = Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            }

            if (key === 'StatusType' && value === 'E') {
                errstyle = 'color: #F00;';
                responseString += '<tr><td style= "' + style + errstyle + '"><b>' + key + ':</b></td><td style= "' + style + errstyle + '">' + value + '</td></tr>';
            } else {
                responseString += '<tr><td style= "' + style + '"><b>' + key + ':</b></td><td style= "' + style + '">' + value + '</td></tr>';
            }
        });
        responseString += '</table><br />';

        return responseString;
    },

    /**
    * Display sidebar content based on condition
    * @param [string] finaltext
    * @param [string] articleId
    * @return void
    */
    displaySidebarFields: function(finaltext, articleId, blAllowProductEsd) {

        // set up article id
        Ext.getCmp('textId').setValue(articleId);

        // show for non-ESD product
        if (blAllowProductEsd === true) {
            Ext.getCmp('fldCombo').show();
            Ext.getCmp('infolabel').setHeight(300);

            if (finaltext !== null ) {
                Ext.getCmp('btnStat').show();
                Ext.getCmp('infolabel').setText(finaltext, false);
            }
        }
    },

    /**
    * Perform search feature
    * @param [object] field object
    * @return void
    */
    onSearch: function (field) {
        var me = this,
            store = me.subApplication.artStore;

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
    onOpenEditWindow : function (record) {
        //Create edit-window
        this.getView('detail.articles.Window').create({ record: record});
    },

    /**
     * Event listener method which fired when the user clicks the article button
     * in the order list to show the article detail page.
     *
     * @param [Ext.data.Model] record - The row record
     */
    onOpenArticleWindow: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: record.get('artid')
            }
        });
    },

    /**
     * Function to create a new article
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onCreateAdditional: function(btn) {
        var me = this,
            win = btn.up('window'),
            form = win.down('form'),
            values = form.getValues();

        if (form.getForm().isValid()) {
            win.setLoading(true);
            Ext.Ajax.request({
                url: '{url controller=AsignYellowcube action=createAdditionals}',
                dataType: 'json',
                params: {
                    id:             values.id,
                    artid:          values.artid,
                    batchreq:       values.batchreq,
                    noflag:         values.noflag,
                    incesd:         values.incesd,
                    expdatetype:    values.expdatetype,
                    altunitiso:     values.altunitiso,
                    eantype:        values.eantype,
                    netto:          values.netto,
                    brutto:         values.brutto,
                    length:         values.length,
                    width:          values.width,
                    height:         values.height,
                    volume:         values.volume,
                    tariff:         values.tariff,
                    tara:           values.tara,
                    origin:         values.origin
                },
                success: function (result) {
                    response = Ext.JSON.decode(result.responseText);
                    var message = response.message;

                    // if the success message..
                    win.setLoading(false);
                    if(response.success) {
                        Ext.Msg.show({
                            title: me.snippets.textSuccess,
                            msg: me.snippets.messageSaved,
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.INFO
                        });
                        win.destroy();
                        me.subApplication.artStore.load();
                    }
                },
                failure: function(e, options) {
                    Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
                },
            });
        }
    },

    /**
     * Function to send all articles with no response
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onSendAllEvent: function(btn) {
        var me = this,
            optmode = 'xx';

            // send only active
            me.sendModeBasedProducts(btn, optmode);
    },

    /**
     * Function to send articles based on modes
     *
     * @event click
     * @param [object] btn Contains the clicked button
     * @param [string] optval mode of article update
     *
     * @return [boolean|null]
     */
    sendModeBasedProducts: function(btn, optval){
        var me = this,
            win = btn.up('window');

        win.setLoading(true);
        Ext.Ajax.request({
            url: '{url controller=AsignYellowcube action=sendProduct}',
            dataType: 'json',
            params: {
                optmode: optval
            },
            success: function (result) {
                response = Ext.JSON.decode(result.responseText);
                var message = response.message,
                    code = response.code,
                    total = response.dcount;

                // if the success message..
                win.setLoading(false);
                if(response.success) {
                    //win.destroy();
                    Ext.Msg.show({
                        title: me.snippets.textSuccess,
                        msg: me.snippets.messageSend + total,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });
                    me.subApplication.artStore.load();
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
                        icon: Ext.Msg.WARNING
                    });
                }
            },
            failure: function(e, options) {
                Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
            }
        });
    },

    /**
     * Callback function for the ComboBox in the Detail view
     * * Will askt the user for conformation
     * @param combo
     * @param record
     */
    onSendArticlesEvent: function(combo, record) {
        var me = this,
            optmode = Ext.getCmp('optid').getValue(),
            rowid = Ext.getCmp('textId').getValue();

            if (optmode === null) {
                var message = me.snippets.messageSelect;
                Ext.Msg.show({
                    title: me.snippets.textWarning,
                    msg: message,
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING
                });
            } else {
                me.updateYcResponse(optmode, rowid);
            }
    },

    /**
     * Function to get Status from YC
     * @event click
     * @return void
     */
    onGetStatusEvent: function(){
        var me   = this;
        rowid   = Ext.getCmp('textId').getValue();
        optmode = 'S';

        me.updateYcResponse(optmode, rowid);
    },

    /**
     * Function executes the request based on data
     *
     * @event click
     * @param [string] optval Operating mode (S = status)
     * @param [int] rowid Contains the row-index
     * @return vpid
     */
    updateYcResponse: function (optval, rowid) {
        var me = this;
        Ext.getCmp('mainWindow').setLoading(true);
        Ext.Ajax.request({
            url: "{url controller=AsignYellowcube action=sendArticles}",
            params: {
                artid: rowid,
                mode:  optval
            },
            success: function (response) {
                var response = Ext.JSON.decode(response.responseText),
                    message = response.message,
                    code = response.code, total = 1;

                // total > 1? then show it..
                if (response.dcount > 1) {
                    total = response.dcount;
                }

                // if the success message..
                Ext.getCmp('mainWindow').setLoading(false);
                if(response.success) {
                    var message = me.snippets.messageSend + total;

                    if (response.mode == 'S') {
                        message = me.snippets.messageUpdate;
                    }

                    Ext.Msg.show({
                        title: me.snippets.textWarning,
                        msg: message,
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.INFO
                    });

                    me.subApplication.artStore.load();

                    // frame and set the response (temp)
                    resultString = me.setResponseLayout(Ext.JSON.decode(response.dataresult));
                    me.displaySidebarFields(resultString, rowid, true); // show/hide fields

                    // enable the button if the status == 10
                    if (response.statcode != 100) {
                        Ext.getCmp('btnStat').setDisabled(false);
                    } else {
                        Ext.getCmp('btnStat').setDisabled(true);
                    }
                } else {
                    // error message === 'Internal Error: null' or -1?
                    var wtitle = me.snippets.textWarning;
                    if (message === 'Internal Error: null') {
                        message = me.snippets.messageCheckCon;
                        wtitle = me.snippets.textConnError;

                        Ext.Msg.show({
	                        title: wtitle,
	                        msg: message,
	                        buttons: Ext.Msg.OK,
	                        icon: Ext.Msg.ERROR
	                    });
                    }

                    me.subApplication.artStore.load();
                }
            },
            failure: function(e, options) {
                Ext.MessageBox.alert(me.snippets.textWarning, me.snippets.messageFailed + e.status);
            }
        });
    }
});
