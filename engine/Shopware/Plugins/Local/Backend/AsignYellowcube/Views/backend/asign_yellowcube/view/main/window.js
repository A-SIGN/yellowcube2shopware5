/**
 * This file creates the main window for plugin
 *
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.main.Window
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.main.Window', {
    extend: 'Enlight.app.Window',
    title : 'Yellowcube Shopware Plugin v2.0',
    cls: Ext.baseCSSPrefix + 'systeminfo-window',
    alias: 'widget.myarticles-main-window',
    autoShow: true,
    layout: 'fit',
    stateful:true,
    stateId:'shopware-myarticles-window',
    height: '90%',
    width: 1024,
    overflow: 'hidden',
    autoMaximize: true,
    fullscreen: true,
    maximizable:true,
    minimizable:true,
    id: 'mainWindow',

    /**
     * Contains all snippets for this component
     */
    snippets: {
        articles:   '{s namespace="backend/asign_yellowcube/main" name=yellowcube/window/articles}Articles{/s}',
        orders:     '{s namespace="backend/asign_yellowcube/main" name=yellowcube/window/orders}Orders{/s}',
        inventory:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/window/inventory}Inventory{/s}',
        errorlogs:  '{s namespace="backend/asign_yellowcube/main" name=yellowcube/window/logs}Error Logs{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        var tabPanel = me.createTabPanel();

        me.items = [tabPanel];
        me.callParent(arguments);
    },

    /**
     * Creates the tabPanel
     * @return [Ext.tab.Panel]
     */
    createTabPanel: function(){
        var me = this;
        var tabPanel = Ext.create('Ext.tab.Panel', {
            items: [
                {
                    xtype: 'asignyellowcube-tabs-product',
                    artStore: me.artStore,
                    title: me.snippets.articles
                },
                {
                    xtype: 'asignyellowcube-tabs-order',
                    title: me.snippets.orders,
                    ordStore: me.ordStore
                },
                {
                    xtype: 'asignyellowcube-tab-inventory',
                    title: me.snippets.inventory,
                    invStore: me.invStore
                },
                {
                    xtype: 'asignyellowcube-logs-listing-grid',
                    title: me.snippets.errorlogs,
                    logStore: me.logStore
                }
            ]
        });

        return tabPanel;
    }
});
