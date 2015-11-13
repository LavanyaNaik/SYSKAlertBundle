<?php

namespace SYSK\AlertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 *
 * @ORM\Table(name="sysk_message")
 * @ORM\Entity(repositoryClass="SYSK\AlertBundle\Repository\SyskMessageRepository")
 */
class SyskMessage
{
    const TYPE_POSITIVE     = "POSITIVE";
    const TYPE_NEGATIVE     = "NEGATIVE";

    const STATUS_READ       = "READ";
    const STATUS_NOTREAD    = "NOT_READ";
    const STATUS_DELETED    = "DELETED";
    const STATUS_REJECTED   = "REJECTED";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="SYSK\AlertBundle\Entity\SyskUser", inversedBy="syskSentMessages")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     */
    private $sender;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="SYSK\AlertBundle\Entity\SyskUser", inversedBy="syskReceivedMessages")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="id", nullable=false)
     */
    private $receiver;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="message_type", type="string", length=255, nullable=false)
     */
    private $messageType;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="SYSK\AlertBundle\Entity\SyskIrritant", inversedBy="syskIrritantMessages")
     * @ORM\JoinColumn(name="irritant_id", referencedColumnName="id", nullable=true)
     */
    private $irritantId;

    /**
     * @var text
     *
     * @ORM\Column(name="message_text", type="text", nullable=true)
     */
    private $messageText;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    private $deleted;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status       = SyskMessage::STATUS_NOTREAD;
        $this->deleted      = false;
        $this->createdAt    = new \DateTime();
    }

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
     * Set status
     *
     * @param string $status
     *
     * @return SyskMessage
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set messageType
     *
     * @param string $messageType
     *
     * @return SyskMessage
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * Get messageType
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Set messageText
     *
     * @param string $messageText
     *
     * @return SyskMessage
     */
    public function setMessageText($messageText)
    {
        $this->messageText = $messageText;

        return $this;
    }

    /**
     * Get messageText
     *
     * @return string
     */
    public function getMessageText()
    {
        return $this->messageText;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return SyskMessage
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SyskMessage
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return SyskMessage
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set sender
     *
     * @param \SYSK\AlertBundle\Entity\SyskUser $sender
     *
     * @return SyskMessage
     */
    public function setSender(\SYSK\AlertBundle\Entity\SyskUser $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \SYSK\AlertBundle\Entity\SyskUser
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set receiver
     *
     * @param \SYSK\AlertBundle\Entity\SyskUser $receiver
     *
     * @return SyskMessage
     */
    public function setReceiver(\SYSK\AlertBundle\Entity\SyskUser $receiver )
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \SYSK\AlertBundle\Entity\SyskUser
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set irritantId
     *
     * @param \SYSK\AlertBundle\Entity\SyskIrritant $irritantId
     *
     * @return SyskMessage
     */
    public function setIrritantId(\SYSK\AlertBundle\Entity\SyskIrritant $irritantId = null)
    {
        $this->irritantId = $irritantId;

        return $this;
    }

    /**
     * Get irritantId
     *
     * @return \SYSK\AlertBundle\Entity\SyskIrritant
     */
    public function getIrritantId()
    {
        return $this->irritantId;
    }
}
