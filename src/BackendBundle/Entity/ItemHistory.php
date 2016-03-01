<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * ItemHistory
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="item_history")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ItemHistory {

    /**
     * Constantes para los posibles eventos sobre los items
     */
    const ITEM_CREATED = 1; //the item has been created
    const ITEM_TYPE_MODIFIED = 2; //the item type has been modified
    const ITEM_TITLE_MODIFIED = 3; //the item title has been modified
    const ITEM_DESCRIPTION_MODIFIED = 4; //the item description has been modified
    const ITEM_PRIORITY_MODIFIED = 5; //the item priority has been modified
    const ITEM_USER_ASSIGNED = 6; //the item has been assigned to
    const ITEM_USER_REASSIGNED = 7; //the item has been reassigned to
    const ITEM_ESTIMATED_HOURS_MODIFIED = 8; //the item estimated hours has been modified
    const ITEM_ESTIMATED_EFFORT_MODIFIED = 9; //the item estimated effort has been modified
    const ITEM_WORKED_HOURS_MODIFIED = 10; //the item worked hours has been modified
    const ITEM_SPRINT_ASSIGNED = 11; //the item has been assigned to sprint
    const ITEM_SPRINT_MOVED = 12; //the item has been moved to other sprint
    const ITEM_MOVED_PRODUCT_BACKLOG = 13; //The item has been moved to Product Backlog
    const ITEM_ATTACHMENT_ADDED = 14; //An attachment has been uploaded
    const ITEM_ATTACHMENT_DELETED = 15; //An attachment has been deleted
    const ITEM_STATUS_MODIFIED = 16; //the item title has been modified
    
    /**
     * @ORM\Id
     * @ORM\Column(name="hite_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Fecha en la que se realiza el evento en el historial
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
     * Texto adicional que se debe concatenar a la descripcion del evento
     * @ORM\Column(name="hite_action_sufix", type="string", nullable=true)
     */
    protected $actionSufix;

    /**
     * JSON con informacion adicional sobre la accion realizada
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
     * @ORM\JoinColumn(name="hite_item_id", referencedColumnName="item_id", nullable=true, onDelete="CASCADE")
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
    
    function getActionSufix() {
        return $this->actionSufix;
    }

    function setActionSufix($actionSufix) {
        $this->actionSufix = $actionSufix;
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

    /**
     * Permite obtener el nombre de la variable de idioma que alberga la
     * descripcion principal del historial del item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 29/02/2016
     * @param integer $action identificador de la accion
     * @return string
     */
    public function getTextAction($action = null) {
        
        if(!$action) {
            $action = $this->getAction();
        }
        
        $langVar = '';
        switch ($action) {
            case self::ITEM_CREATED:
                $langVar = 'backend.item_history.item_created';
                break;
            case self::ITEM_TYPE_MODIFIED:
                $langVar = 'backend.item_history.item_type_modified';
                break;
            case self::ITEM_TITLE_MODIFIED:
                $langVar = 'backend.item_history.item_title_modified';
                break;
            case self::ITEM_DESCRIPTION_MODIFIED:
                $langVar = 'backend.item_history.item_description_modified';
                break;
            case self::ITEM_PRIORITY_MODIFIED:
                $langVar = 'backend.item_history.item_priority_modified';
                break;
            case self::ITEM_USER_ASSIGNED:
                $langVar = 'backend.item_history.item_user_assigned';
                break;
            case self::ITEM_USER_REASSIGNED:
                $langVar = 'backend.item_history.item_user_reassigned';
                break;
            case self::ITEM_ESTIMATED_HOURS_MODIFIED:
                $langVar = 'backend.item_history.item_estimated_hours_modified';
                break;
            case self::ITEM_ESTIMATED_EFFORT_MODIFIED:
                $langVar = 'backend.item_history.item_estimated_effort_modified';
                break;
            case self::ITEM_WORKED_HOURS_MODIFIED:
                $langVar = 'backend.item_history.item_worked_hours_modified';
                break;
            case self::ITEM_SPRINT_ASSIGNED:
                $langVar = 'backend.item_history.item_sprint_assigned';
                break;
            case self::ITEM_SPRINT_MOVED:
                $langVar = 'backend.item_history.item_sprint_moved';
                break;
            case self::ITEM_MOVED_PRODUCT_BACKLOG:
                $langVar = 'backend.item_history.item_moved_product_backlog';
                break;
            case self::ITEM_ATTACHMENT_ADDED:
                $langVar = 'backend.item_history.item_attachment_added';
                break;
            case self::ITEM_ATTACHMENT_DELETED:
                $langVar = 'backend.item_history.item_attachment_deleted';
                break;
            case self::ITEM_STATUS_MODIFIED:
                $langVar = 'backend.item_history.item_status_modified';
                break;
            default:
                break;
        }
        
        return $langVar;
    }

}
