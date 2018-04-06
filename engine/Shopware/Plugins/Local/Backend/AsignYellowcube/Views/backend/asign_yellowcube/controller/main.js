/**
 * This file defines mian controller with stores
 * 
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.controller.Main
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.controller.Main', {
    extend: 'Enlight.app.Controller',
    requires: [ 
        'Shopware.apps.AsignYellowcube.controller.Product',
        'Shopware.apps.AsignYellowcube.controller.Order',
        'Shopware.apps.AsignYellowcube.controller.Inventory',
        'Shopware.apps.AsignYellowcube.controller.Errorlogs' 
    ],
    
    init: function() {
        var me = this;

        me.subApplication.artStore = me.subApplication.getStore('Product').load();
        me.subApplication.ordStore = me.subApplication.getStore('Order').load();
        me.subApplication.invStore = me.subApplication.getStore('Inventory').load();
        me.subApplication.logStore = me.subApplication.getStore('Errorlogs').load();
        me.subApplication.countryStore = me.subApplication.getStore('Country').load();
        
        me.mainWindow = me.getView('main.Window').create({
            artStore: me.subApplication.artStore,
            ordStore: me.subApplication.ordStore,
            invStore: me.subApplication.invStore,
            logStore: me.subApplication.logStore,
            countryStore: me.subApplication.countryStore
        });

        this.callParent(arguments);
    }
});