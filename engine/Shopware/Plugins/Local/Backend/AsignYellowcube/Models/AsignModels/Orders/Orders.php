<?php

/**
 * This file defines data model for Orders
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
 * @see       Orders
 * @since     File available since Release 1.0
 */

namespace Shopware\CustomModels\AsignModels\Orders;

use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

/**
* Defines data model for Orders
* 
* @category A-Sign
* @package  AsignYellowcube_v2.0_CE_5.1
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/

/**
 * Shopware\CustomModels\AsignModels\Orders\Orders
 *
 * @ORM\Table(name="asign_yellowcube_orders")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Orders extends ModelEntity
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
     * @var integer $ordid
     *     
     * @ORM\Column(name="ordid", type="integer", length=10, precision=0, scale=0, nullable=false, unique=false)
     */
    private $ordid;    

    /**
     * @var integer $lastSent
     *
     * @ORM\Column(name="lastSent", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $lastSent = false;    

    /**
     * @var string $ycReference
     *
     * @ORM\Column(name="ycReference", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $ycReference;

    /**
     * @var string $ycResponse
     *
     * @ORM\Column(name="ycResponse", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $ycResponse = null;

    /**
     * @var string $ycWabResponse
     *
     * @ORM\Column(name="ycWabResponse", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $ycWabResponse = null;

    /**
     * @var string $ycWarResponse
     *
     * @ORM\Column(name="ycWarResponse", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $ycWarResponse = null;

    /**
     * @var \DateTime $createDate
     *
     * @ORM\Column(name="createDate", type="datetime", precision=0, scale=0, nullable=false, unique=false))
     */
    private $createDate = null;
       
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $ordid
     */
    public function setOrdid($ordid)
    {
        $this->ordid = $ordid;
    }

    /**
     * @return int
     */
    public function getOrdid()
    {
        return $this->ordid;
    }

    /**
     * @param int $lastSent
     */
    public function setLastSent($lastSent)
    {
        $this->lastSent = $lastSent;
    }

    /**
     * @return int
     */
    public function getLastSent()
    {
        return $this->lastSent;
    }    

    /**
     * @param string $ycReference
     */
    public function setYcReference($ycReference)
    {
        $this->ycReference = $ycReference;
    }

    /**
     * @return string
     */
    public function getYcReference()
    {
        return $this->ycReference;
    }

    /**
     * @param string $ycresponse
     */
    public function setYcResponse($ycresponse)
    {
        $this->ycResponse = $ycresponse;
    }

    /**
     * @return string
     */
    public function getYcResponse()
    {
        return $this->ycResponse;
    }

    /**
     * @param string $ycwabresponse
     */
    public function setYcWabResponse($ycwabresponse)
    {
        $this->ycWabResponse = $ycwabresponse;
    }

    /**
     * @return string
     */
    public function getYcWabResponse()
    {
        return $this->ycWabResponse;
    }

    /**
     * @param string $ycwarresponse
     */
    public function setYcWarResponse($ycwarresponse)
    {
        $this->ycWarResponse = $ycwarresponse;
    }

    /**
     * @return string
     */
    public function getYcWarResponse()
    {
        return $this->ycWarResponse;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }
}
