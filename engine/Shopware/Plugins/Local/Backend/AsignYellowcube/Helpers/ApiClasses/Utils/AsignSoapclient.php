<?php

/**
 * This file extends SOAP features and functions
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
 * @see       AsignSoapclient
 * @since     File available since Release 1.0
 */

namespace Shopware\AsignYellowcube\Helpers\ApiClasses\Utils;

use Shopware\AsignYellowcube\Helpers\ApiClasses\AsignSoapClientApi;
use Shopware\AsignYellowcube\Components\Api\WSSESoap;
use Shopware\AsignYellowcube\Components\Api\XMLSecurityKey;

/**
* Extends SOAP features and functions
* 
* @category A-Sign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
class AsignSoapclient extends \SoapClient
{
    /**
     * @var array Soap client options.
     */
    protected $options = array();

    /**
     * @var string Loaded certificate.
     */
    protected $certificateContent;

    /**
     * @var bool Use certifiate or not
     */
    protected $useCertificate;
    
    /**
     * @param mixed $wsdl
     * @param array $options
     */
    public function __construct($wsdl, $options)
    {
        $oSoap = new AsignSoapClientApi();
        $this->useCertificate = $oSoap->useCertificateForAllModes();

        $this->options = $options;
        parent::__construct($wsdl, $this->options);
    }

    /**
     * @inheritdoc
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = NULL)
    {
        if ($this->useCertificate) {            
            return $this->signRequest($request, $location, $action, $version, $oneWay);
        }

        return parent::__doRequest($request, $location, $action, $version, $oneWay);
    }

    /**
     * Signs the specified request.
     *
     * @param      $request
     * @param      $location
     * @param      $action
     * @param      $version
     * @param null $oneWay
     *
     * @return string
     * @throws \Exception
     */
    protected function signRequest($request, $location, $action, $version, $oneWay = NULL)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($request);
        
        $wsse = new WSSESoap($doc);
        $wsse->addTimestamp();

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
        $key->loadKey($this->getCertificateContent());
        $wsse->signSoapDoc($key);

        $token = $wsse->addBinaryToken($this->getCertificateContent());
        $wsse->attachTokentoSig($token);

        $signedRequest = $wsse->saveXML();
        
        return parent::__doRequest($signedRequest, $location, $action, $version, $oneWay);
    }

    /**
     * Calls the specified method on the SOAP server.
     *
     * @param string $method
     * @param string $args
     * @return mixed|void
     *
     * @throws YellowCubeException if a SOAP error occurs.
     */
    public function __call($method, $args)
    {
        try {
            return parent::__call($method, $args);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message .= PHP_EOL . PHP_EOL . 'Request XML: ' . PHP_EOL . $this->__getLastRequest();
            throw new \Exception($message, $e->getCode(), $e);
        }
    }

    /**
     * Returns true if a certificate should be used.
     *
     * @return mixed
     */
    protected function useCertificate()
    {
        return !empty($this->options['local_cert']);
    }

    /**
     * Returns content of the certificate passed  in 'local_cert'.
     *
     * @return string Content of the certificate passed  in 'local_cert'.
     */
    protected function getCertificateContent()
    {        
        return file_get_contents($this->options['local_cert']);
    }
}
