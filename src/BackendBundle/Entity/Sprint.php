<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * Sprint
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="sprint")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Sprint {

    use Consecutive;

    const ALL_SPRINTS = 'all_sprints';
    
    /**
     * Constanted para los estados de los Sprints
     */
    const STATUS_PLANNED = 1;
    const STATUS_IN_PROCESS = 2;
    const STATUS_STOPPED = 3;
    const STATUS_FINISHED = 4;

    /**
     * @ORM\Id
     * @ORM\Column(name="sprint_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Nombre del Sprint
     * @ORM\Column(name="sprint_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Descripcion general del sprint
     * @ORM\Column(name="sprint_description", type="text", nullable=true)
     */
    protected $description;

    /**
     * Fecha de iniciación del sprint
     * @ORM\Column(name="sprint_start_date", type="datetime", nullable=false)
     * @Assert\NotBlank()
     */
    protected $startDate;

    /**
     * Fecha estimada para la finalizacion del sprint
     * @ORM\Column(name="sprint_end_date", type="datetime", nullable=true)
     */
    protected $estimatedDate;

    /**
     * Proyecto al que pertenece el Sprint
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="sprint_project_id", referencedColumnName="proj_id")
     */
    protected $project;

    /**
     * Usuario quien realiza la creación del sprint
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="sprint_user_owner", referencedColumnName="user_id")
     */
    protected $userOwner;

    /**
     * Boolean que permite saber si durante el Sprint se van a trabajar los fines
     * de semana (Sabados y Domingos)
     * @ORM\Column(name="sprint_is_working_weekends", type="boolean", nullable=true)
     */
    protected $isWorkingWeekends;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     * @ORM\OneToMany(targetEntity="SprintDay", mappedBy="sprint")
     */
    protected $sprintDays;

    /**
     * Fecha de creacion del sprint en el sistema
     * @ORM\Column(name="sprint_creation_date", type="datetime", nullable=true)
     */
    protected $creationDate;

    /**
     * Estado del sprint(1 = Planeado, 2 = En proceso, 3 = Detenido, 4 = Finalizado)
     * @ORM\Column(name="sprint_status", type="integer", nullable=true)
     */
    protected $status;

    /**
     * Almacena el tiempo estimado para la realizacion de todos los items del sprint
     * @ORM\Column(name="sprint_estimated_time", type="float", nullable=true)
     */
    protected $estimatedTime;

    /**
     * Almacena el tiempo trabajado por el equipo para la realizacion de todos los items del sprint
     * @ORM\Column(name="sprint_worked_time", type="float", nullable=true)
     */
    protected $workedTime;

    /**
     * Almacena el tiempo restante para la realizacion de todos los items del sprint
     * @ORM\Column(name="sprint_remaining_time", type="float", nullable=true)
     */
    protected $remainingTime;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getStartDate() {
        return $this->startDate;
    }

    function getEstimatedDate() {
        return $this->estimatedDate;
    }

    function getProject() {
        return $this->project;
    }

    function getUserOwner() {
        return $this->userOwner;
    }

    function getCreationDate() {
        return $this->creationDate;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    function setEstimatedDate($estimatedDate) {
        $this->estimatedDate = $estimatedDate;
    }

    function setProject($project) {
        $this->project = $project;
    }

    function setUserOwner($userOwner) {
        $this->userOwner = $userOwner;
    }

    function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    function getIsWorkingWeekends() {
        return $this->isWorkingWeekends;
    }

    function setIsWorkingWeekends($isWorkingWeekends) {
        $this->isWorkingWeekends = $isWorkingWeekends;
    }

    function getSprintDays() {
        return $this->sprintDays;
    }

    function setSprintDays($sprintDays) {
        $this->sprintDays = $sprintDays;
    }

    function getEstimatedTime() {
        return $this->estimatedTime;
    }

    function getWorkedTime() {
        return $this->workedTime;
    }

    function setEstimatedTime($estimatedTime) {
        $this->estimatedTime = $estimatedTime;
    }

    function setWorkedTime($workedTime) {
        $this->workedTime = $workedTime;
    }

    function getRemainingTime() {
        return $this->remainingTime;
    }

    function setRemainingTime($remainingTime) {
        $this->remainingTime = $remainingTime;
    }

    function getStatus() {
        return $this->status;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    public function __toString() {
        return $this->getName();
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
            $this->setStatus(self::STATUS_PLANNED);
        }
    }

    /**
     * Permite obtener el nombre de la variable de idioma correspondiente
     * al estado del sprint
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 17/02/2016
     * @param integer $status
     * @return string
     */
    public function getTextStatus($status = null) {

        if (!$status) {
            $status = $this->getStatus();
        }

        $langVar = '';
        switch ($status) {
            case self::STATUS_PLANNED:
                $langVar = 'backend.sprint.status_planned';
                break;
            case self::STATUS_IN_PROCESS:
                $langVar = 'backend.sprint.status_in_process';
                break;
            case self::STATUS_STOPPED:
                $langVar = 'backend.sprint.status_stopped';
                break;
            case self::STATUS_FINISHED:
                $langVar = 'backend.sprint.status_finished';
                break;
            default:
                break;
        }
        return $langVar;
    }

    public function getTextWorkWeekends() {
        if($this->getIsWorkingWeekends()){
            return 'backend.global.yes';
        }
        return 'backend.global.no';
    }

}
