/**
 * This file defines rows for country store
 * 
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.store.Order
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.store.Country', {    
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Store',
    storeId: 'origincountries',

    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: false,
    
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 1000,
    remoteFilter: true,    
    remoteSort: true,
    
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Base.model.Country',

     /**
     * Configure the data communication
     * @object
     */
    proxy:{
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * @object
         */
        api: {            
            read: '{url controller="base" action="getCountries"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});