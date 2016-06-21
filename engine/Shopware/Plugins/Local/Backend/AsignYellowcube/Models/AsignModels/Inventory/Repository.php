<?php
/**
 * This file defines data repository for Inventory
 *
 * PHP version 5
 *
 * @category  asign
 * @package   AsignYellowcube_v2.0_CE_5.1
 * @author    entwicklung@a-sign.ch
 * @copyright A-Sign
 * @license   http://www.a-sign.ch/
 * @version   2.0
 * @link      http://www.a-sign.ch/
 * @since     File available since Release 1.0
 */

namespace Shopware\CustomModels\AsignModels\Inventory;
use Shopware\Components\Model\ModelRepository;

/**
* Defines repository for Inventory
*
* @category A-Sign
* @package  AsignYellowcube_v2.0_CE_5.1
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
     * @param array $aResponseData Array of response
     *
     * @return integer
     */
    public function saveInventoryData($aResponseData)
    {
        // format the response data
        $iCount = 0;

        // reset the inventory data
        $this->resetInventoryData();

        foreach ($aResponseData->ArticleList->Article as $article) {
            $qtyISO  = $article->QuantityUOM->QuantityISO;
            $qtyUOM  = $article->QuantityUOM->_;
            $ycartnr = $article->YCArticleNo;
            $artnr   = $article->ArticleNo;
            $artdesc = $article->ArticleDescription;

            // entry id to avoid duplicates
            $mainId = substr($ycartnr, 4);

            // frame the additioanal information array
            $aAddInfo = array(
                'EAN'               => $article->EAN,
                'Plant'             => $article->Plant,
                'StorageLocation'   => $article->StorageLocation,
                'StockType'         => $article->StockType,
                'QuantityISO'       => $qtyISO,
                'QuantityUOM'       => $qtyUOM,
                'YCLot'             => $article->YCLot,
                'Lot'               => $article->Lot,
                'BestBeforeDate'    => $article->BestBeforeDate,
            );
            // serialize the data
            $sAdditional = serialize($aAddInfo);

            // push in db
            $query = "INSERT INTO `asign_yellowcube_inventory` SET `id` = '" . $mainId . "', `ycarticlenr` = '".$ycartnr."', `articlenr` = '".$artnr."', `artdesc` = '" . $artdesc . "', `additional` = '" . $sAdditional . "' ON DUPLICATE KEY UPDATE `createdon` = NOW()";
            Shopware()->Db()->query($query);

            //update the stock
            $iStock = (int) $qtyUOM;
            Shopware()->Db()->query("UPDATE `s_articles_details` SET `instock` = '" . $iStock . "' WHERE `ordernumber` = '" . $artnr . "'");

            $iCount = $iCount + 1;
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

            if ($aResponse['StatusCode'] == 100) {
                Shopware()->Db()->query("UPDATE `s_articles_details` SET `instock` = '0' WHERE `articleID` = '" . $article['artid'] . "'");
            }
        }
    }
}
