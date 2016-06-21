<?php

/**
 * This file defines data model for Inventory
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
 * @see       Inventory
 * @since     File available since Release 1.0
 */

namespace Shopware\CustomModels\AsignModels\Inventory;
 
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

/**
* Defines data model for Inventory
* 
* @category A-Sign
* @package  AsignYellowcube_v2.0_CE_5.1
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
 
/**
 * Shopware\CustomModels\AsignModels\Inventory\Inventory
 *
 * @ORM\Table(name="asign_yellowcube_inventory")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Inventory extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $ycarticlenr
     *
     * @ORM\Column(name="ycarticlenr", type="string", length=128, precision=0, scale=0, nullable=false, unique=false)
     */
    private $ycarticlenr;

    /**
     * @var string $articlenr
     *
     * @ORM\Column(name="articlenr", type="string", length=100, precision=0, scale=0, nullable=false, unique=false)
     */
    private $articlenr;

    /**
     * @var string $artdesc
     *
     * @ORM\Column(name="artdesc", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $artdesc;

    /**
     * @var string $additional
     *
     * @ORM\Column(name="additional", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $additional;

    /**
     * @var \DateTime $createdon
     *
     * @ORM\Column(name="createdon", type="datetime", precision=0, scale=0, nullable=false, unique=false)
     */
    private $createdon;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ycarticlenr
     *
     * @param string $sValue
     * @return Inventory
     */
    public function setYcartnum($sValue)
    {
        if (!empty($sValue)) {
            $this->ycarticlenr = $sValue;
        }
        return $this;
    }

    /**
     * Get Ycarticlenr
     *
     * @return string
     */
    public function getYcartnum()
    {
        return $this->ycarticlenr;
    }

    /**
     * Set articlenr
     *
     * @param string $sValue
     * @return Inventory
     */
    public function setArtnum($sValue)
    {
        if (!empty($sValue)) {
            $this->articlenr = $sValue;
        }
        return $this;
    }

    /**
     * Get Article Number
     *
     * @return string
     */
    public function getArtnum()
    {
        return $this->articlenr;
    }

    /**
     * Set article desc
     *
     * @param string $sValue
     * @return Inventory
     */
    public function setArtDesc($sValue)
    {
        if (!empty($sValue)) {
            $this->artdesc = $sValue;
        }
        return $this;
    }

    /**
     * Get Article Description
     *
     * @return string
     */
    public function getArtDesc()
    {
        return $this->artdesc;
    }

    /**
     * Set additional information
     *
     * @param string $sValue
     * @return Inventory
     */
    public function setAddInfo($sValue)
    {
        if (!empty($sValue)) {
            $this->additional = $sValue;
        }
        return $this;
    }

    /**
     * Get Additional Information
     *
     * @return text
     */
    public function getAddInfo()
    {
        return $this->additional;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Inventory
     */
    public function setCreated($created)
    {
        if (!empty($created)) {
            $this->createdon = $created;
        }
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->createdon;
    }
}
