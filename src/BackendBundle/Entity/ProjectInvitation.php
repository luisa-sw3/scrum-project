<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Util\Util;

/**
 * ProjectInvitation
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
 * @ORM\Table(name="project_invitation")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ProjectInvitation {

    use Consecutive;

    /**
     * Constantes para los estados de las invitaciones a proyectos
     */
    const STATUS_CANCELED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = 3;
    
    /**
     * @ORM\Id
     * @ORM\Column(name="prin_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Fecha en la que se realiza la invitacion al proyecto
     * @ORM\Column(name="prin_date", type="datetime", nullable=true)
     */
    protected $date;
    
    /**
     * Fecha en la que se acepta la invitacion al proyecto
     * @ORM\Column(name="prin_accepted_date", type="datetime", nullable=true)
     */
    protected $acceptedDate;
    
    /**
     * Fecha en la que se cancela la invitacion al proyecto
     * @ORM\Column(name="prin_canceled_date", type="datetime", nullable=true)
     */
    protected $canceledDate;

    /**
     * Usuario que se invita al proyecto
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="prin_user_id", referencedColumnName="user_id", nullable=true)
     */
    protected $user;

    /**
     * Proyecto asociado a la invitacion
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="prin_project_id", referencedColumnName="proj_id", nullable=true)
     */
    protected $project;

    /**
     * Rol el cual se asignara al usuario en caso de aceptar la invitacion
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="prin_role_id", referencedColumnName="role_id", nullable=true)
     */
    protected $role;
    
    /**
     * Estado de la invitacion (0 = Cancelada , 1 = Activa, 2 = Rechazada)
     * @ORM\Column(name="prin_status", type="integer", nullable=true)
     */
    protected $status;
    
     /**
     * Usuario quien ingresa la invitacion al sistema
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="prin_user_owner_id", referencedColumnName="user_id", nullable=true)
     */
    protected $userOwner;

    function getId() {
        return $this->id;
    }

    function getDate() {
        return $this->date;
    }

    function getUser() {
        return $this->user;
    }

    function getProject() {
        return $this->project;
    }

    function getRole() {
        return $this->role;
    }

    function getStatus() {
        return $this->status;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setProject($project) {
        $this->project = $project;
    }

    function setRole($role) {
        $this->role = $role;
    }

    function setStatus($status) {
        $this->status = $status;
    }
    
    function getAcceptedDate() {
        return $this->acceptedDate;
    }

    function getCanceledDate() {
        return $this->canceledDate;
    }

    function setAcceptedDate($acceptedDate) {
        $this->acceptedDate = $acceptedDate;
    }

    function setCanceledDate($canceledDate) {
        $this->canceledDate = $canceledDate;
    }
    
    function getUserOwner() {
        return $this->userOwner;
    }

    function setUserOwner($userOwner) {
        $this->userOwner = $userOwner;
    }

    public function __toString() {
        return $this->getUser() . "";
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getDate() === null) {
            $this->setDate(Util::getCurrentDate());
        }
        if ($this->getStatus() === null) {
            $this->setStatus(self::STATUS_ACTIVE);
        }
    }

}
