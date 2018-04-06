/**
 * This file defines rows for product model
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.model.Product
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.model.Product', {
    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'artid', type: 'int' },
        { name : 'ordernumber', type: 'string' },
        { name : 'name', type: 'string' },
        { name : 'instock', type: 'int' },
        { name : 'active', type: 'boolean' },
        { name : 'batchreq', type: 'int' },
        { name : 'noflag', type: 'int' },
        { name : 'esdid', type: 'int' },
        { name : 'incesd', type: 'int' },
        { name : 'expdatetype', type: 'string' },
        { name : 'altunitiso', type: 'string' },
        { name : 'eantype', type: 'string' },
        { name : 'netto', type: 'string' },
        { name : 'brutto', type: 'string' },
        { name : 'length', type: 'string' },
        { name : 'width', type: 'string' },
        { name : 'height', type: 'string' },
        { name : 'volume', type: 'string' },
        { name : 'createDate',  type: 'string', dateReadFormat: 'Y-m-d H:i:s.u', dateWriteFormat: 'd-m-Y H:i' },
        { name : 'lastSent', type: 'boolean' },
        { name : 'ycResponse', type: 'string' },
        { name : 'ycReference', type: 'int' },
        { name : 'isaccepted', type: 'int' },
        { name : 'blhasdetails', type: 'boolean' },

        // international settings
        { name : 'tariff', type: 'string' },
        { name : 'tara', type: 'string' },
        { name : 'origin', type: 'string' }
    ]
});
