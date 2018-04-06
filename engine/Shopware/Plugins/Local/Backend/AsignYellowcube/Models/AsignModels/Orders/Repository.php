<?php
/**
 * This file defines data repository for Orders
 *
 * PHP version 5
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1
 * @link      https://www.a-sign.ch/
 * @since     File available since Release 1.0
 */

namespace Shopware\CustomModels\AsignModels\Orders;
use Shopware\Components\Model\ModelRepository;

/**
* Defines repository for Orders
*
* @category A-Sign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
class Repository extends ModelRepository
{
    /**
     * BS Tracking code value set
     *
     * @param \DateTime $createDate
     */
    protected $_bIsTrackingResponse = false;

    /**
     * Getter method for the boolean status variable
     *
     * @return bool
     */
    protected function isTrackingNrResponse()
    {
        return $this->_bIsTrackingResponse;
    }

    /**
     * Returns all the orders based on filter or sort.
     *
     * @param array   $filters Filters
     * @param integer $sort    Sort value
     * @param integer $offset  Offset value
     * @param integer $limit   Limit value
     *
     * @return array
     */
    public function getOrdersListQuery($filters, $sort, $offset = 0, $limit = 100)
    {
        $sOrdersColumns = array('ordid' => 'id', 'orderNumber'=>'ordernumber','amount'=>'invoice_amount', 'amountNet'=>'invoice_amount_net','timestamp'=>'ordertime', 'userID');
        $sYcubeOrderCols = array('id','lastSent','ycReference','ycResponse','ycWabResponse','ycWarResponse');
        $select = Shopware()->Db()->select()
            ->from('s_order', $sOrdersColumns)
            ->joinLeft('s_core_paymentmeans', 's_order.paymentID = s_core_paymentmeans.id', array('payment' => 'description'))
            ->joinLeft('s_premium_dispatch', 's_order.dispatchID = s_premium_dispatch.id', array('shipping' => 'name'))
            ->joinLeft('s_core_states', 's_order.status = s_core_states.id', array('status' => 'description'))
            ->joinLeft('asign_yellowcube_orders', 'asign_yellowcube_orders.ordid = s_order.id', $sYcubeOrderCols)
            ->where('s_order.ordernumber > 0');

        //If a filter is set
        if ($filters) {
            foreach ($filters as $filter) {
                $select->andWhere('s_order.ordernumber LIKE ?', '%' . $filter["value"] . '%');
                $select->orWhere('s_premium_dispatch.name LIKE ?', '%' . $filter["value"] . '%');
                $select->orWhere('s_core_paymentmeans.description LIKE ?', '%' . $filter["value"] . '%');
            }
        }

        // add sorting features...
        if ($sort) {
            $sorting = reset($sort);
            $column = $sorting['property'];
            $direction = $sorting['direction'];

            switch ($column) {
                case 'timestamp':
                    $select->order('s_order.ordertime ' . $direction);
                    break;
                case 'orderNumber':
                    $select->order('s_order.ordernumber ' . $direction);
                    break;
                case 'amount':
                    $select->order('s_order.invoice_amount ' . $direction);
                    break;
                case 'payment':
                    $select->order('s_core_paymentmeans.description ' . $direction);
                    break;
                case 'shipping':
                    $select->order('s_premium_dispatch.name ' . $direction);
                    break;
                case 'status':
                    $select->order('s_core_states.description ' . $direction);
                    break;
                case 'ycReference':
                    $select->order('asign_yellowcube_orders.ycReference ' . $direction);
                    break;
                default:
                    $select->order('s_order.ordertime ' . $direction);
            }
        } else {
            $select->order('s_order.ordernumber DESC');
        }

        return $select;
    }

    /**
     * Returns EORI for userid
     *
     * @param integer $userID user Id
     *
     * @return string
     */
    public function getOrderEoriNumber($userID)
    {
        $sEori = Shopware()->Db()->fetchOne("select `text1` from `s_user_billingaddress_attributes` where `billingID` = (select `id` from `s_user_billingaddress` where `userID` = '" . $userID . "')");

        return $sEori ? $sEori : "-";
    }

    /**
     * Updates Tara, Tariff and Origin details
     *
     * @param array $orderArticles Order articles
     *
     * @return null
     */
    public function updateHandlingInfo($orderArticles)
    {
        $oProduct = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Product\Product");
        foreach ($orderArticles as $article) {
            $aHandling = $oProduct->getHandlingInfo($article['articleID']);
            $this->updateOrderArticlesHandlingInfo($aHandling, $article['articleID']);
        }
    }

    /**
     * Update order articles based on artid
     *
     * @param array $aIntHandling array of handling data
     * @param integer $articleID article item id
     *
     * @return null
     */
    public function updateOrderArticlesHandlingInfo($aIntHandling, $articleID)
    {
        if ($articleID > 0) {
            /// sinc it has to be displayed on invoice change to countryname..
            $localeRepo = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getDefault();
            $localeId= $localeRepo->getLocale()->getId();

            // update query executed.
            $sCountry = Shopware()->Db()->fetchOne("select `countryiso` from `s_core_countries` where `id` = '" . $aIntHandling['origin'] . "'");
            $sOrdArtQuery = "update `s_order_details` set `tariff` = '" . $aIntHandling['tariff'] . "', `tara` = '" . $aIntHandling['tara'] . "', `origin` = '" . $sCountry . "' where `articleID` = '" . $articleID . "'";

            Shopware()->Db()->query($sOrdArtQuery);
        }
    }

    /**
     * Returns order data based on ordid
     *
     * @param integer $ordid    order item id
     * @param boolean $isDirect if direct from CO
     * @param bool    $artid    if its a CRON
     *
     * @return array
     */
    public function getOrderDetails($ordid, $isDirect = false, $isCron = false)
    {
        // get order details based on query
        $sSql = "SELECT so.id as ordid, so.ordernumber as ordernumber, so.ordertime as ordertime, so.paymentID as paymentid, so.dispatchID as dispatchid, sob.salutation as sal, sob.company, sob.department, CONCAT(sob.firstname, ' ', sob.lastname) as fullname, sob.street as streetinfo, sob.zipcode as zip, sob.city as city, scc.countryiso as country, su.email as email, spd.comment as shipping, scl.locale as language";
        $sSql .= " FROM s_order so";
        $sSql .= " JOIN s_order_shippingaddress sob ON so.id = sob.orderID";
        $sSql .= " JOIN s_core_countries scc ON scc.id = sob.countryID";
        $sSql .= " JOIN s_user su ON su.id = so.userID";
        $sSql .= " JOIN s_premium_dispatch spd ON so.dispatchID = spd.id";
        $sSql .= " JOIN s_core_locales scl ON so.language = scl.id";

        // cron?
        if ($isCron) {
            $sSql .= " JOIN asign_yellowcube_orders aso ON so.id = aso.ordid";
        }

        // if directly from Thank you page
        if ($isDirect) {
            $sSql .= " WHERE so.ordernumber = '" . $ordid . "'";
        } else {
            $sSql .= " WHERE so.id = '" . $ordid . "'";
        }

        // cron?
        if ($isCron) {
            $sSql .= " AND aso.ycReference = 0";
        }

        $aOrders = Shopware()->Db()->fetchRow($sSql);
        $orderId = $aOrders['ordid'];

        // get order article details
        $aOrders['orderarticles'] = Shopware()->Db()->fetchAll("SELECT `articleID`, `articleordernumber`, `name`, `quantity`, `ean` FROM `s_order_details` WHERE `orderID` = '" . $orderId . "' AND `articleID` <> 0");

        return $aOrders;
    }

    /**
     * Function to save the Response
     * received from Yellowcube. Modes included:
     * WAB, WAR, DC = Direct Call
     *
     * @param array $aResponseData Array of response
     * @param string $ordid        Order id
     * @param string $mode         Mode of transfer
     *
     * @return null
     */
    public function saveOrderResponseData($aResponseData, $ordid, $mode = null)
    {
        // based on mode switch the response
        try{
            if ($mode !== null) {
                // if direct then?
                if ($mode === 'DC') {
                    $clrResponse = $aResponseData['data'];
                    $sColumn = 'ycResponse';
                } else {
                    $clrResponse = $aResponseData;
                    if ($mode === 'WAB') {
                        $sColumn = 'ycWabResponse';
                    } elseif ($mode === 'WAR') {
                        $sColumn = 'ycWarResponse';
                    }
                }
            } else {
                $clrResponse = $aResponseData['data'];
                $sColumn = 'ycResponse';
            }

            // format as object2array
            $clrResponse = (array)$clrResponse;
            if (count($clrResponse) > 0) {
                // if response is not "E" then?
                if ($clrResponse['StatusType'] !== 'E') {
                    $sReference = ", `ycReference` = '" . $clrResponse['Reference'] . "'";
                }

                // push in db..
                $sData = serialize($clrResponse);
                $sWhere = " where `ordid` = '" . $ordid . "'";

                // update reference number, but first check if alreay entry?
                $iCount = Shopware()->Db()->fetchOne("select count(*) from `asign_yellowcube_orders` where `ordid` = '" . $ordid . "'");
                // if present then?
                if ($iCount) {
                    $sQuery = "update `asign_yellowcube_orders` set `" . $sColumn . "` = '" . $sData . "'" . $sReference . $sWhere;
                } else {
                    $sQuery = "insert into `asign_yellowcube_orders` set `ordid` = '" . $ordid . "', `lastSent` = 1, `" . $sColumn . "` = '" . $sData ."'" . $sReference;
                }
                Shopware()->Db()->query($sQuery);

                // update tracking code in s_order table
                if ($mode === 'WAR') {
                    $sTrackingCode = $aResponseData[WAR]->GoodsIssue->CustomerOrderHeader->PostalShipmentNo;
                    Shopware()->Db()->query("update `s_order` set `trackingcode` = '" . $sTrackingCode . "' where `id` = '" . $ordid . "'");

                    $this->_bIsTrackingResponse = true;
                }
            }
        } catch(Exception $e) {
            $oLogs = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs");
            $oLogs->saveLogsData('saveOrderResponseData', $e);
        }
    }

    /**
     * Getter method field data based on id
     *
     * @return string
     */
    public function getFieldData($oId, $sField)
    {
        return Shopware()->Db()->fetchOne("select `" . $sField . "` from `asign_yellowcube_orders` where `ordid` = '" . $oId . "'");
    }

    /**
     * Returns saved status from the saved data
     *
     * @param string $itemId Item id
     * @param string $sTable Table name
     *
     * @param string $sColumn
     * @return array
     */
    public function getYellowcubeReport($itemId, $sTable, $sColumn = 'ycResponse')
    {
        $sQuery = "select `" . $sColumn . "` from `" . $sTable . "` where `ordid` = '" . $itemId ."'";
        $aComplete = Shopware()->Db()->fetchOne($sQuery);
        $aResponse = unserialize($aComplete);

        $aReturn = array();
        if (!empty($aResponse)) {
            foreach ($aResponse as $key => $result) {
                $aReturn[$key] = $result;
            }
        }

        return $aReturn;
    }
}
