<?php

/**
 * This file handles CRON related functions
 *
 * PHP version 5
 * 
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright asign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @see       AsignYellowcubeCron
 * @since     File available since Release 1.0
 */

namespace Shopware\AsignYellowcube\Components\Api;

use Shopware\AsignYellowcube\Components\Api\AsignYellowcubeCore;
use Shopware\AsignYellowcube\Helpers\ApiClasses\AsignSoapClientApi;

/**
* Handles CRON related function
* 
* @category Asign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
class AsignYellowcubeCron
{    
    /** @var constants **/
    const YCRESPONSE = 'ycResponse';
    const YCWABRESPONSE = 'ycWabResponse';
    const YCWARRESPONSE = 'ycWarResponse';

    /** @var object **/
    protected $objErrorLog = null;

    /** @var object **/
    protected $objProduct = null;

    /** @var object **/
    protected $objOrders = null;

    /** @var object **/
    protected $objInventory = null;

    /** @var object **/
    protected $objYcubeCore = null;

    /**
     * Class constructor
     *
     * @return null
     */    
    public function __construct()
    {
        $this->objYcubeCore = new AsignYellowcubeCore();
        $this->objErrorLog  = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs");
        $this->objProduct   = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Product\Product");
        $this->objOrders    = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Orders\Orders");
        $this->objInventory = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Inventory\Inventory");        
    }
    
    /**
     * Creates New customer Order in Yellowcube     
     *
     * @param string $sMode Payment Parameter
     *
     * @return array
     */
    public function autoSendYCOrders($sMode = null, $isCron = false)
    {
        $iCount   = 0;
        
        try {
            // if pp = prepayment then?
            $sWhere = " and `paymentID` <> 5";
            if ($sMode === 'pp') {
                // 12 - completely_paid
                // 2 - completed 
                // as per s_core_status
                $sWhere = " and `paymentID` = 5 and `cleareddate` IS NOT NULL and `status` = 2 and `cleared` = 12";
            }
            $aOrders  = Shopware()->Db()->fetchAll("select `id` from `s_order` where `ordernumber` > 0" . $sWhere);
           
            if (count($aOrders) > 0) {
                foreach ($aOrders as $order) {                    
                    $ordid = $order['id'];
                    $oDetails = $this->objOrders->getOrderDetails($ordid, false, true);                    

                    // check if the Status in the Order table
                    $sRequestField = $this->getOrderRequestField($ordid);
                    $iStatusCode = $this->getRecordedStatus($ordid, 'asign_yellowcube_orders', $sRequestField);
                    $sResponseType = '';
                    
                    // get YC response                    
                    if ($iStatusCode == null && $this->objOrders->getFieldData($ordid, $sRequestField) == '') {
                        // execute the order object
                        echo "Submitting Order for OrderID: " . $ordid . "\n";
                        $aResponse = $this->objYcubeCore->createYCCustomerOrder($oDetails);                        
                    }  elseif ($iStatusCode < 100) {
                        // get the status
                        echo "Requesting WAB status for OrderID: " . $ordid . "\n";
                        $aResponse = $this->objYcubeCore->getYCGeneralDataStatus($ordid, "WAB");
                        $sResponseType = 'WAB';
                    } elseif ($iStatusCode == 100) {
                        // get the WAR status
                        echo "Requesting WAR status for OrderID: " . $ordid . "\n";
                        $aResponse = $this->objYcubeCore->getYCGeneralDataStatus($ordid, "WAR");
                        $sResponseType = 'WAR';
                    }                   

                    // increment the counter
                    if (isset($aResponse) && count((array)$aResponse) !== 0) {
                        $this->objOrders->saveOrderResponseData($aResponse, $ordid);
                    }

                    // increment the counter
                    if (count($aResponse) > 0) {
                        $iCount = $iCount + 1;
                    }
                }
            }
        } catch(Exception $e) {            
            $this->objErrorLog->saveLogsData('Orders-CRON', $e);
        }
        
        // if cron then log in database too..
        if ($isCron) {
            $this->objErrorLog->saveLogsData('Orders-CRON', "Total Yellowcube Orders created: " . $iCount, true);
        } else {
            return $iCount;
        }        
    }
    
    /**
     * Inserts Article Master data to Yellowcube     
     * 
     * @param string $sMode - Mode of handling
     *                        ax - Only active ones
     *                        ia - Only Inactive ones
     *                        xx - All articles    
     * @param string $sFlag - Type of action
     *                        Insert(I), 
     *                        Update(U), 
     *                        Deactivate/Delete(D)
     *
     * @return array
     */
    public function autoInsertArticles($sMode, $sFlag, $isCron = false)
    {        
        $iCount = 0;$where = '';

        // form where condition based on options...
        switch($sMode) {
        case "ax": $where = ' WHERE active = 1';
            break;
            
        case "ix": $where = ' WHERE active = 0';
            break;
            
        case "xx": $where = ' WHERE 1';
            break;
        }

        try {
            // get all the articles based on above condition...
            $aArticles  = Shopware()->Db()->fetchAll("SELECT `id` FROM `s_articles`" . $where);
            
            if (count($aArticles) > 0) {
                foreach ($aArticles as $article) {                    
                    $artid = $article['id'];
                    $oDetails = $this->objProduct->getArticleDetails($article['id'], true);
                    $iStatusCode = $this->getRecordedStatus($ordid, 'asign_yellowcube_product');

                    // if not 10 then insert the article
                    // execute the article object                    
                    if ($iStatusCode != 10) {
                        echo "Submitting Article for Article-ID: " . $artid . "\n";
                        $aResponse = $this->objYcubeCore->insertArticleMasterData($oDetails, $sFlag);
                    } elseif ($iStatusCode == 10 && $iStatusCode != 100) {
                        // get the status                                
                        echo "Getting Article status for Article-ID: " . $artid . "\n";
                        $aResponse = $this->objYcubeCore->getYCGeneralDataStatus($artid, "ART");
                    }

                    // increment the counter
                    if (count($aResponse) > 0) {
                        $this->objProduct->saveArticleResponseData($aResponse, $artid);
                        $iCount = $iCount + 1;
                    }
                }
            }
        } catch(Exception $e) {            
            $this->objErrorLog->saveLogsData('Articles-CRON', $e);
        }
        
        // if cron then log in database too..
        if ($isCron) {
            $this->objErrorLog->saveLogsData('Articles-CRON', "Total articles sent to Yellowcube: " . $iCount, true);
        } else {
            return $iCount;
        }         
    }

    /**
     * Returns inventory list from Yellowcube
     *
     * @internal param Object $oObject Active object
     *
     * @return array
     */
    public function autoFetchInventory($isCron = false)
    {
        try {            
            $aResponse = $this->objYcubeCore->getInventory();
            
            // update
            if (count($aResponse) > 0) {                
                $iCount = $this->objInventory->saveInventoryData($aResponse["data"]);    
            } 
        } catch(Exception $e) {
            $this->objErrorLog->saveLogsData('Inventory-CRON', $e);
        }
        
        // if cron then log in database too..
        if ($isCron) {
            $this->objErrorLog->saveLogsData('Inventory-CRON', "Total updated items: " . $iCount, true);
        } else {
            return $iCount;
        }
    }

    /**
     * Checks which step is the present step the order is in based on the filled database fields
     *
     * @param   $orderId
     * @return  string
     */
    protected function getOrderRequestField($orderId)
    {        
        $sResponseField = '';

        if ($this->objOrders->getFieldData($orderId, self::YCRESPONSE) == '') {
            $sResponseField = self::YCRESPONSE;
        } elseif ($this->objOrders->getFieldData($orderId, self::YCWABRESPONSE) == '') {
            $sResponseField = self::YCRESPONSE;
        } elseif ($this->objOrders->getFieldData($orderId, self::YCWARRESPONSE) == '') {
            $sResponseField = self::YCWABRESPONSE;
        }

        return $sResponseField;
    }

    /**
     * Returns status list from Yellowcube
     *
     * @param string $itemid item id
     * @param string $sTable Table name
     *
     * @param string null $sResponseType
     * @return string
     */
    protected function getRecordedStatus($itemid, $sTable, $sResponseField = null)
    {
        $oModel = $this->objProduct;
        if ($sTable == 'asign_yellowcube_orders') {
            $oModel = $this->objOrders;    
        }        
        $aParams = $oModel->getYellowcubeReport($itemid, $sTable, $sResponseField);

        return $aParams["StatusCode"];
    }
}
