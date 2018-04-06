/**
 * This file defines rows for inventory model
 * 
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       Shopware.apps.AsignYellowcube.model.Inventory
 * @since     File available since Release 1.0
 */

Ext.define('Shopware.apps.AsignYellowcube.model.Inventory', {
   /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',
    
    /**
     * The fields used for this model
     * @array
     */
    fields : [
        { name : 'id', type : 'int' },
        { name : 'ycarticlenr', type : 'string'},
        { name : 'articlenr', type : 'string' },
        { name : 'artdesc', type : 'string' },        
        { name : 'additional', type : 'string' },        
        { name : 'stockvalue', type : 'string' },
        { name : 'createdon',  type: 'string', dateReadFormat: 'Y-m-d H:i:s.u', dateWriteFormat: 'd-m-Y H:i' }
    ]
});
