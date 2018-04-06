<?php
/**
 * This file extends Shopware_Components_Document class
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
 * @see       PdfaDocument
 * @since     File available since Release 1.0
 */

require_once(Shopware()->AppPath("Components") . "/Document.php");
require_once(Shopware()->AppPath("Components") . "/Translation.php");

/**
* Extends Shopware_Components_Document class
*
* @category Asign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
class PdfaDocument extends Shopware_Components_Document
{
	/**
     * Object from Type Model\Order
     *
     * @var object Model\Order
     */
    public $_order;

    /**
     * Shopware Template Object (Smarty)
     *
     * @var object
     */
    public $_template;

    /**
     * Shopware View Object (Smarty)
     *
     * @var object
     */
    public $_view;

    /**
     * Configuration
     * @var array
     */
    public $_config;

    /**
     * compatibilityMode = true means that html2ps will be used instead of mpdf.
     * Additionally old templatebase will be used (For pre 3.5 versions)
     *
     * Unsupported till shopware 4.0.0
     * @var bool
     * @deprecated
     */
    protected $_compatibilityMode = false;

    /**
     * Define output
     *
     * @var string html,pdf,return
     */
    protected $_renderer = "html";

    /**
     * Are properties already assigned to smarty?
     *
     * @var bool
     */
    protected $_valuesAssigend = false;

    /**
     * Subshop-Configuration
     *
     * @var array
     */
    public $_subshop;

    /**
     * Path to load templates from
     *
     * @var string
     */
    public $_defaultPath = "templates/_emotion";

    /**
     * Generate preview only
     *
     * @var bool
     */
    protected $_preview = false;

    /**
     * Typ/ID of document [0,1,2,3] - s_core_documents
     *
     * @var int
     */
    protected $_typID = 5;

    /**
     * Document-Metadata / Properties
     *
     * @var array
     */
    public $_document;

    /**
     * Invoice / Document number
     *
     * @var int
     */
    protected $_documentID;

    /**
     * Primary key of the created document row (s_order_documents)
     *
     * @var int
     */
    protected $_documentRowID;

    /**
     * Hash of the created document row (s_order_documents.hash), will be used as filename when preview is false
     *
     * @var string
     */
    protected $_documentHash;

    /**
     * Invoice ID for reference in shipping documents etc.
     *
     * @var string
     */
    protected $_documentBid;

    /**
     * Ref to the translation component
     *
     * @var \Shopware_Components_Translation
     */
    protected $translationComponent;

    /**
     * Start renderer / pdf-generation especially for PDFA
     *
     * @param string optional define renderer mostly an array
     * with orderid and renderer
     */
    public function pdfaRender($aRenderer = null)
	{
        // array of render and order-id data
        try {
            if ($aRenderer) {
                $_renderer = $aRenderer['render'];
                $orderID = $aRenderer['orderid'];
                $this->_preview = $aRenderer['preview'];
            }

            // set config details in session
            // get active Shop details
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $defaultShop = $repository->getDefault();
            $localISO = $defaultShop->getLocale()->getLocale();
            $shopID = $defaultShop->getId();

            // get Shop owner details: below ids are related to s_core_config_elements
            // and the values are stored in s_core_config_values..
            // 673 - shopName
            // 674 - mail
            // 675 - address
            // 676 - taxNumber
            // 677 - bankAccount
            // 893 - company
            $aShopData['shopname'] = $this->getConfigValue('shopName', $shopID);
            $aShopData['company'] = $this->getConfigValue('company', $shopID);
            $aShopData['mail'] = $this->getConfigValue('mail', $shopID);
            $aShopData['address'] = $this->getConfigValue('address', $shopID);
            $aShopData['taxnum'] = $this->getConfigValue('taxNumber', $shopID);
            $aShopData['bankaccnt'] = $this->getConfigValue('bankAccount', $shopID);
            $aShopData['country'] = $defaultShop->getLocale()->getTerritory();
            Shopware()->Session()->shopData = $aShopData;

            // get and set the countryId related to localeID
            $sCountISO = end(explode("_", $localISO));
            $localId = Shopware()->Db()->fetchOne("SELECT `id` FROM `s_core_countries` WHERE `countryiso` = '" . $sCountISO . "'");
            Shopware()->Session()->shopLocaleId = (int)$localId;

            // pluginpath assigned
            $sShopPath = $defaultShop->getHost();
            if ($defaultShop->getBasePath()) {
                $sShopPath .=  '/' . $defaultShop->getBasePath();
            }

            $pluginPath = 'http://' . $sShopPath . '/engine/Shopware/Plugins/Local/Backend/AsignYellowcube/Media/';
            Shopware()->Session()->pluginPath = $pluginPath;

            // start PDF-generation process
            $this->_renderer = $_renderer;
		    $mpdfPath = Shopware()->AppPath('Plugins') . "Local/Backend/AsignYellowcube/Helpers/Library/AsignMpdf/mpdf.php";
            include_once($mpdfPath);

            //$this->_view = new Enlight_Template_Manager();
            $this->initTemplateEngine();

            // set the translation component
            $this->translationComponent = new Shopware_Components_Translation();
            $this->setOrder(Enlight_Class::Instance('Shopware_Models_Document_Order', array($orderID, $config)));

            if (!empty($_renderer)) $this->_renderer = $_renderer;
            if ($this->_valuesAssigend == false) {
                $this->assignValues();
            }

            $sPdfTemplate = "engine/Shopware/Plugins/Local/Backend/AsignYellowcube/Views/";
	        $data = $this->_template->fetch($sPdfTemplate . "documents/index_yc.tpl", $this->_view);
            if ($this->_renderer == "html" || !$this->_renderer) {
                echo $data;
            } elseif ($this->_renderer == "pdf") {
                if ($this->_preview == true || !$this->_documentHash) {
                    $mpdf = new AsignMpdf("utf-8","A4","","helvetica",$this->_document["left"],$this->_document["right"],$this->_document["top"],$this->_document["bottom"]);
                    $mpdf->WriteHTML($data);
                    $mpdf->Output();
                    die;
                } else {
                    $path = Shopware()->OldPath()."files/documents"."/".$this->_documentHash.".pdf";
                    $mpdf = new AsignMpdf("utf-8","A4","","helvetica",$this->_document["left"],$this->_document["right"],$this->_document["top"],$this->_document["bottom"]);
                    $mpdf->WriteHTML($data);
                    $mpdf->Output($path, "F");

                    // update order_documents table for correct bill-number
                    $this->updateOrderBillNumber($orderID, $this->_documentHash);
                }
            }
        } catch (Exception $ex) {
            $oLogs = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs");
            $oLogs->saveLogsData('pdfaRender', $ex);
        }
	}

    /**
     * Update order billing number based on the orderid and hashid
     * since somehow the billing number is not updated
     *
     * @param int    $orderID order id
     * @param string $sHashID File hash-id
     */
    protected function updateOrderBillNumber($orderID, $sHashID)
    {
        // get the max current value in the list...
        $iMaxId = Shopware()->Db()->fetchOne("SELECT MAX(docID) FROM `s_order_documents`");
        $iMaxId = $iMaxId + 1;

        // update generated document details
        Shopware()->Db()->query("UPDATE `s_order_documents` SET `docID` = '" . $iMaxId . "' WHERE `orderID` = '" . $orderID . "' AND `hash` = '" . $sHashID . "' AND `docID` = 0");
    }

    /**
     * Return config value based on the name passed on and shopid
     *
     * @param string  $sElement Form Element name
     * @param integer $iShopid  Shop ID
     */
    protected function getConfigValue($sElement, $iShopid)
    {
        $sValue = Shopware()->Db()->fetchOne("SELECT `value` FROM `s_core_config_values` WHERE `element_id` = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = '" . $sElement . "') AND `shop_id` = '" . $iShopid . "'");
        $sValue = unserialize($sValue);

        return $sValue;
    }
}
