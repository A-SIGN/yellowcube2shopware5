/**
 * This file defines rows for orders model
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.model.Order
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.model.Order', {
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
        { name : 'ordid', type: 'int' },
        { name : 'orderNumber', type: 'int' },
        { name : 'amount', type: 'float' },
        { name : 'amountNet', type: 'float' },
        { name : 'payment', type: 'string' },
        { name : 'shipping', type: 'string' },
        { name : 'status', type: 'string' },
        { name : 'lastSent', type: 'boolean' },
        { name : 'eori', type: 'string' },
        { name : 'ycReference', type: 'int' },
        { name : 'ycResponse', type: 'string' },
        { name : 'ycWabResponse', type: 'string' },
        { name : 'ycWarResponse', type: 'string' },
        { name : 'ycWarCount', type: 'int' },
        { name : 'ismanual', type: 'int' },
        { name : 'iswabaccepted', type: 'int' },
        { name : 'iswaraccepted', type: 'int' },
        { name : 'timestamp',  type: 'string', dateReadFormat: 'Y-m-d H:i:s.u', dateWriteFormat: 'd-m-Y H:i' }
    ]
});
