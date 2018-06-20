<?php
/**
 * This file defines data repository for Inventory
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

namespace Shopware\CustomModels\AsignModels\Inventory;

use Shopware\Components\Model\ModelRepository;

/**
* Defines repository for Inventory
*
* @category A-Sign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
class Repository extends ModelRepository
{
    /**
     * Returns all the inventory based on filter or sort.
     *
     * @param array   $filters Filters
     * @param integer $sort    Sort value
     * @param integer $offset  Offset value
     * @param integer $limit   Limit value
     *
     * @return array
     */
    public function getInventoryListQuery($filters, $sort, $offset = 0, $limit = 100)
    {
        $select = Shopware()->Db()->select()
                ->from('asign_yellowcube_inventory');

        //If a filter is set
        if ($filters) {
            foreach ($filters as $filter) {
                $select->where('asign_yellowcube_inventory.ycarticlenr LIKE ?', '%' . $filter["value"] . '%');
                $select->orWhere('asign_yellowcube_inventory.articlenr LIKE ?', '%' . $filter["value"] . '%');
                $select->orWhere('asign_yellowcube_inventory.artdesc LIKE ?', '%' . $filter["value"] . '%');
            }
        }

        // sortin the inventory list
        if ($sort) {
            $sorting = reset($sort);
            switch ($sorting['property']) {
                case 'ycarticlenr':
                    $select->order('asign_yellowcube_inventory.ycarticlenr', $sorting['direction']);
                    break;
                case 'articlenr':
                    $select->order('asign_yellowcube_inventory.articlenr', $sorting['direction']);
                    break;
                case 'artdesc':
                    $select->order('asign_yellowcube_inventory.artdesc', $sorting['direction']);
                    break;
                default:
                    $select->order('asign_yellowcube_inventory.createdon', 'DESC');
            }
        } else {
            $select->order('asign_yellowcube_inventory.artdesc', 'DESC');
        }

        return $select;
    }

    /**
     * Stores inventory information received from Yellowcube
     *
     * @param array $aResponseData Object of response
     *
     * @return integer Updated articles
     */
    public function saveInventoryData($aResponseData)
    {
        // format the response data
        $iCount = 0;

        // reset the inventory data
        $this->resetInventoryData();

        foreach ($aResponseData['ArticleList']['Article'] as $aArticle) {
            $qtyISO  = $aArticle['QuantityUOM']['QuantityISO'];
            $qtyUOM  = $aArticle['QuantityUOM']['_'];
            $ycartnr = $aArticle['YCArticleNo'];
            $artnr   = $aArticle['ArticleNo'];
            $artdesc = $aArticle['ArticleDescription'];

            // entry id to avoid duplicates
            $mainId = substr($ycartnr, 4);

            // frame the additioanal information array
            $aAddInfo = array(
                'EAN'               => $aArticle['EAN'],
                'Plant'             => $aArticle['Plant'],
                'StorageLocation'   => $aArticle['StorageLocation'],
                'StockType'         => $aArticle['StockType'],
                'QuantityISO'       => $qtyISO,
                'QuantityUOM'       => $qtyUOM,
                'YCLot'             => $aArticle['YCLot'],
                'Lot'               => $aArticle['Lot'],
                'BestBeforeDate'    => $aArticle['BestBeforeDate'],
            );
            // serialize the data
            $sAdditional = serialize($aAddInfo);

            // push in db
            $sUpdateYCInventory = "INSERT INTO `asign_yellowcube_inventory` SET `id` = '" . $mainId . "', `ycarticlenr` = '".$ycartnr."', `articlenr` = '".$artnr."', `artdesc` = '" . $artdesc . "', `additional` = '" . $sAdditional . "' ON DUPLICATE KEY UPDATE `createdon` = NOW()";
            Shopware()->Db()->query($sUpdateYCInventory);

            //update the stock
            $iStock = (int) $qtyUOM;
            $sUpdateArtInventory = "UPDATE `s_articles_details` SET `instock` = '" . $iStock . "' WHERE `ordernumber` = '" . $artnr . "'";
            Shopware()->Db()->query($sUpdateArtInventory);

            $iCount++;
        }

        return $iCount;
    }

    /**
     * Resets the oxstock value for all articles that are entered in the YC warehouse.
     * This should be run before setting stock, because YC only sends information on articles, that have
     * over 0 stock.
     */
    public function resetInventoryData()
    {
        $aArticles = Shopware()->Db()->fetchAll("SELECT `artid`, `ycResponse` FROM `asign_yellowcube_product` WHERE `ycResponse` != ''");

        foreach ($aArticles as $article) {
            $aResponse = unserialize($article['ycResponse']);

            // convert object 2 array
            if (is_object($aResponse)) {
                $aResponse = json_decode(json_encode($aResponse), true);
            }

            if ($aResponse['StatusCode'] == 100) {
                Shopware()->Db()->query("UPDATE `s_articles_details` SET `instock` = '0' WHERE `articleID` = '" . $article['artid'] . "'");
            }
        }
    }
}
