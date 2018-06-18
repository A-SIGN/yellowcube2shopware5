<?php
/**
 * This file defines data validator for rrders
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1.3
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
    /**
     * @param object $oRequestData
     * @return void
     */
    public function validate(object $oRequestData)
    {
        $this->removeObjEmptyFields($oRequestData);
    }

    /*
     * @param object $oRequestData
     * @return void
     */
    private function removeObjEmptyFields(object &$mData)
    {
        if (is_object($mData)) {
            $aDataFields = get_object_vars($mData);

            if (count($aDataFields)) {
                foreach ($aDataFields as $sKey => &$mDataField) {
                    if (is_object($mDataField)) {
                        $this->removeObjEmptyFields($mDataField);
                    }

                    if (is_string($mDataField) && !strlen($mDataField)) {
                        unset($mData->$sKey);
                    }
                }
            }
        }
    }
}
