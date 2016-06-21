/**
 * This file defines sidebar view for product
 *
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.sidebar.Product
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.sidebar.Product', {
    extend: 'Ext.panel.Panel',
    collapsed: true,
    collapsible: true,
    region: 'east',
    width: 300,
    alias: 'widget.asignyellowcube-sidebar-article-detail',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        textTitle:          '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/title}Yellowcube Response{/s}',
        textNothingSelected:'{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/nothing}No article selected!!{/s}',
        textSendNow:        '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/sendnow}Send now{/s}',
        textRequestStat:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/artresp}Request status{/s}',
        textEmpty:          '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/empty}Please select mode{/s}',
        textGetResponse:    '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/getresp}Get Status from Yellowcube{/s}',
        textOptions: {
            optMode:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/mode}Mode of Operation{/s}',
            optInsert: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/insert}Insert Product{/s}',
            optUpdate: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/update}Update Product{/s}',
            optDelete: '{s namespace="backend/asign_yellowcube/main" name=yellowcube/list/sidebar/product/delete}Deactivate Product{/s}'
        }
    },

    /**
     * Init the main detail component, add components
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.textTitle;
        me.items = [ me.createContainer() ];
        me.addEvents('getStatus');

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
            artStore: me.artStore,
            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            items: [
                me.createFormFields(),
                me.createResponseFieldset()
            ]
        });
    },

    /**
     * Creates an form field set with all required configuration fields.
     *
     * @return Ext.form.FieldSet
     */
    createFormFields: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            flex: 2,
            id: 'fldCombo',
            alias: 'widget.asign-fieldset-form-panel',
            collapsible: true,
            title: me.snippets.textOptions.optMode,
            autoScroll: true,
            hidden: true,
            layout: 'column',
            items: [
                me.createModeCombo(),
                me.createProdSendButton(),
                me.createHiddenField()
            ]
        });
    },

    /**
     * Creates and returns the voucher combo. It will contain the available vouchers
     * @return Ext.form.ComboBox
     */
    createModeCombo: function() {
        var me = this;

        me.modeCombo = Ext.create('Ext.form.ComboBox', {
            name:'optmode',
            id: 'optid',
            store:[['I', me.snippets.textOptions.optInsert], ['U', me.snippets.textOptions.optUpdate], ['D', me.snippets.textOptions.optDelete]],
            emptyText: me.snippets.textEmpty,
            editable: false,
            columnWidth: 0.5
        });

        return me.modeCombo;
    },

    /**
     * Creates and returns hidden text field
     * @return Ext.form.Label
     */
    createHiddenField: function()  {
        var me = this;

        me.infoLabel = Ext.create('Ext.form.field.Text', {
            id: 'textId',
            name: 'articleId',
            hidden: true,
            border:0
        });
        return me.infoLabel;
    },

    /**
     * Button for sending the selected article to Yellowcube
     * @return Ext.button.Button
     */
    createProdSendButton: function() {
        var me = this;

        me.getProSendButton = Ext.create('Ext.button.Button', {
            text: me.snippets.textSendNow,
            id: 'btnSend',
            cls: 'primary',
            columnWidth: 0.5,
            handler: function () {
                me.fireEvent('sendArticle');
                Ext.getCmp('optid').reset();
            }
        });

        return me.getProSendButton;
    },

    /**
     * Creates an form fieldset for showing response
     *
     * @return Ext.form.FieldSet
     */
    createResponseFieldset: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            id: 'fldResp',
            flex: 1,
            padding: 10,
            collapsible: true,
            title: me.snippets.textRequestStat,
            autoScroll: true,
            layout: 'anchor',
            items: [
                me.createResponseText(),
                me.createGetStatusButton()
            ]
        });
    },

    /**
     * Creates and returns response text on the sidebar
     * @return Ext.form.Label
     */
    createResponseText: function()  {
        var me = this;

        me.infoLabel = Ext.create('Ext.form.Label', {
            id: 'infolabel',
            text: me.snippets.textNothingSelected,
            padding: '0 0 5 0',
            resizable: true
        });
        return me.infoLabel;
    },

    /**
     * Creates and returns a button used to get status for the YC response
     * @return Ext.button.Button
     */
    createGetStatusButton: function() {
        var me = this;

        me.getStatusButton = Ext.create('Ext.button.Button', {
            text: me.snippets.textGetResponse,
            id: 'btnStat',
            cls: 'primary',
            hidden: true,
            disabled: true,
            handler: function () {
                me.fireEvent('getStatus');
            }
        });

        return me.getStatusButton;
    }
});
