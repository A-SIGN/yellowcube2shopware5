<?php
/**
 * This file defines data repository for Products
 *
 * @category  asign
 * @package   AsignYellowcube
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   https://www.a-sign.ch/
 * @version   2.1.3
 * @link      https://www.a-sign.ch/
 * @since     File available since Release 1.0
 */

namespace Shopware\CustomModels\AsignModels\Product;

use Shopware\Components\Model\ModelRepository;

/**
 * Defines repository for Products
 *
 * @category A-Sign
 * @package  AsignYellowcube
 * @author   entwicklung@a-sign.ch
 * @link     http://www.a-sign.ch
 */
class Repository extends ModelRepository
{
    /**
     * @var define constants
     */
    const ALT_DENO_VAL = 1;
    const ALT_NUM_VAL = 1;

    /**
     * Returns all the products based on filter or sort.
     *
     * @param array $filters Filters
     * @param integer $sort Sort value
     * @param integer $offset Offset value
     * @param integer $limit Limit value
     *
     * @return Zend_Db_Select
     */
    public function getProductsListQuery($filters, $sort, $offset = 0, $limit = 100)
    {
        $select = Shopware()->Db()->select()
            ->from('s_articles', array('artid' => 'id', 'name', 'active', 'tara', 'tariff', 'origin'))
            ->joinInner('s_articles_attributes', 's_articles.id = s_articles_attributes.articleID', array('yc_export'))
            ->joinLeft('s_articles_details', 's_articles.id = s_articles_details.articleID', array('ordernumber', 'instock', 'active'))
            ->joinLeft('s_articles_esd', 's_articles.id = s_articles_esd.articleID', array('esdid' => 'id'))
            ->joinLeft('asign_yellowcube_product', 'asign_yellowcube_product.artid = s_articles_details.articleID', array('id', 'lastSent', 'ycSpsDetails', 'ycResponse', 'ycReference', 'createDate'));

        $select->where('s_articles_attributes.yc_export = 1');

        //If a filter is set
        if ($filters) {
            foreach ($filters as $filter) {
                $select->where('s_articles.name LIKE ?', '%' . $filter["value"] . '%');
                $select->orWhere('s_articles_details.ordernumber LIKE ?', '%' . $filter["value"] . '%');
            }
        }

        // add sorting features...
        if ($sort) {
            $sorting = reset($sort);
            $column = $sorting['property'];
            $direction = $sorting['direction'];
            switch ($column) {
                case 'artnum':
                    $select->order('s_articles_details.ordernumber ' . $direction);
                    break;
                case 'name':
                    $select->order('s_articles.name ' . $direction);
                    break;
                case 'inStock':
                    $select->order('s_articles_details.instock ' . $direction);
                    break;
                case 'active':
                    $select->order('s_articles.active ' . $direction);
                    break;
                case 'ycReference':
                    $select->order('asign_yellowcube_product.ycReference ' . $direction);
                    break;
                case 'timestamp':
                    $select->order('asign_yellowcube_product.createDate ' . $direction);
                    break;
                default:
                    $select->order('s_articles.name ' . $direction);
            }
        } else {
            $select->order('s_articles.name');
        }

        return $select;
    }

    /**
     * Stores additional information from Articles
     *
     * @param string $sData Serialized data values
     * @param integer $id Selected Row Id
     * @param integer $artid Selected Article Id
     * @param integer $aIntHandling Internation shipping data
     *
     * @return null
     */
    public function saveAdditionalData($sData, $id, $artid, $aIntHandling)
    {
        // frame the columns...
        $blUpdate = false;
        $sColumns = "`id` = ''";

        // insert / update asign_yellowcube_product table
        // push in db.. But first check if the data is alreay present!
        if ($id) {
            $iCount = Shopware()->Db()->query("select count(*) from `asign_yellowcube_product` where `id` = '" . $id . "' and `artid` = '" . $artid . "'");
            $blUpdate = true;
        }

        if ($iCount && $blUpdate) {
            $query = "update `asign_yellowcube_product` set `ycSpsDetails` = '" . $sData . "' where `id` = '" . $id . "' and `artid` = '" . $artid . "'";
        } else {
            $query = "insert into `asign_yellowcube_product` set `artid` = '" . $artid . "',`ycSpsDetails` = '" . $sData . "'";
        }
        Shopware()->Db()->query($query);

        // update internation handling details on s_articles table
        $sCountry = Shopware()->Db()->fetchOne("select `countryiso` from `s_core_countries` where `id` = '" . $aIntHandling['origin'] . "'");
        $sArtQuery = "update `s_articles` set `tariff` = '" . $aIntHandling['tariff'] . "', `tara` = '" . $aIntHandling['tara'] . "', `origin` = '" . $sCountry . "' where `id` = '" . $artid . "'";
        Shopware()->Db()->query($sArtQuery);

        // update order article information
        $this->updateHandlingInfo($aIntHandling, $artid);
    }

    /**
     * Updates Tara, Tariff and Origin details
     *
     * @param array $orderArticles Order articles
     *
     * @return null
     */
    public function updateHandlingInfo($aIntHandling, $artid)
    {
        $oOrder = Shopware()->Models()->getRepository("Shopware\CustomModels\AsignModels\Orders\Orders");
        $oOrder->updateOrderArticlesHandlingInfo($aIntHandling, $artid);
    }

    /**
     * Get Handling inforamtion for the articleid
     *
     * @param integer $articleID article item id
     *
     * @return array
     */
    public function getHandlingInfo($articleID)
    {
        $aIntHandling = Shopware()->Db()->fetchRow("select `tara`, `tariff`, `origin` from `s_articles` where `id` = '" . $articleID . "'");
        return $aIntHandling;
    }

    /**
     * Returns article data based on artid
     *
     * @param integer $iArticleId article item id
     *
     * @return array $aResult
     */
    public function getArticleDetails($iArticleId)
    {
        $sSql = "SELECT s_articles.name as `name`, s_articles_details.articleID, s_articles_details.weight, s_articles_details.length, s_articles_details.width, s_articles_details.height, s_articles_details.ordernumber, s_articles_details.ean, s_articles_details.instock FROM s_articles";
        $sSql .= " JOIN s_articles_details ON s_articles.id = s_articles_details.articleID";
        $sSql .= " WHERE s_articles.id = '" . $iArticleId . "' AND s_articles_details.kind = 1";

        $aResult = Shopware()->Db()->fetchRow($sSql);

        // attach esd status
        $aResult['esdid'] = Shopware()->Db()->fetchOne("select count(*) from `s_articles_esd` where `articleID` = '" . $iArticleId . "'");

        // attach ycube article parameters
        $ycSpsDetails = Shopware()->Db()->fetchOne("select `ycSpsDetails` from `asign_yellowcube_product` where `artid` = '" . $aResult['articleID'] . "'");
        $aParams = unserialize($ycSpsDetails);
        if ($aParams) {
            $aParams['altnum'] = self::ALT_NUM_VAL; // default
            $aParams['altdeno'] = self::ALT_DENO_VAL;// default
        }

        $aResult['ycparams'] = $aParams;

        if ($aResult) {
            // get translations
            $sSql = "SELECT s_core_locales.`locale`
                     FROM `s_core_locales` 
                     JOIN `s_core_shops` ON  s_core_shops.locale_id = s_core_locales.id
                     WHERE s_core_shops.id = 1";

            $aResult['pronames'][] = array(
                'lang' => Shopware()->Db()->fetchOne($sSql),
                'name' => $aResult['name'],
            );

            // set multilang value
            $sSql = "SELECT
                         s_core_locales.locale as `lang`,
                         s_articles_translations.name as `name`
                      FROM `s_articles_translations`
                      JOIN `s_core_shops` ON s_articles_translations.languageID = s_core_shops.id
                      JOIN `s_core_locales` ON s_core_shops.locale_id = s_core_locales.id
                      WHERE s_core_shops.active = 1 AND s_articles_translations.articleID = ?";
            $aTranslations = Shopware()->Db()->fetchAll($sSql, [$aResult['articleID']]);

            if (count($aTranslations)) {
                foreach ($aTranslations as $aTranslation) {
                    if ($aTranslation['name'] != '') {
                        $aResult['pronames'][] = $aTranslation;
                    }
                }
            }

            return $aResult;
        }
    }

    /**
     * Function to save the Response
     * received from Yellowcube
     *
     * @param array $aResponse Array of response
     * @param string $artid Article id
     *
     * @return void
     * @throws \Exception
     */
    public function saveArticleResponseData($aResponseData, $artid)
    {
        // if response is not "E" then?
        if ($aResponseData['StatusType'] !== 'E') {
            $sReference = ", `ycReference` = '" . $aResponseData['Reference'] . "'";
        }

        // serialize the data
        $sData = serialize($aResponseData);

        // update reference number, but first check if alreay entry?
        $iCount = Shopware()->Db()->fetchOne("select count(*) from `asign_yellowcube_product` where `artid` = '" . $artid . "'");

        // if present then?
        if ($iCount) {
            $sQuery = "update `asign_yellowcube_product` set `lastSent` = 1, `ycResponse` = '" . $sData . "'" . $sReference . " where `artid` = '" . $artid . "'";
        } else {
            $sQuery = "insert into `asign_yellowcube_product` set `artid` = '" . $artid . "', `lastSent` = 1, `ycResponse` = '" . $sData . "'" . $sReference;
        }

        Shopware()->Db()->query($sQuery);
    }

    /**
     * Returns saved status from the saved data
     *
     * @param string $itemId Item id
     * @param string $sTable Table name
     * @param string $sColumn
     *
     * @return array $aResponse
     */
    public function getYellowcubeReport($itemId, $sTable, $sColumn = 'ycResponse')
    {
        if (!$sColumn) {
            $sColumn = 'ycResponse';
        }

        $sQuery = "select `" . $sColumn . "` from `" . $sTable . "` where `artid` = '" . $itemId . "'";
        $sData = Shopware()->Db()->fetchOne($sQuery);
        $aData = unserialize($sData);

        if (is_object($aData)) {
            $aData = json_decode(json_encode($aData), true);
        }

        return $aData;
    }
}
