<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * Item
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="item")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Item {

    use Consecutive;

    /**
     * Constantes para los tipos de items que se pueden crear
     */
    const TYPE_USER_HISTORY = 1;
    const TYPE_TASK = 2;
    const TYPE_BUG = 3;
    const TYPE_IMPROVEMENT = 4;
    const TYPE_IDEA = 5;

    /**
     * Constantes para los estados de los items
     */
    const STATUS_NEW = 1;
    const STATUS_INVESTIGATING = 2;
    const STATUS_CONFIRMED = 3;
    const STATUS_NOT_A_BUG = 4;
    const STATUS_BEING_WORKED_ON = 5;
    const STATUS_NEAR_COMPLETION = 6;
    const STATUS_READY_FOR_TESTING = 7;
    const STATUS_TESTING = 8;
    const STATUS_CLOSED = 9;
    const STATUS_POSTPONED = 10;
    const STATUS_DONE = 11;
    const STATUS_FIXED = 12;

    /**
     * @ORM\Id
     * @ORM\Column(name="item_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Titulo del Item
     * @ORM\Column(name="item_title", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * Descripcion de las caracteristicas o funciones del item
     * @ORM\Column(name="item_description", type="text", nullable=true)
     */
    protected $description;

    /**
     * Fecha de creacion del item en el sistema
     * @ORM\Column(name="item_creation_date", type="datetime", nullable=true)
     */
    protected $creationDate;

    /**
     * Tipo del item (1 = Historia de Usuario, 2 = Tarea, etc..)
     * @ORM\Column(name="item_type", type="integer")
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * Estado del item (1 = Nuevo, 2 = En Imnvestigacion, 3 = Confirmado, etc..)
     * @ORM\Column(name="item_status", type="integer", nullable=true)
     */
    protected $status;
    
    /**
     * Tiempo estimado en horas para la realizacion del item
     * @ORM\Column(name="item_estimated_hours", type="float", nullable=true)
     */
    protected $estimatedHours;
    
    /**
     * Tiempo transcurrido en horas para la realizacion del item
     * @ORM\Column(name="item_transcurred_hours", type="float", nullable=true)
     */
    protected $transcurredHours;
    
    /**
     * Prioridad del item (100 = Urgente , 0 = No es prioridad)
     * @ORM\Column(name="item_priority", type="integer", nullable=true)
     */
    protected $priority;
    
    /**
     * Usuario quien realiza la creación del item
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="item_user_owner", referencedColumnName="user_id")
     */
    protected $userOwner;
    
    /**
     * Proyecto al que pertenece el item
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="item_project_id", referencedColumnName="proj_id")
     */
    protected $project;
    
    /**
     * Sprint al que pertenece el usuario item
     * @ORM\ManyToOne(targetEntity="Sprint")
     * @ORM\JoinColumn(name="ussp_sprint_id", referencedColumnName="sprint_id", nullable=true)
     */
    protected $sprint;
    
    /**
     * Usuario responsable de la realizacion del item
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="item_designed_user_id", referencedColumnName="user_id", nullable=true)
     */
    protected $userDesigned;
    
    /**
     * Item al cual puede estar relacionado como item dependiente
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="item_parent_id", referencedColumnName="item_id", nullable=true)
     */
    protected $parent;

    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }

    function getCreationDate() {
        return $this->creationDate;
    }

    function getType() {
        return $this->type;
    }

    function getStatus() {
        return $this->status;
    }

    function getEstimatedHours() {
        return $this->estimatedHours;
    }

    function getTranscurredHours() {
        return $this->transcurredHours;
    }

    function getPriority() {
        return $this->priority;
    }

    function getUserOwner() {
        return $this->userOwner;
    }

    function getProject() {
        return $this->project;
    }

    function getSprint() {
        return $this->sprint;
    }

    function getUserDesigned() {
        return $this->userDesigned;
    }

    function getParent() {
        return $this->parent;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setEstimatedHours($estimatedHours) {
        $this->estimatedHours = $estimatedHours;
    }

    function setTranscurredHours($transcurredHours) {
        $this->transcurredHours = $transcurredHours;
    }

    function setPriority($priority) {
        $this->priority = $priority;
    }

    function setUserOwner($userOwner) {
        $this->userOwner = $userOwner;
    }

    function setProject($project) {
        $this->project = $project;
    }

    function setSprint(Sprint $sprint = null) {
        $this->sprint = $sprint;
    }

    function setUserDesigned(User $userDesigned = null) {
        $this->userDesigned = $userDesigned;
    }

    function setParent(Item $parent = null) {
        $this->parent = $parent;
    }

    
    public function __toString() {
        return $this->getTitle();
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getCreationDate() === null) {
            $this->setCreationDate(Util::getCurrentDate());
        }
        if ($this->getStatus() === null) {
            $this->setStatus(self::STATUS_NEW);
        }
    }

}
