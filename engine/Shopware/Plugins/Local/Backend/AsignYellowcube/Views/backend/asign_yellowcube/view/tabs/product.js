/**
 * This file defines rows for product tab view
 * 
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.view.tabs.Product
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.view.tabs.Product', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.asignyellowcube-tabs-product',    
    layout: 'border',

    defaults: {
        bodyBorder: 0
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;        
        me.items = me.createItems();

        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            {
                xtype:'asignyellowcube-listing-grid',
                artStore: me.artStore
            },
            {
                xtype: 'asignyellowcube-sidebar-article-detail',
                artStore: me.artStore
            }
        ];
    }

});