<?php

/**
 * This file handles Yellowcube related functions
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
 * @see       AsignYellowcubeCore
 * @since     File available since Release 1.0
 */

namespace Shopware\AsignYellowcube\Components\Api;

use Shopware\AsignYellowcube\Helpers\ApiClasses\AsignSoapClientApi;

/**
* Handles Yellowcube related functions
*
* @category Asign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
class AsignYellowcubeCore
{
    /**
     * List of the countries to be verified
     * for zip address.
     * Key = countryiso, value = digits
     *
     * @return array
     */
    protected $aCountryVsZip= array('CH' => 4, 'DE' => 5);

    /**
     * List of Language based Salutations
     * Key = oxidval; value = translatedval
     *
     * @return array
     */
    protected $aSaluationsDE= array('MR' => 'Herr',     'MS' => 'Frau', 'MRS' => 'Frau',    'COMPANY' => 'Firma');
    protected $aSaluationsEN= array('MR' => 'Mr.',      'MS' => 'Frau', 'MRS' => 'Ms.',     'COMPANY' => 'Company');
    protected $aSaluationsIT= array('MR' => 'Signore',  'MS' => 'Frau', 'MRS' => 'Signora', 'COMPANY' => 'Ditta');
    protected $aSaluationsFR= array('MR' => 'Monsieur', 'MS' => 'Frau', 'MRS' => 'Madame',  'COMPANY' => 'Société');

    /**
     * Object variable for this class
     *
     * @var object
     */
    private $oSoapApi;
    private $oLogs;

    /**
     * Constructor for this class
     *
     * @return \AsignYellowCubeCore
     */
    public function __construct()
    {
        $this->oSoapApi = new AsignSoapClientApi();
        $this->oLogs = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs");
    }

    /**
	 * Returns timestamp value with date and time
	 * Default Format: YmdHis
	 *
	 * @param string $sFormat Format of Timestamp
	 *
	 * @return string
	 */
    protected function generateTimestampValue($sFormat = 'YmdHis')
    {
        return date($sFormat);
    }

	/**
	 * Returns initial parameters used for request
	 * Includes: Type, Sender, Receiver,Timestamp,
	 * 			 OperatingMode, Version, CommType
	 *
	 * @param string $sType Type of request sent
	 * E.g. WAB, ART, BAR, WAR
	 *
	 * @return array
	 */
    public function getInitialParams($sType)
    {
        $aParams = array(
					'Type' 			=> $sType,
					'Sender'		=> $this->oSoapApi->getSoapWsdlSender(),
					'Receiver'		=> $this->oSoapApi->getSoapWsdlReceiver(),
					'Timestamp'		=> (float)$this->generateTimestampValue(),//20141017000020,
					'OperatingMode'	=> $this->oSoapApi->getSoapOperatingMode(),
					'Version'		=> $this->oSoapApi->getSoapVersion(),
					'CommType'		=> $this->oSoapApi->getCommType()
				);

		return $aParams;
    }

    /**
     * Returns inventory list from Yellowcube
     *
     * @internal param Object $oObject Active object
     *
     * @return array
     */
	public function getInventory()
	{
        // get initial params...
        $aParams = $this->getInitialParams("BAR");

		$oObject = new \stdClass();
        $oObject->ControlReference = new \stdClass();
		foreach ($aParams as $key => $param) {
            $oObject->ControlReference->$key = $param;
        }

        // try importing inventory data...
        try{
            $aResponse = $this->oSoapApi->callFunction("GetInventory", $oObject);
            return(array(
                            'success'   => true,
                            'data'      => $aResponse
                        ));
        } catch(Exception $soapex) {
            $this->oLogs->saveLogsData('getInventory', $soapex);
			throw new Exception($soapex->getMessage());
            return(array(
                            'success' => false,
                            'message' => $soapex->getMessage()
                        ));
        }
	}

    /**
     * Inserts article into YC master data
     *
     * @param array  $oArticle Article data
     * @param string $sFlag    Mode i.e. I,U,D
     *
     * @return object
     */
    public function insertArticleMasterData($aArticle, $sFlag)
    {
        // get the formatted article data
        $oRequestData = $this->getYCFormattedArticleData($aArticle, $sFlag);

        try{
            $aResponse = $this->oSoapApi->callFunction("InsertArticleMasterData", $oRequestData);

            return(array(
                            'success'   => true,
                            'data'      => $aResponse
                        ));
        } catch(Exception $soapex) {
            $this->oLogs->saveLogsData('insertArticleMasterData', $soapex);
            throw new Exception($soapex->getMessage());
            return(array(
                            'success' => false,
                            'message' => $soapex->getMessage()
                        ));
        }
    }

    /**
     * Returns status for both order/article from Yellowcube
     *
     * @param integer $itemId Order/Article ID
     * @param string  $sType Defines if its WAB or ART
     *
     * @return array
     */
    public function getYCGeneralDataStatus($itemId, $sType)
    {
        // define params
        $aParams         = $this->getInitialParams($sType);
        $aFunc["ART"]    = "GetInsertArticleMasterDataStatus";
        $aFunc["WAB"]    = "GetYCCustomerOrderStatus";
        $aFunc["WAR"]    = "GetYCCustomerOrderReply";

        $oObject = new \stdClass();
        $oObject->ControlReference = new \stdClass();
        foreach ($aParams as $key => $param) {
            $oObject->ControlReference->$key = $param;
        }

        // if customer order reply then?
        if ($sType == "WAR") {
            // add Max Wait Time...
            $oObject->ControlReference->TransMaxWait = $this->oSoapApi->getTransMaxTime();

            // get Reference number for the YC status
            $oObject->CustomerOrderNo = $this->getYCReferenceNumber($itemId, $sType);
        } elseif ($sType == "ART" || $sType == "WAB") {
            // get Reference number for the YC status
            $oObject->Reference = $this->getYCReferenceNumber($itemId, $sType);
        }

        // ping and get response...
        try{
            $aResponse = $this->oSoapApi->callFunction($aFunc[$sType], $oObject);
            return $aResponse;
        } catch(Exception $soapex) {
            $this->oLogs->saveLogsData('getYCGeneralDataStatus', $soapex);
            return(array(
                            'success' => false,
                            'message' => $soapex->getMessage()
                        ));
        }
    }

    /**
     * Returns array of YC details
     *
     * @param string $iValue - Article Id
     *
     * @return array
     */
    protected function getYCParams($iValue)
    {
        $oModel = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Product\Product");
        $aParams = $oModel->getYCDetailsForThisArticle($iValue);

        return $aParams;
    }

    /**
     * Returns the articles details in object form
     *
     * @param array  $oArticle Article data
     * @param string $sFlag    Mode i.e. I,U,D
     *
     * @return object
     */
    protected function getYCFormattedArticleData($aArticle, $sFlag)
    {
        // define params needed
        $aYCParams          = $aArticle['ycparams'];
        $aExpDateType       = array('Ignore' => 0,'Wocht' => 1,'Monat' => 2,'Jahr' => 3);
        $sExpVal            = $aYCParams['expdatetype'];
        $sExpDateType       = $aExpDateType[$sExpVal];

        $sDepoNumber        = $this->oSoapApi->getYCDepositorNumber();
        $sPlantID           = $this->oSoapApi->getYCPlantId();
        $sMinRemLife        = $this->oSoapApi->getTransMaxTime();

        $sNWeightISO        = $aYCParams['netto'] ? $aYCParams['netto'] : $this->oSoapApi->getYCNetWeightISO();
        $sGWeightISO        = $aYCParams['brutto'] ? $aYCParams['brutto'] : $this->oSoapApi->getYCGWeightISO();
        $sLengthISO         = $aYCParams['length'] ? $aYCParams['length'] : $this->oSoapApi->getYCLengthISO();
        $sWidthISO          = $aYCParams['width'] ? $aYCParams['width'] : $this->oSoapApi->getYCWidthISO();
        $sHeightISO         = $aYCParams['height'] ? $aYCParams['height'] : $this->oSoapApi->getYCHeightISO();
        $sVolumeISO         = $aYCParams['volume'] ? $aYCParams['volume'] : $this->oSoapApi->getYCVolumeISO();
        $sEANType           = $aYCParams['eantype'] ? $aYCParams['eantype'] : $this->oSoapApi->getYCEANType();
        $sAlternateUnitISO  = $aYCParams['altunitiso'] ? $aYCParams['altunitiso'] : $this->oSoapApi->getYCAlternateUnitISO();

        $sBatchReq          = $aYCParams['batchreq'];
        $sSerialNoFlag      = $aYCParams['noflag'];
        $sAltNumeratorUOM   = $aYCParams['altnum'];
        $sAltDenominatorUOM = $aYCParams['altdeno'];

        // initiate formatting process with initial details
        $aParams            = $this->getInitialParams("ART");
        $oObject            = new \stdClass();
        $oObject->ControlReference = new \stdClass();
        foreach ($aParams as $key => $param) {
            $oObject->ControlReference->$key = $param;
        }

        // if no stock available for this article then,
        // deactivate this article in warehouse
        if ($aArticle['instock'] === 0) {
            $sFlag = "D";
        }

        // set the length, width, height, volume
        $sBaseOUM   = $sAlternateUnitISO ? $sAlternateUnitISO : "PCE";
        $sName      = $aArticle['name'];
        $sEan       = $aArticle['ean'];
        $dWeight    = $aArticle['weight'];
        $dLength    = $aArticle['length'];
        $dWidth     = $aArticle['width'];
        $dHeight    = $aArticle['height'];

        // get precise volume values
        $dVolume = $this->calcArticleVolume($dLength, $dWidth, $dHeight, $sLengthISO, $sWidthISO, $sHeightISO, $sVolumeISO);

        // set the article data now!!
        $oObject->ArticleList                               = new \stdClass();
        $oObject->ArticleList->Article                      = new \stdClass();
        $oObject->ArticleList->Article->ChangeFlag          = $sFlag;
        $oObject->ArticleList->Article->DepositorNo         = $sDepoNumber;
        $oObject->ArticleList->Article->PlantID             = $sPlantID;
        $oObject->ArticleList->Article->ArticleNo           = $aArticle['ordernumber']; // artnum
        $oObject->ArticleList->Article->BaseUOM             = $sBaseOUM;
        $oObject->ArticleList->Article->NetWeight["_"]      = round($dWeight, 3); // weight
        $oObject->ArticleList->Article->NetWeight["ISO"]    = $sNWeightISO; // ISO
        $oObject->ArticleList->Article->BatchMngtReq        = $sBatchReq;
        $oObject->ArticleList->Article->Restlaufzeit        = $sMinRemLife;

        // if the not 0=ignore?
        if ($sExpDateType > 0) {
            $oObject->ArticleList->Article->PeriodExpDateType   = $sExpDateType;
        }
        $oObject->ArticleList->Article->SerialNoFlag        = $sSerialNoFlag;

        // Add unit data
        $oObject->ArticleList->Article->UnitsOfMeasure                      = new \stdClass();
        $oObject->ArticleList->Article->UnitsOfMeasure->EAN["EANType"]      = $sEANType; // EANType

        if ($sEan != "") {
            $sEANvalue = sprintf("%09d", $sEan);
        }

        $oObject->ArticleList->Article->UnitsOfMeasure->EAN["_"]            = $sEANvalue; // EAN
        $oObject->ArticleList->Article->UnitsOfMeasure->AlternateUnitISO    = $sAlternateUnitISO;
        $oObject->ArticleList->Article->UnitsOfMeasure->AltNumeratorUOM     = $sAltNumeratorUOM;
        $oObject->ArticleList->Article->UnitsOfMeasure->AltDenominatorUOM   = $sAltDenominatorUOM;
        $oObject->ArticleList->Article->UnitsOfMeasure->GrossWeight["ISO"]  = $sGWeightISO;
        $oObject->ArticleList->Article->UnitsOfMeasure->GrossWeight["_"]    = round($dWeight, 3);
        $oObject->ArticleList->Article->UnitsOfMeasure->Length["ISO"]       = $sLengthISO;
        $oObject->ArticleList->Article->UnitsOfMeasure->Length["_"]         = round($dLength, 3);
        $oObject->ArticleList->Article->UnitsOfMeasure->Width["ISO"]        = $sWidthISO;
        $oObject->ArticleList->Article->UnitsOfMeasure->Width["_"]          = round($dWidth, 3);
        $oObject->ArticleList->Article->UnitsOfMeasure->Height["ISO"]       = $sHeightISO;
        $oObject->ArticleList->Article->UnitsOfMeasure->Height["_"]         = round($dHeight, 3);
        $oObject->ArticleList->Article->UnitsOfMeasure->Volume["ISO"]       = $sVolumeISO;
        $oObject->ArticleList->Article->UnitsOfMeasure->Volume["_"]         = round($dVolume, 3);

        // article description
        $arrayOfObjects = array();
        $oObject->ArticleList->Article->ArticleDescriptions                 = new \stdClass();

        // temporary single language // include the other languages from translated file
        foreach ($aArticle['pronames'] as $proname) {
            $abbr = substr($proname['lang'], 0, 2);
            $oObject->ArticleList->Article->ArticleDescriptions->ArticleDescription = array();
            $oObject->ArticleList->Article->ArticleDescriptions->ArticleDescription["ArticleDescriptionLC"] = $abbr;
            $oObject->ArticleList->Article->ArticleDescriptions->ArticleDescription["_"] = substr($proname['name'], 0, 40);

            $arrayOfObjects[] = $oObject->ArticleList->Article->ArticleDescriptions->ArticleDescription;
        }
        $oObject->ArticleList->Article->ArticleDescriptions = $arrayOfObjects;

        return $oObject;
    }

    /**
     * Creates New customer Order in Yellowcube
     *
     * @param array   $aOrders  Array of Order data
     * @param boolean $isReturn If this is return
     *
     * @return array
     */
    public function createYCCustomerOrder($aOrders, $isReturn = false)
    {
        // get the formatted article data
        $oRequestData = $this->getYCFormattedOrderData($aOrders, $isReturn);

        // if the response is an array and is having error message?
        if (is_array($oRequestData) && $oRequestData['success'] === false) {
            return $oRequestData;
        } else {
            try{
                $aResponse = $this->oSoapApi->callFunction("CreateYCCustomerOrder", $oRequestData);
                return(array(
                                'success'   => true,
                                'data'      => $aResponse
                            ));
            } catch(Exception $soapex) {
                $this->oLogs->saveLogsData('createYCCustomerOrder', $soapex);
                return(array(
                                'success' => false,
                                'message' => $soapex->getMessage()
                            ));
            }
        }
    }

    /**
     * Returns the order details in object form
     *
     * @param array   $aOrder   array of order data
     * @param boolean $isReturn If this is return
     *
     * @return array
     */
    public function getYCFormattedOrderData($aOrder, $isReturn = false)
    {
        // define params needed
        $aLang          = explode('_', $aOrder['language']);
        $sLanguage      = reset($aLang);
        $sDepoNumber    = $this->oSoapApi->getYCDepositorNumber();
        $sPlantID       = $this->oSoapApi->getYCPlantId();
        $sMinRemLife    = $this->oSoapApi->getTransMaxTime();

        $sPartner       = $aOrder['userid'];
        $sPartnerNo     = $this->oSoapApi->getYCPartnerNumber();
        $sPartnerType   = $this->oSoapApi->getYCPartnerType();
        $sShipping      = $this->cleanShippingValue($aOrder['shipping']);
        $countryISO     = $aOrder['country'];
        $sZipCode       = $this->verifyZipStatus($aOrder['zip'], $countryISO);

        // if the zipcode is an array and is having error message?
        if (is_array($sZipCode) && $sZipCode['success'] === false) {
            return $sZipCode;
        } else {
            $sDocType       = $this->oSoapApi->getYCDocType();
            $sDocMimeType   = $this->oSoapApi->getYCDocMimeType();
            $sOrderDocFlag  = $this->oSoapApi->getYCOrderDocumentsFlag();
            $sPickMessage   = '';
            $sReturnReason  = '';

            // initiate formatting process with initial details
            $aParams            = $this->getInitialParams("WAB");
            $oObject            = new \stdClass();
            $oObject->ControlReference = new \stdClass();
            foreach ($aParams as $key => $param) {
                $oObject->ControlReference->$key = $param;
            }

            // order header information
            $oObject->Order                                 = new \stdClass();
            $oObject->Order->OrderHeader                    = new \stdClass();
            $oObject->Order->OrderHeader->DepositorNo       = $sDepoNumber;
            $oObject->Order->OrderHeader->CustomerOrderNo   = $aOrder['ordernumber'];
            $oObject->Order->OrderHeader->CustomerOrderDate = date("Ymd", strtotime($aOrder['ordertime']));

            // order partner information
            $oObject->Order->PartnerAddress                            = new \stdClass();
            $oObject->Order->PartnerAddress->Partner                   = new \stdClass();
            $oObject->Order->PartnerAddress->Partner->PartnerType      = $sPartnerType;
            $oObject->Order->PartnerAddress->Partner->PartnerNo        = $sPartnerNo;

            if ($sPartner) {
                $oObject->Order->PartnerAddress->Partner->PartnerReference = $sPartner;
            }

            // salutation
            $sSalutation = $this->getLangBasedSal($sLanguage, strtoupper($aOrder['sal']));

            $oObject->Order->PartnerAddress->Partner->Title            = $sSalutation;

            /* set company and department details
             * FORMAT: Firma > Abteilung > Vorname Nachname
             *
             * If none beyond name is set, name starts in the Name1 field.
             * If Firma is set, then it starts in the Name1 field, then Name2 Abteilung (if it is set),
             * then Vorname Nachname in Name3
             */
            if ($aOrder['company']) {
                $oObject->Order->PartnerAddress->Partner->Name1 = $aOrder['company'];
                if ($aOrder['department']) {
                    $oObject->Order->PartnerAddress->Partner->Name2 = $aOrder['department'];
                    $oObject->Order->PartnerAddress->Partner->Name3 = $aOrder['fullname'];
                } else {
                    $oObject->Order->PartnerAddress->Partner->Name2 = $aOrder['fullname'];
                }
            } else {
                if ($aOrder['department']) {
                    $oObject->Order->PartnerAddress->Partner->Name1 = $aOrder['department'];
                    $oObject->Order->PartnerAddress->Partner->Name2 = $aOrder['fullname'];
                } else {
                    $oObject->Order->PartnerAddress->Partner->Name1 = $aOrder['fullname'];
                }
            }

            // address information for the shipping user.
            $oObject->Order->PartnerAddress->Partner->Street           = $aOrder['streetinfo'];
            $oObject->Order->PartnerAddress->Partner->CountryCode      = $countryISO;
            $oObject->Order->PartnerAddress->Partner->ZIPCode          = $sZipCode;
            $oObject->Order->PartnerAddress->Partner->City             = $aOrder['city'];
            $oObject->Order->PartnerAddress->Partner->Email            = $aOrder['email'];
            $oObject->Order->PartnerAddress->Partner->LanguageCode     = $sLanguage;

            // Value added information
            $oObject->Order->ValueAddedServices                         = new \stdClass();
            $oObject->Order->ValueAddedServices->AdditionalService      = new \stdClass();

            // if the return operation
            if ($isReturn) {
                $sBasicShipping = "RETURN";
                $sAdditionalShipping = "";
            } else {
                $sBasicShipping = trim(reset($sShipping));
                if (count($sShipping) > 0) {
                    $sAdditionalShipping = trim(end($sShipping));
                }
            }

            $oObject->Order->ValueAddedServices->AdditionalService->BasicShippingServices       = $sBasicShipping;
            $oObject->Order->ValueAddedServices->AdditionalService->AdditionalShippingServices  = $sAdditionalShipping;

            // order articles information
            $arrayOfObjects = array();
            $oObject->Order->OrderPositions = new \stdClass();
            $oOrderArticles = $aOrder['orderarticles'];

            // define counter variable
            $iterator = 1;
            foreach ($oOrderArticles as $key=>$article) {

                // if not set: use OXID > articles > Extended tab values
                $sQuantityISO   = $aYCParams['sYellowCubeAlternateUnitISO'];

                // set module default value
                if (!$sQuantityISO) {
                    $sQuantityISO   = $this->oSoapApi->getYCQuantityISO();
                }

                $oObject->Order->OrderPositions->Position                   = new \stdClass();
                $oObject->Order->OrderPositions->Position->PosNo            = $iterator;
                $oObject->Order->OrderPositions->Position->ArticleNo        = $article['articleordernumber'];
                $oObject->Order->OrderPositions->Position->EAN              = $article['ean'];
                $oObject->Order->OrderPositions->Position->Plant            = $sPlantID;
                $oObject->Order->OrderPositions->Position->Quantity         = $article['quantity'];
                $oObject->Order->OrderPositions->Position->QuantityISO      = $sQuantityISO;
                $oObject->Order->OrderPositions->Position->ShortDescription = substr($article['name'], 0, 40);
                $oObject->Order->OrderPositions->Position->PickingMessage   = $sPickMessage;
                $oObject->Order->OrderPositions->Position->PickingMessageLC = $sLanguage;
                $oObject->Order->OrderPositions->Position->ReturnReason     = $sReturnReason;
                $arrayOfObjects[] = $oObject->Order->OrderPositions->Position;// assign to array

                $iterator = $iterator + 1;
            }
            $oObject->Order->OrderPositions = $arrayOfObjects; // reverse assign the array to object...

            // PDF order overview..
            $pdfData = $this->getOrderInvoiceData($aOrder['ordid']);

            // PDF order overview..
            if ($pdfData != "" || $pdfData != null) {
                $oObject->Order->OrderDocuments                                 = new \stdClass();
                $oObject->Order->OrderDocuments->Docs                           = new \stdClass();
                $oObject->Order->OrderDocuments->OrderDocumentsFlag             = $sOrderDocFlag;
                $oObject->Order->OrderDocuments->Docs->DocType                  = $sDocType;
                $oObject->Order->OrderDocuments->Docs->DocMimeType              = $sDocMimeType;
                $oObject->Order->OrderDocuments->Docs->DocStream                = $pdfData; // base64 encoded data
            }

            return $oObject;
        }
    }

    /**
     * Validates zipcode values
     *
     * @param string $zipValue   Zipcode value
     * @param string $countryISO CountryCode
     *
     * @return string
     */
    public function verifyZipStatus($zipValue, $countryISO)
    {
        try {
            // flip chars -> iso
            $myArray = array_flip($this->aCountryVsZip);
            if (in_array($countryISO, $myArray)) {
                $maxChars = $this->aCountryVsZip[$countryISO];
                if (strlen($zipValue) != $maxChars) {
                    $message = $this->getSnippetValue('engine/Shopware/Plugins/Local/Backend/AsignYellowcube', 'yellowcube/zip/message/nomatch');
                    $this->oLogs->saveLogsData('NOMATCH_ZIP_ERROR', $message, true);
                    return(array(
                            'success' => false,
                            'zcode'   => -2,
                            'message' => $message
                        ));
                } elseif (preg_match('/[A-Za-z]/', $zipValue)) {
                    $message = $this->getSnippetValue('engine/Shopware/Plugins/Local/Backend/AsignYellowcube', 'yellowcube/zip/message/invalid');
                    $this->oLogs->saveLogsData('INVALID_ZIP_ERROR', $message, true);
                    return(array(
                            'success' => false,
                            'zcode'   => -3,
                            'message' => $message
                        ));
                } else {
                    return $zipValue;
                }
            } else {
                return $zipValue;
            }
        } catch (Exception $sEx) {
            $this->oLogs->saveLogsData('verifyZipStatus', $soapex);
            return(array(
                        'success' => false,
                        'message' => $sEx->getMessage(),
                        'zcode'   => -4
                    ));
        }
    }

    /**
     * Validates snippet value
     *
     * @param string $namespace  Namespace value
     * @param string $name       Name
     *
     * @return string
     */
    public function getSnippetValue($namespace, $name)
    {
        $sMessage = Shopware()->Db()->fetchOne("select `value` from `s_core_snippets` where `namespace` = '" . $namespace . "' and `name` = '" . $name . "'");

        return $sMessage;
    }

    /**
     * Replaces and returns shipping method as YC format
     * e.g. BasicShippingServices = ECO,PRI,etc.
     * AdditionalShippingServices = SI;SA
     *
     * @param string $sValue YC Shipping Value
     *
     * @return mixed
     */
    protected function cleanShippingValue($sValue)
    {
        $sValue = str_replace("SPS_", "", $sValue);
        $aShipValue = explode("_", $sValue);

        return $aShipValue;
    }

    /**
     * Returns Salutation based on langauge
     *
     * @param string  $sLang Language character
     * @param string  $sSal  Salutation value
     *
     * @return string
     */
    protected function getLangBasedSal($sLang, $sSal)
    {
        switch ($sLang) {
        case 'de': return $this->aSaluationsDE[$sSal];
            break;

        case 'en': return $this->aSaluationsEN[$sSal];
            break;

        case 'fr': return $this->aSaluationsFR[$sSal];
            break;

        case 'it': return $this->aSaluationsIT[$sSal];
            break;
        }
    }

    /**
     * Get Swag default unit value for this article
     *
     * @param string $artNum Article number
     *
     * @return string
     */
    protected function getSwagUnitValue($artNum)
    {
        return Shopware()->Db()->fetchOne("select `unit` from `s_core_units` where `id` = (select `unitID` from `s_articles_details` where `ordernumber` = '" . $artNum . "')");
    }

    /**
     * Returns stored Yellowcube reference
     *
     * @param integer $myId  Object id
     * @param string  $sType Type of query
     *
     * @return array
     */
    public function getYCReferenceNumber($myId, $sType)
    {
        $aTables =  array(
                        'ART'   => 'asign_yellowcube_product',
                        'WAB'   => 'asign_yellowcube_orders',
                        'WAR'   => 'asign_yellowcube_orders'
                   );

        // if Type is ART or WAB
        if ($sType === 'ART') {
            $sColumn = 'artid';
        } else {
            $sColumn = 'ordid';
        }

        $sReference = Shopware()->Db()->fetchOne("select `ycReference` from `" . $aTables[$sType] . "` where `" . $sColumn . "` = '" . $myId ."'");
        // if second calling then?
        if ($sType == "WAR") {
            $sReference = Shopware()->Db()->fetchOne("select `ordernumber` from `s_order` where `id` = '" . $myId . "'");
        }

        return $sReference;
    }

    /**
     * Returns invoice pdf content for the document
     *
     * @param integer $orderId Order Id
     *
     * @return string
     */
    protected function getOrderInvoiceData($orderId)
    {
        $documentHash = Shopware()->Db()->fetchOne("select `hash` from `s_order_documents` where `orderID` = '" . $orderId . "'");
        $sFilename = Shopware()->OldPath() . "files/documents" . "/" . $documentHash . ".pdf";

        // get the base64 encoded content
        $sContent  = file_get_contents($sFilename);

        return $sContent;
    }

    /**
     * Converts and sends Volume value based on Units
     *
     * @param float  $dLength    Length val
     * @param float  $dWidth     Width val
     * @param float  $dHeight    Height val
     * @param float  $sLength    Length Unit
     * @param float  $sWidth     Width unit
     * @param float  $sHeight    Height unit
     * @param string $sVolumeISO Volume ISO name
     *
     * @return float
     */
    public function calcArticleVolume($dLength, $dWidth, $dHeight, $sLength, $sWidth, $sHeight, $sVolumeISO)
    {
        switch ($sVolumeISO) {
            case 'CMQ': $l = $this->getAdjustedValues($sLength, $dLength);
                        $w = $this->getAdjustedValues($sWidth, $dWidth);
                        $h = $this->getAdjustedValues($sHeight, $dHeight);
            break;
            case 'MTQ': $l = $this->getAdjustedValues($sLength, $dLength, 'm');
                        $w = $this->getAdjustedValues($sWidth, $dWidth, 'm');
                        $h = $this->getAdjustedValues($sHeight, $dHeight, 'm');
            break;
        }

        return $l * $w * $h;
    }

    /**
     * Returns adjusted value as per Unit and Type
     *
     * @param string $sUnit Unit name
     * @param float  $dUnit Unit value
     * @param string $sType Volum Unit
     *
     * @return float
     */
    protected function getAdjustedValues($sUnit, $dUnit, $sType = 'c')
    {
        switch ($sUnit) {
            case 'CMT':
                if ($sType === 'm') {
                    return round($dUnit / 100, 3);
                }

                return round($dUnit, 3);
            break;
            case 'MMT':
                if ($sType === 'm') {
                    return round($dUnit / 1000, 3);
                }

                return round($dUnit / 10, 3);
            break;
            case 'MTR':
                if ($sType === 'm') {
                    return round($dUnit, 3);
                }

                return round($dUnit * 100, 3);
            break;
        }
    }
}
