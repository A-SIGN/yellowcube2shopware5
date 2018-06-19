<?php
/**
 * This file defines the Backend controller
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright asign
 * @license   https://www.a-sign.ch/
 * @version   2.1.3
 * @link      https://www.a-sign.ch/
 * @see       Shopware_Controllers_Backend_AsignYellowcube
 * @since     File available since Release 1.0
 */

use Shopware\AsignYellowcube\Components\Api\AsignYellowcubeCore;
use Shopware\AsignYellowcube\Components\Api\AsignYellowcubeCron;

/**
 * Defines backend controller
 *
 * @category Asign
 * @package  AsignYellowcube
 * @author   entwicklung@a-sign.ch
 * @link     http://www.a-sign.ch
 */
class Shopware_Controllers_Backend_AsignYellowcube extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Returns stock value for the inventory
     *
     * @return integer value
     */
    protected $_iStockValue = null;

    /**
     * Returns repository for the model
     *
     * @return object
     */
    protected $repository = null;

    /**
     * Returns Plugin config values
     *
     * @return object
     */
    protected $pluginconfig = null;

    /**
     * Returns repository for passed model
     *
     * @param string $sModelName Model name
     *
     * @return object
     */
    public function getRepository($sModelName)
    {
        $sFinalModelName = "\\" . $sModelName . "\\" . $sModelName;
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository(
                "Shopware\CustomModels\AsignModels" . $sFinalModelName
            );
        }

        return $this->repository;
    }

    /**
     * Returns plugin config
     *
     * @return object
     */
    public function getPluginConfig()
    {
        if ($this->pluginconfig === null) {
            $this->pluginconfig = Shopware()->Plugins()->Backend()->AsignYellowcube()->Config();
        }

        return $this->pluginconfig;
    }

    /**
     * Returns all the products based on filter or sort.
     *
     * @return array
     */
    public function getProductsAction()
    {
        $filter = $this->Request()->getParam('filter');
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);
        $sort = $this->Request()->getParam('sort');

        $query = $this->getRepository('Product')->getProductsListQuery(
            $filter, $sort, $offset, $limit
        );

        // set the paginator and result
        $paginator = new Zend_Paginator_Adapter_DbSelect($query);
        $totalCount = $paginator->count();
        $result = $paginator->getItems($offset, $limit);

        $data = array();
        foreach ($result as $key => $product) {
            // initialize blhasdetails
            $product['blhasdetails'] = false;

            // get the sps params
            $aData = unserialize($product['ycSpsDetails']);

            // set blhasdetails
            $product['blhasdetails'] = ($aData !== false) ? true : false;
            unset($aData['id']); // remove empty-id value

            // get response first
            $aRsp = unserialize($product['ycResponse']);
            if ($aRsp !== false) {
                $aData['isaccepted'] = $this->isResponseAccepted($aRsp);
                $aData['ycResponse'] = $this->getJsonEncodedData($aRsp);
            }

            // merge if data
            if ($aData !== false) {
                // set blhasdetails
                $product['blhasdetails'] = true;
                $product = array_merge($product, $aData);
            }

            $data[] = $product;
        }

        $this->View()->assign(array('data' => $data, 'success' => true, 'total' => $totalCount));
    }

    /**
     * Returns all the orders based on filter or sort.
     *
     * @return array
     */
    public function getOrdersAction()
    {
        $filter = $this->Request()->getParam('filter');
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);
        $sort = $this->Request()->getParam('sort');

        $query = $this->getRepository('Orders')->getOrdersListQuery(
            $filter, $sort, $offset, $limit
        );

        // is the manual order sending enabled?
        $isManual = $this->getPluginConfig()->blYellowCubeOrderManualSend;

        // set the paginator and result
        $paginator = new Zend_Paginator_Adapter_DbSelect($query);
        $totalCount = $paginator->count();
        $result = $paginator->getItems($offset, $limit);

        $data = array();
        foreach ($result as $key => $order) {
            // frame serialized responses
            $aResponse = unserialize($order["ycResponse"]);
            $order['ycResponse'] = $this->getJsonEncodedData($aResponse);

            // get EORI data
            $order['eori'] = $this->getRepository('Orders')->getOrderEoriNumber($order['userID']);

            // WAB response
            $aWabResponse = unserialize($order["ycWabResponse"]);
            $order['iswabaccepted'] = (isset($aResponse['StatusType']) && $aResponse['StatusType'] === 'S' && $aResponse['StatusCode'] == 10) ? 1 : 0;
            $order['iswaraccepted'] = (isset($aWabResponse['StatusType']) && $aWabResponse['StatusType'] === 'S' && $aWabResponse['StatusCode'] == 100) ? 1 : 0;
            $order['ycWabResponse'] = $this->getJsonEncodedData($aWabResponse);

            //modified version of the WAR response, since it has items information
            $warResponse = unserialize($order["ycWarResponse"]);
            $warResponse = $warResponse[WAR]->GoodsIssue;

            $warMergeData['GoodsIssueHeader'] = (array)$warResponse->GoodsIssueHeader;
            $warMergeData['CustomerOrderHeader'] = (array)$warResponse->CustomerOrderHeader;

            $aResponseItems = null;
            $customerOrderDetail = (array)$warResponse->CustomerOrderList->CustomerOrderDetail;
            $BVPosNo = $customerOrderDetail['BVPosNo'];

            // if the count of the array is only one? decide by finding the first element
            if ($BVPosNo) {
                $aResponseItems[0] = $customerOrderDetail;
            } else {
                foreach ($customerOrderDetail as $items) {
                    $aResponseItems[] = (array)$items;
                }
            }
            $warMergeData['CustomerOrderList'] = $aResponseItems;
            $order['ycWarResponse'] = json_encode($warMergeData);
            $order['ycWarCount'] = count($aResponseItems);
            $order['ismanual'] = $isManual;

            $data[] = $order;
        }

        $this->View()->assign(array('data' => $data, 'success' => true, 'total' => $totalCount));
    }

    /**
     * Returns all the inventory based on filter or sort.
     *
     * @return array
     */
    public function getInventoryAction()
    {
        $filter = $this->Request()->getParam('filter');
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);
        $sort = $this->Request()->getParam('sort');

        $query = $this->getRepository('Inventory')->getInventoryListQuery(
            $filter, $sort, $offset, $limit
        );

        // set the paginator and result
        $paginator = new Zend_Paginator_Adapter_DbSelect($query);
        $totalCount = $paginator->count();
        $result = $paginator->getItems($offset, $limit);

        $data = array();
        foreach ($result as $key => $inventory) {
            // unserialize and get values
            $inventory['additional'] = $this->getJsonEncodedData(unserialize($inventory["additional"]));
            $inventory['stockvalue'] = $this->_iStockValue;

            $data[] = $inventory;
        }

        $this->View()->assign(array('data' => $data, 'success' => true, 'total' => $totalCount));
    }

    /**
     * Returns all the logs based on filter or sort.
     *
     * @return array
     */
    public function getLogsAction()
    {
        $filter = $this->Request()->getParam('filter');
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 100);
        $sort = $this->Request()->getParam('sort');

        $query = $this->getRepository('Errorlogs')->getLogsListQuery(
            $filter, $sort, $offset, $limit
        );

        // set the paginator and result
        $paginator = new Zend_Paginator_Adapter_DbSelect($query);
        $totalCount = $paginator->count();
        $result = $paginator->getItems($offset, $limit);

        $data = array();
        foreach ($result as $key => $logs) {
            $data[] = $logs;
        }

        $this->View()->assign(array('data' => $data, 'success' => true, 'total' => $totalCount));
    }

    /**
     * Saves additional information related to product
     *
     * @return array
     */
    public function createAdditionalsAction()
    {
        // get update id...
        $updateId = $this->Request()->getParam('id');
        $articleId = $this->Request()->getParam('artid');
        try {
            $aParams = array(
                'id'          => $this->Request()->getParam('id'),
                'batchreq'    => (int)$this->Request()->getParam('batchreq'),
                'noflag'      => (int)$this->Request()->getParam('noflag'),
                'incesd'      => (int)$this->Request()->getParam('incesd'),
                'expdatetype' => $this->Request()->getParam('expdatetype'),
                'altunitiso'  => $this->Request()->getParam('altunitiso'),
                'eantype'     => $this->Request()->getParam('eantype'),
                'netto'       => $this->Request()->getParam('netto'),
                'brutto'      => $this->Request()->getParam('brutto'),
                'length'      => $this->Request()->getParam('length'),
                'width'       => $this->Request()->getParam('width'),
                'height'      => $this->Request()->getParam('height'),
                'volume'      => $this->Request()->getParam('volume'),
                'createDate'  => date('Y-m-d'),
            );
            $sParams = serialize($aParams); // serialize and save

            // internation information
            $aIntHandling = array(
                'tariff' => $this->Request()->getParam('tariff'),
                'tara'   => (double)$this->Request()->getParam('tara'),
                'origin' => $this->Request()->getParam('origin'),
            );

            // save the details
            $this->getRepository('Product')->saveAdditionalData($sParams, $updateId, $articleId, $aIntHandling);

            $this->View()->assign(array('success' => true));
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Sends single article to Yellowcube based on options
     *
     * @return void
     */
    public function sendArticlesAction()
    {
        // define parameters
        $iArtId = $this->Request()->getParam('artid');
        $sMode = $this->Request()->getParam('mode');

        // get article information based on ID
        $oProductRepository = $this->getRepository('Product');
        $aArticles = $oProductRepository->getArticleDetails($iArtId);

        // check if ESD available?
        $blAllowEsd = true;
        if ($aArticles['esdid'] > 0) {
            $blAllowEsd = false; // set false if ESD is present
        }

        if ($aArticles['ycparams']['incesd']) {
            $blAllowEsd = true; // set true if ESD is allowed
        }

        try {
            if ($blAllowEsd) {
                $oYCube = new AsignYellowcubeCore();

                if ($sMode === "S") {
                    $aResponse = $oYCube->getYCGeneralDataStatus($iArtId, "ART");
                } else {
                    $aResponse = $oYCube->insertArticleMasterData($aArticles, $sMode);
                }

                $iStatusCode = -1;
                $blSuccess = $aResponse['success'];

                if ($blSuccess) {
                    $oResponse = $aResponse['data'];
                    $iStatusCode = $oResponse->StatusCode;
                }

                // save in database
                if ($blSuccess || $iStatusCode === 100) {
                    // log it event if its success / failure
                    $oProductRepository->saveArticleResponseData($oResponse, $iArtId);

                    // get the serialized response
                    $sTmpResult = $this->getSerializedResponse($oResponse); // to override the content

                    $this->View()->assign(
                        array(
                            'success'    => true,
                            'mode'       => $sMode,
                            'dataresult' => $sTmpResult,
                            'statcode'   => $iStatusCode,
                        )
                    );
                } else {
                    $this->View()->assign(
                        array(
                            'success' => false,
                            'code'    => -1,
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Creates Order into Yellowcube datastore
     *
     * @return void
     */
    public function createOrderAction()
    {
        try {
            // define parameters
            $iOrderId = $this->Request()->getParam('ordid');
            $sMode = $this->Request()->getParam('mode');

            // get order repository
            $oOrderRepository = $this->getRepository('Orders');

            $oYCube = new AsignYellowcubeCore();

            if ($sMode) {
                $aResponse = $oYCube->getYCGeneralDataStatus($iOrderId, $sMode);
            } else {
                $aOrders = $oOrderRepository->getOrderDetails($iOrderId);
                $aResponse = $oYCube->createYCCustomerOrder($aOrders);
            }

            $oResponse = $aResponse['data'];

            // check if any zip code error is linked?
            if ($oResponse->zcode) {
                $this->View()->assign(
                    array(
                        'success' => false,
                        'code'    => $oResponse->zcode,
                        'message' => $aResponse['message'],
                    )
                );
            } else {
                // get the serialized response
                $sTmpResult = $this->getSerializedResponse($oResponse); // to override the content

                // log the response whether S or E
                $oOrderRepository->saveOrderResponseData($aResponse, $iOrderId, $sMode);

                $blStatusMsg = $aResponse['success'];
                $sStatusType = $oResponse->StatusType;
                $iStatusCode = $oResponse->StatusCode;

                // save in database
                if ($blStatusMsg || $sStatusType === 'S' || $iStatusCode === 100) {
                    $this->View()->assign(
                        array(
                            'success'    => true,
                            'dcount'     => 1,
                            'mode'       => $sMode,
                            'dataresult' => $sTmpResult,
                            'statcode'   => $iStatusCode,
                        )
                    );
                } else {
                    $this->View()->assign(
                        array(
                            'success' => false,
                            'code'    => -1,
                        )
                    );
                }
            }
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Saves EORI information for selected order
     *
     * @return array
     */
    public function saveEoriAction()
    {
        try {
            $orderId = $this->Request()->getParam('ordid');
            $eoriNumber = $this->Request()->getParam('eori');

            // save the details
            $oModel = $this->getRepository('Orders');
            $oModel->saveOrderEoriNumber($orderId, $eoriNumber);

            $this->View()->assign(array('success' => true));
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Function to send only prepaid orders
     *
     * @return void
     */
    public function sendPrepaidAction()
    {
        try {
            $oYCron = new AsignYellowcubeCron();
            $iCount = $oYCron->autoSendYCOrders();

            // save in database
            if ($iCount > 0) {
                $this->View()->assign(
                    array(
                        'success' => true,
                        'dcount'  => $iCount,
                    )
                );
            } else {
                $this->View()->assign(
                    array(
                        'success' => false,
                        'code'    => -1,
                    )
                );
            }
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Function to send active/inactive articles
     *
     * @return void
     */
    public function sendProductAction()
    {
        try {
            $oYCron = new AsignYellowcubeCron();
            $sMode = $this->Request()->getParam("optmode");
            $sFlag = $this->getPluginConfig()->sCronArtFlag;
            $iCount = $oYCron->autoInsertArticles($sMode, $sFlag);

            // save in database
            if ($iCount > 0) {
                $this->View()->assign(
                    array(
                        'success' => true,
                        'dcount'  => $iCount,
                    )
                );
            } else {
                $this->View()->assign(
                    array(
                        'success' => false,
                        'code'    => -1,
                    )
                );
            }
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Updates inventory list by sending request to yellowcube
     *
     * @return void
     */
    public function updateListAction()
    {
        try {
            $oYCube = new AsignYellowcubeCore();
            $aResponse = $oYCube->getInventory();

            // save in database
            if ($aResponse['success']) {
                $oModel = $this->getRepository('Inventory');
                $iCount = $oModel->saveInventoryData($aResponse['data']);

                $this->View()->assign(
                    array(
                        'success' => true,
                        'dcount'  => $iCount,
                    )
                );
            } else {
                $this->View()->assign(
                    array(
                        'success' => false,
                        'code'    => -1,
                    )
                );
            }
        } catch (\Exception $e) {
            $this->View()->assign(
                array(
                    'success' => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * Performs CRON based YC actions
     *
     * @return null
     */
    public function cronAction()
    {
        $aParams = explode(';', $this->Request()->getParam('opt'));
        $dbHashValue = $this->getPluginConfig()->sYellowCubeCronHash;

        if ($this->Request()->getParam('hash') !== $dbHashValue) {
            header('HTTP/1.0 403 Forbidden');
            die('<h1>Forbidden</h1>You are not allowed to access this file!!');
        }

        /**
         * Options for script:
         *
         * co - Create YC Customer Order
         * ia - Insert Article Master Data
         *      ax  - Include only active
         *      ix    - Include only inactive
         *      xx  - Include all
         *      I   - Insert article to yellowcube
         *      U   - Update article to yellowcube
         *      D   - Delete article from yellowcube
         * gi - Get Inventory
         */
        $command = reset($aParams);
        $oYCron = new AsignYellowcubeCron();
        switch ($command) {
            case 'co':
                $oYCron->autoSendYCOrders();
                break;

            case 'ia':
                // applicable only for articles...
                $sMode = $aParams[1];// ax, ix, xx
                $sFlag = $aParams[2];//I, U, D

                // if no flags specified then use from module settings
                if ($sFlag == "") {
                    $sFlag = "I";
                }
                $oYCron->autoInsertArticles($sMode, $sFlag);
                break;

            case 'gi':
                $oYCron->autoFetchInventory();
                break;

            default:
                echo "No options specified...";
                break;
        }
    }

    /**
     * Retuns unserialized, reversed and json_encoded data
     * for showing on views.
     *
     * @param array $aData - Array of data
     *
     * @return string
     */
    protected function getJsonEncodedData($aData)
    {
        $aData = array_reverse($aData); // put in reverse order
        $jsonData = json_encode($aData); // encode as JSON data

        $this->_iStockValue = $aData['QuantityUOM']; // stock value is set

        return $jsonData;
    }

    /**
     * Checks if the response is finalized. Check for code=100
     *
     * @param string $aData - Array of Data
     *
     * @return string
     */
    protected function isResponseAccepted($aData)
    {
        $sCode = $aData['StatusCode'];
        $sType = $aData['StatusType'];

        if ($sType === 'S' && $sCode === 100) {
            return 1;
        } elseif ($sType === 'S' && $sCode === 10) {
            return 2;
        }

        return 0;
    }

    /**
     * Filter and send serialized data
     *
     * @param array $aResponse - Array of Response data
     *
     * @return string
     */
    protected function getSerializedResponse($aResponse)
    {
        $aResponse = (array)$aResponse;
        $aResponse = array_reverse($aResponse);// reverse the array

        return json_encode($aResponse);
    }
}
