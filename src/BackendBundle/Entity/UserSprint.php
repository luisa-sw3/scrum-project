<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * UserSprint
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="user_sprint")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserSprint {

    use Consecutive;

    /**
     * @ORM\Id
     * @ORM\Column(name="ussp_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Fecha en la que se asigna el usuario a un sprint
     * @ORM\Column(name="ussp_assignation_date", type="datetime", nullable=true)
     */
    protected $assignationDate;

    /**
     * Usuario que se asigna al sprint
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="ussp_user_id", referencedColumnName="user_id")
     * @Assert\NotBlank()
     */
    protected $user;

    /**
     * Sprint que se asigna al usuario
     * @ORM\ManyToOne(targetEntity="Sprint")
     * @ORM\JoinColumn(name="ussp_sprint_id", referencedColumnName="sprint_id")
     */
    protected $sprint;

    /**
     * Rol que se asigna al usuario para participar en el sprint el cual
     * por defecto es el rol que ocupa el usuario dentro del proyecto
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="ussp_role_id", referencedColumnName="role_id", nullable=true)
     */
    protected $role;

    function getId() {
        return $this->id;
    }

    function getAssignationDate() {
        return $this->assignationDate;
    }

    function getUser() {
        return $this->user;
    }

    function getRole() {
        return $this->role;
    }

    function setAssignationDate($assignationDate) {
        $this->assignationDate = $assignationDate;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setRole($role) {
        $this->role = $role;
    }
    
    function getSprint() {
        return $this->sprint;
    }

    function setSprint($sprint) {
        $this->sprint = $sprint;
    }

    public function __toString() {
        return $this->getUser() . "";
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getAssignationDate() === null) {
            $this->setAssignationDate(Util::getCurrentDate());
        }
    }

}
