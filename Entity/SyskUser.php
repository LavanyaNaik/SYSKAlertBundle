<?php

namespace SYSK\AlertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SyskUser
 *
 * @ORM\Table(name="sysk_user")
 * @ORM\Entity(repositoryClass="SYSK\AlertBundle\Repository\SyskUserRepository")
 */
class SyskUser
{
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
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="token_count", type="integer", nullable=false)
     */
    private $tokenCount;

    /**
    * @ORM\OneToMany(targetEntity="SYSK\AlertBundle\Entity\SyskMessage", mappedBy="sender", cascade={"persist", "remove"})
    */
    protected $syskSentMessages;

    /**
    * @ORM\OneToMany(targetEntity="SYSK\AlertBundle\Entity\SyskMessage", mappedBy="receiver", cascade={"persist", "remove"})
    */
    protected $syskReceivedMessages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tokenCount           = 0;
        $this->syskSentMessages     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->syskReceivedMessages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set userId
     *
     * @param integer $userId
     *
     * @return SyskUser
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set tokenCount
     *
     * @param integer $tokenCount
     *
     * @return SyskUser
     */
    public function setTokenCount($tokenCount)
    {
        $this->tokenCount = $tokenCount;

        return $this;
    }

    /**
     * Get tokenCount
     *
     * @return integer
     */
    public function getTokenCount()
    {
        return $this->tokenCount;
    }

    /**
     * Add syskSentMessage
     *
     * @param \SYSK\AlertBundle\Entity\SyskMessage $syskSentMessage
     *
     * @return SyskUser
     */
    public function addSyskSentMessage(\SYSK\AlertBundle\Entity\SyskMessage $syskSentMessage)
    {
        $this->syskSentMessages[] = $syskSentMessage;

        return $this;
    }

    /**
     * Remove syskSentMessage
     *
     * @param \SYSK\AlertBundle\Entity\SyskMessage $syskSentMessage
     */
    public function removeSyskSentMessage(\SYSK\AlertBundle\Entity\SyskMessage $syskSentMessage)
    {
        $this->syskSentMessages->removeElement($syskSentMessage);
    }

    /**
     * Get syskSentMessages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSyskSentMessages()
    {
        return $this->syskSentMessages;
    }

    /**
     * Add syskReceivedMessage
     *
     * @param \SYSK\AlertBundle\Entity\SyskMessage $syskReceivedMessage
     *
     * @return SyskUser
     */
    public function addSyskReceivedMessage(\SYSK\AlertBundle\Entity\SyskMessage $syskReceivedMessage)
    {
        $this->syskReceivedMessages[] = $syskReceivedMessage;

        return $this;
    }

    /**
     * Remove syskReceivedMessage
     *
     * @param \SYSK\AlertBundle\Entity\SyskMessage $syskReceivedMessage
     */
    public function removeSyskReceivedMessage(\SYSK\AlertBundle\Entity\SyskMessage $syskReceivedMessage)
    {
        $this->syskReceivedMessages->removeElement($syskReceivedMessage);
    }

    /**
     * Get syskReceivedMessages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSyskReceivedMessages()
    {
        return $this->syskReceivedMessages;
    }
}
