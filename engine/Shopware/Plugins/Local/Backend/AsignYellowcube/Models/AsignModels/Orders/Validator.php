<?php
/**
 * This file defines data validator for rrders
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1.4
 * @link      https://www.a-sign.ch/
 * @since     File available since Release 2.1.3
 */

namespace Shopware\CustomModels\AsignModels\Orders;

/**
 * @category A-Sign
 * @package  AsignYellowcube
 * @author   entwicklung@a-sign.ch
 * @link     https://www.a-sign.ch
 */
class Validator
{
    private $aTrimFields = array(
        'CustomerOrderNo' => 35,
        'Title' => 15,
        'Name1' => 35,
        'Name2' => 35,
        'Name3' => 35,
        'Name4' => 35,
        'Street' => 35,
        'ZIPCode' => 10,
        'City' => 35,
        'POBox' => 35,
        'PhoneNo' => 21,
        'MobileNo' => 21,
        'SMSAvisMobNo' => 16,
        'FaxNo' => 13,
        'Email' => 241,
    );

    /**
     * @param object $oRequestData
     *
     * @return void
     */
    public function validate(&$oRequestData)
    {
        $aDataFields = array();

        if (is_object($oRequestData)) {
            $aDataFields = get_object_vars($oRequestData);
        }

        if (count($aDataFields)) {
            foreach ($aDataFields as $sKey => &$mData) {
                if (is_object($mData)) {
                    $this->validate($mData);
                } else {
                    // do the magic
                    $this->removeObjEmptyFields($sKey, $mData, $oRequestData);
                    $this->trimMaxFieldLength($sKey, $mData, $oRequestData);
                }
            }
        }
    }

    /*
     * Remove empty fields
     *
     * @param string $sKey
     * @param mixed $mData
     *
     * @return void
     */
    private function removeObjEmptyFields($sKey, $mData, &$oRequestData)
    {
        if (is_string($mData) && !strlen($mData)) {
            unset($oRequestData->$sKey);
        }
    }

    /*
     * Trim the maximum length of user adress fields
     *
     * @param string $sKey
     * @param mixed $mData
     *
     * @return void
     */
    private function trimMaxFieldLength($sKey, $mData, &$oRequestData)
    {
        if (array_key_exists($sKey, $this->aTrimFields) && strlen($mData)) {
            $oRequestData->$sKey = substr($mData, 0, $this->aTrimFields[$sKey]);
        }
    }
}
