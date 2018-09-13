<?php
/**
 * This file handles SOAP requests
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1.3
 * @link      https://www.a-sign.ch/
 * @see       AsignSoapClientApi
 * @since     File available since Release 1.0
 */

namespace Shopware\AsignYellowcube\Helpers\ApiClasses;

use Exception;
use Shopware\AsignYellowcube\Helpers\ApiClasses\Utils\AsignSoapclient;

/**
 * Handles SOAP related functions
 *
 * @category A-Sign
 * @package  AsignYellowcube
 * @author   entwicklung@a-sign.ch
 * @link     http://www.a-sign.ch
 */
class AsignSoapClientApi
{
    const YELLOWCUBE_WSDL_URL_TEST = 'https://service-test.swisspost.ch/apache/yellowcube-test/?wsdl';
    const YELLOWCUBE_WSDL_URL_DEV = 'https://service-test.swisspost.ch/apache/yellowcube-int/?wsdl';
    const YELLOWCUBE_WSDL_URL_PROD = 'https://service.swisspost.ch/apache/yellowcube/?wsdl';

    /**
     * Returns Operation mode for this process
     *
     * @param string $sParam Configuration param
     *
     * @return string
     */
    protected function returnConfigParam($sParam)
    {
        $config = Shopware()->Plugins()->Backend()->AsignYellowcube()->Config();
        return $config->$sParam;
    }

    /**
     * Returns Operation mode for this process
     *
     * @return string
     */
    public function getSoapOperatingMode()
    {
        return $this->returnConfigParam('sYellowCubeMode');
    }

    /**
     * Returns WSDL URI for the file
     *
     * @return string
     */
    public function getSoapWsdlUrl()
    {
        if ($this->getSoapOperatingMode() == 'P') {
            return self::YELLOWCUBE_WSDL_URL_PROD;
        } elseif ($this->getSoapOperatingMode() == 'D') {
            return self::YELLOWCUBE_WSDL_URL_DEV;
        } else {
            return self::YELLOWCUBE_WSDL_URL_TEST;
        }
    }

    /**
     * Returns Sender Identity detail
     *
     * @return string
     */
    public function getSoapWsdlSender()
    {
        return $this->returnConfigParam('sYellowCubeSender');
    }

    /**
     * Returns YellowCube Receiver info
     *
     * @return string
     */
    public function getSoapWsdlReceiver()
    {
        return $this->returnConfigParam('sYellowCubeReceiver');
    }

    /**
     * Returns developer email address
     *
     * @return string
     */
    public function getDeveloperEmail()
    {
        return $this->returnConfigParam('sYellowCubeNotifyEmail');
    }

    /**
     * Returns Yellowcube Depositor number
     *
     * @return string
     */
    public function getYCDepositorNumber()
    {
        return $this->returnConfigParam('sYellowCubeDepositorNo');
    }

    /**
     * Returns Yellowcube Plant ID
     *
     * @return string
     */
    public function getYCPlantId()
    {
        return $this->returnConfigParam('sYellowCubePlant');
    }

    /**
     * Returns maximum wait time
     *
     * @return string
     */
    public function getTransMaxTime()
    {
        return $this->returnConfigParam('sYellowCubeTransMaxTime');
    }

    /**
     * Returns Partner number
     *
     * @return string
     */
    public function getYCPartnerNumber()
    {
        return $this->returnConfigParam('sYellowCubePartnerNo');
    }

    /**
     * Returns Partner Type
     *
     * @return string
     */
    public function getYCPartnerType()
    {
        return $this->returnConfigParam('sYellowCubePType');
    }

    /**
     * Returns Quantity ISO value
     *
     * @return string
     */
    public function getYCQuantityISO()
    {
        return $this->returnConfigParam('sYellowCubeQuantityISO');
    }

    /**
     * Returns default EAN Type
     *
     * @return string
     */
    public function getYCEANType()
    {
        return $this->returnConfigParam('sYellowCubeEANType');
    }

    /**
     * Returns alternate ISO unit
     *
     * @return string
     */
    public function getYCAlternateUnitISO()
    {
        return $this->returnConfigParam('sYellowCubeAlternateUnitISO');
    }

    /**
     * Returns default Net Weight ISO unit
     *
     * @return string
     */
    public function getYCNetWeightISO()
    {
        return $this->returnConfigParam('sYellowCubeNetWeightISO');
    }

    /**
     * Returns default Gross Weight ISO unit
     *
     * @return string
     */
    public function getYCGWeightISO()
    {
        return $this->returnConfigParam('sYellowCubeGrossWeightISO');
    }

    /**
     * Returns default Length ISO unit
     *
     * @return string
     */
    public function getYCLengthISO()
    {
        return $this->returnConfigParam('sYellowCubeLengthISO');
    }

    /**
     * Returns default Width ISO unit
     *
     * @return string
     */
    public function getYCWidthISO()
    {
        return $this->returnConfigParam('sYellowCubeWidthISO');
    }

    /**
     * Returns default Height ISO unit
     *
     * @return string
     */
    public function getYCHeightISO()
    {
        return $this->returnConfigParam('sYellowCubeHeightISO');
    }

    /**
     * Returns default Volume ISO unit
     *
     * @return string
     */
    public function getYCVolumeISO()
    {
        return $this->returnConfigParam('sYellowCubeVolumeISO');
    }

    /**
     * Returns status of send order manually
     *
     * @return string
     */
    public function isManualSendAllowed()
    {
        return $this->returnConfigParam('blYellowCubeOrderManualSend');
    }

    /**
     * Returns order document type
     *
     * @return string
     */
    public function getYCDocType()
    {
        return $this->returnConfigParam('sYellowCubeDocType');
    }

    /**
     * Returns order document MIME type
     *
     * @return string
     */
    public function getYCDocMimeType()
    {
        return $this->returnConfigParam('sYellowCubeDocMimeType');
    }

    /**
     * Returns Order Documents Flag
     *
     * @return string
     */
    public function getYCOrderDocumentsFlag()
    {
        return $this->returnConfigParam('sYellowCubeOrderDocumentsFlag');
    }

    /**
     * Returns Certificate Filename
     *
     * @return string
     */
    public function getCertFilename()
    {
        return $this->returnConfigParam('sYellowCubeCertFile');
    }

    /**
     * Returns whether to Use certificate for LIVE or ALL modes
     *
     * @return bool
     */
    public function useCertificateForAllModes()
    {
        if ($this->getSoapOperatingMode() == 'T') {
            return false;
        }

        return true;
    }

    /**
     * Returns CRON hash value
     *
     * @return string
     */
    public function getCronHashValue()
    {
        return $this->returnConfigParam('sYellowCubeCronHash');
    }

    /**
     * Returns SOAP version defined
     *
     * @return string
     */
    public function getSoapVersion()
    {
        $sVersion = "1.0";
        return $sVersion;
    }

    /**
     * Returns Communication Type
     * Options: SOAP|REST|HTTPS|FTP
     *
     * @return string
     */
    public function getCommType()
    {
        $sComm = "SOAP";
        return $sComm;
    }

    /**
     * Initiates the Soap Client API
     *
     * @return object
     */
    protected function initSoap()
    {
        // api configs
        try {
            $wsdl = $this->getSoapWsdlUrl();
            $sCertFilename = $this->getCertFilename();
            $certPath = Shopware()->AppPath('Plugins/Local/Backend/AsignYellowcube/cert') . $sCertFilename;

            // set SOAP parameters
            $aParams = array(
                'soap_version' => SOAP_1_1,
                'trace'        => true,
                'exception'    => true,
                'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
            );

            // if only live then
            if ($this->useCertificateForAllModes()) {
                $aParams["local_cert"] = $certPath;
            }

            return new AsignSoapclient($wsdl, $aParams);
        } catch (Exception $sEx) {
            $oLogs = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs");
            $oLogs->saveLogsData('SOAP_INIT', $sEx);

            throw new Exception($sEx->getMessage());
        }
    }

    /**
     * Calls the function passed. Along with the params.
     * Performs SOAP call using passed function. Function name
     * varies for every WSDL.
     *
     * @param string $sFnc Function to be called
     * @param object $oParams object of params to be passed
     *
     * @return array $aResponse
     */
    public function callFunction($sFnc, $oParams = null)
    {
        // soap call the function
        $oClient = $this->initSoap();
        $oResponse = $oClient->$sFnc($oParams);

        if (!($oResponse instanceof \stdClass)) {
            throw new Exception("Return isn't an object!");
        }

        $aResponse = json_decode(json_encode($oResponse), true);

        // DEBUG: only for checking XML output
        $devMail = $this->getDeveloperEmail();
        if ($devMail != "") {
            @mail($devMail, "SOAP_REQUEST __getLastRequest", print_r($oClient->__getLastRequest(), 1)); // YC request
            @mail($devMail, 'SOAP_RESPONSE __getLastResponse', print_r($oClient->__getLastResponse(), 1)); // YC response
            @mail($devMail, 'SOAP_RESPONSE $aResponse', print_r($aResponse, 1)); // YC response
        }
        // END-DEBUG

        return $aResponse;
    }
}
