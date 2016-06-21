/**
 * This file defines controllers, models and views
 * 
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.AsignYellowcube',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 
        'Main', 
        'Product', 
        'Order', 
        'Inventory',
        'Errorlogs' 
    ],

    views: [        
        'main.Window',

        'list.Product',
        'list.Order',
        'list.Inventory', 
        'list.Errorlogs',

        'detail.articles.Window',              
        'detail.errorlogs.More',

        'tabs.Product',
        'tabs.Orders',
        'tabs.Inventory',
        
        'sidebar.Product',
        'sidebar.Orders',
        'sidebar.Inventory'
    ],

    models: [ 
        'Product', 
        'Order',
        'Inventory',
        'Errorlogs'
    ],
    
    stores: [ 
        'Product', 
        'Order', 
        'Inventory',
        'Errorlogs'
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});