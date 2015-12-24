<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * HistoryItem
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="history_item")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class HistoryItem {

    use Consecutive;

    const ITEM_DESCRIPTION_MODIFIED = 1;
    const ITEM_STATUS_CHANGED = 2;
    const ITEM_MOVED_FROM_SPRINT = 3;

    /**
     * @ORM\Id
     * @ORM\Column(name="hite_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Fecha en la que se realiza el comentario
     * @ORM\Column(name="hite_date", type="datetime", nullable=true)
     */
    protected $date;

    /**
     * Accion realizada sobre el item (1 = Modificada, 2 = Se cambio de estado, etc..)
     * @ORM\Column(name="hite_action", type="integer", nullable=false)
     * @Assert\NotBlank()
     */
    protected $action;

    /**
     * Informacion adicional sobre la accion realizada
     * @ORM\Column(name="hite_additional_data", type="text", nullable=true)
     */
    protected $additionalData;

    /**
     * Usuario que realiza la accion sobre el item
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="hite_user_owner_id", referencedColumnName="user_id")
     */
    protected $userOwner;

    /**
     * Item sobre el cual se realiza la accion
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="hite_item_id", referencedColumnName="item_id", nullable=true)
     */
    protected $item;

    function getId() {
        return $this->id;
    }

    function getDate() {
        return $this->date;
    }

    function getAction() {
        return $this->action;
    }

    function getAdditionalData() {
        return $this->additionalData;
    }

    function getUserOwner() {
        return $this->userOwner;
    }

    function getItem() {
        return $this->item;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setAction($action) {
        $this->action = $action;
    }

    function setAdditionalData($additionalData) {
        $this->additionalData = $additionalData;
    }

    function setUserOwner($userOwner) {
        $this->userOwner = $userOwner;
    }

    function setItem($item) {
        $this->item = $item;
    }

    public function __toString() {
        return $this->getTextAction();
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getDate() === null) {
            $this->setDate(Util::getCurrentDate());
        }
    }

    public function getTextAction() {
        return '';
    }

}
