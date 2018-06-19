<?php

/**
 * This file handles CRON related functions
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright asign
 * @license   https://www.a-sign.ch/
 * @version   2.1.3
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
    /** @var constants * */
    const YCRESPONSE = 'ycResponse';
    const YCWABRESPONSE = 'ycWabResponse';
    const YCWARRESPONSE = 'ycWarResponse';

    /** @var object * */
    protected $objErrorLog = null;

    /** @var object * */
    protected $objProduct = null;

    /** @var object * */
    protected $objOrders = null;

    /** @var object * */
    protected $objInventory = null;

    /** @var object * */
    protected $objYcubeCore = null;

    /**
     * Class constructor
     *
     * @return null
     */
    public function __construct()
    {
        $this->objYcubeCore = new AsignYellowcubeCore();
        $this->objErrorLog = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs");
        $this->objProduct = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Product\Product");
        $this->objOrders = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Orders\Orders");
        $this->objInventory = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Inventory\Inventory");
    }

    /**
     * Creates New customer Order in Yellowcube
     *
     * @param bool $isCron Called via cron
     *
     * @return array
     */
    public function autoSendYCOrders($isCron = false)
    {
        $iCount = 0;

        // check order status
        $sWhere = " and `status` != " . \Shopware\Models\Order\Status::ORDER_STATE_READY_FOR_DELIVERY .
            " and `status` != " . \Shopware\Models\Order\Status::ORDER_STATE_CANCELLED .
            " and `status` != " . \Shopware\Models\Order\Status::ORDER_STATE_CLARIFICATION_REQUIRED .
            " and `status` != " . \Shopware\Models\Order\Status::ORDER_STATE_COMPLETELY_DELIVERED;

        // check payment status
        $sWhere .= " and `paymentID` != 5 or (`paymentID` = 5 and `cleared` = 12)";

        $aOrders = Shopware()->Db()->fetchAll("select `id`, `paymentid`, cleared from `s_order` where `ordernumber` > 0" . $sWhere);

        if (count($aOrders) > 0) {
            foreach ($aOrders as $order) {
                try {
                    $iOrdid = $order['id'];

                    // check if the Status in the Order table
                    $sRequestField = $this->getOrderRequestField($iOrdid);
                    $iStatusCode = $this->getRecordedStatus($iOrdid, 'asign_yellowcube_orders', $sRequestField);
                    $aResponse = array('success' => false);

                    // get YC response
                    if (($iStatusCode == null || $iStatusCode == 101) && $this->objOrders->getFieldData($iOrdid, $sRequestField) == '') {
                        // execute the order object
                        echo "Submitting Order for OrderID: " . $iOrdid . "\n";
                        $oDetails = $this->objOrders->getOrderDetails($iOrdid);
                        $aResponse = $this->objYcubeCore->createYCCustomerOrder($oDetails);
                    } elseif ($iStatusCode < 100) {
                        // get the status
                        echo "Requesting WAB status for OrderID: " . $iOrdid . "\n";
                        $aResponse = $this->objYcubeCore->getYCGeneralDataStatus($iOrdid, "WAB");
                    } elseif ($iStatusCode == 100) {
                        // get the WAR status
                        echo "Requesting WAR status for OrderID: " . $iOrdid . "\n";
                        $aResponse = $this->objYcubeCore->getYCGeneralDataStatus($iOrdid, "WAR");
                    }

                    // increment the counter
                    if ($aResponse['success']) {
                        $this->objOrders->saveOrderResponseData($aResponse, $iOrdid);
                        $iCount++;
                    }

                } catch (Exception $e) {
                    $this->objErrorLog->saveLogsData('Orders-CRON', $e);
                }
            }
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
     * @return integer|void
     */
    public function autoInsertArticles($sMode, $sFlag, $isCron = false)
    {
        $iCount = 0;
        $where = '';

        // form where condition based on options...
        switch ($sMode) {
            case "ax":
                $where = ' WHERE active = 1';
                break;

            case "ix":
                $where = ' WHERE active = 0';
                break;

            case "xx":
                $where = ' WHERE 1';
                break;
        }

        // get all the articles based on above condition...
        $aArticles = Shopware()->Db()->fetchAll("SELECT `id` FROM `s_articles`" . $where);

        if (count($aArticles) > 0) {
            foreach ($aArticles as $article) {

                try {
                    $artid = $article['id'];
                    $aDetails = $this->objProduct->getArticleDetails($article['id'], true);
                    $iStatusCode = $this->getRecordedStatus($artid, 'asign_yellowcube_product');
                    $aResponse = array('success' => false);

                    // if not 10 then insert the article
                    // execute the article object
                    if ($iStatusCode != 10) {
                        echo "Submitting Article for Article-ID: " . $artid . "\n";
                        $aResponse = $this->objYcubeCore->insertArticleMasterData($aDetails, $sFlag);
                    } elseif ($iStatusCode == 10 && $iStatusCode != 100) {
                        // get the status
                        echo "Getting Article status for Article-ID: " . $artid . "\n";
                        $aResponse = $this->objYcubeCore->getYCGeneralDataStatus($artid, "ART");
                    }

                    // increment the counter
                    if ($aResponse['success']) {
                        $oResponse = $aResponse['data'];
                        $this->objProduct->saveArticleResponseData($oResponse, $artid);
                        $iCount++;
                    }
                } catch (\Exception $e) {
                    $this->objErrorLog->saveLogsData('Articles-CRON', $e);
                }
            }
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
            if ($aResponse['success']) {
                $iCount = $this->objInventory->saveInventoryData($aResponse["data"]);
            }
        } catch (Exception $e) {
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
