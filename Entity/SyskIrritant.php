<?php

namespace SYSK\AlertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SyskUser
 *
 * @ORM\Table(name="sysk_irritant")
 * @ORM\Entity(repositoryClass="SYSK\AlertBundle\Repository\SyskIrritantRepository")
 */
class SyskIrritant
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
     * @var String
     *
     * @ORM\Column(name="irritant_token", type="string", nullable=false), length=500, nullable=true)
     */
    private $irritantToken;

    /**
     * @var text
     *
     * @ORM\Column(name="irritant_message", type="text", nullable=false)
     */
    private $irritantMessage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    protected $deleted;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
    * @ORM\OneToMany(targetEntity="SYSK\AlertBundle\Entity\SyskMessage", mappedBy="irritantId", cascade={"persist"})
    */
    protected $syskIrritantMessages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->deleted              = false;
        $this->createdAt            = new \DateTime();
        $this->syskIrritantMessages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set irritantToken
     *
     * @param string $irritantToken
     *
     * @return SyskIrritant
     */
    public function setIrritantToken($irritantToken)
    {
        $this->irritantToken = $irritantToken;

        return $this;
    }

    /**
     * Get irritantToken
     *
     * @return string
     */
    public function getIrritantToken()
    {
        return $this->irritantToken;
    }

    /**
     * Set irritantMessage
     *
     * @param string $irritantMessage
     *
     * @return SyskIrritant
     */
    public function setIrritantMessage($irritantMessage)
    {
        $this->irritantMessage = $irritantMessage;

        return $this;
    }

    /**
     * Get irritantMessage
     *
     * @return string
     */
    public function getIrritantMessage()
    {
        return $this->irritantMessage;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return SyskIrritant
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
     * @return SyskIrritant
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
     * Add syskIrritantMessage
     *
     * @param \SYSK\AlertBundle\Entity\SyskMessage $syskIrritantMessage
     *
     * @return SyskIrritant
     */
    public function addSyskIrritantMessage(\SYSK\AlertBundle\Entity\SyskMessage $syskIrritantMessage)
    {
        $this->syskIrritantMessages[] = $syskIrritantMessage;

        return $this;
    }

    /**
     * Remove syskIrritantMessage
     *
     * @param \SYSK\AlertBundle\Entity\SyskMessage $syskIrritantMessage
     */
    public function removeSyskIrritantMessage(\SYSK\AlertBundle\Entity\SyskMessage $syskIrritantMessage)
    {
        $this->syskIrritantMessages->removeElement($syskIrritantMessage);
    }

    /**
     * Get syskIrritantMessages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSyskIrritantMessages()
    {
        return $this->syskIrritantMessages;
    }
}
