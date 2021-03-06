<?php

/**
 * This file defines data model for Error logs
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
 * @see       Errorlogs
 * @since     File available since Release 1.0
 */

namespace Shopware\CustomModels\AsignModels\Errorlogs;
 
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

/**
* Defines data model for error logs
* 
* @category A-Sign
* @package  AsignYellowcube
* @author   entwicklung@a-sign.ch
* @link     http://www.a-sign.ch
*/
 
/**
 * Shopware\CustomModels\AsignModels\Errorlogs\Errorlogs
 *
 * @ORM\Table(name="asign_yellowcube_logs")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Errorlogs extends ModelEntity
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
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=100, precision=0, scale=0, nullable=false, unique=false)
     */
    private $type;

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $message;

    /**
     * @var string $devlog
     *
     * @ORM\Column(name="devlog", type="text", precision=0, scale=0, nullable=false, unique=false)
     */
    private $devlog;

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
     * Set type
     *
     * @param string $sType
     * @return Logs
     */
    public function setType($sType)
    {
        if (!empty($sType)) {
            $this->type = $sType;
        }
        return $this;
    }

    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Message
     *
     * @param string $sMessage
     * @return Logs
     */
    public function setMessage($sMessage)
    {
        if (!empty($sMessage)) {
            $this->message = $sMessage;
        }
        return $this;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set Developer logs
     *
     * @param string $sDevlog
     * @return Logs
     */
    public function setDevlog($sDevlog)
    {
        if (!empty($sDevlog)) {
            $this->devlog = $sDevlog;
        }
        return $this;
    }

    /**
     * Get Developer log
     *
     * @return string
     */
    public function getDevlog()
    {
        return $this->devlog;
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
