<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * UserProject
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="user_project")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserProject {

    use Consecutive;

    /**
     * @ORM\Id
     * @ORM\Column(name="uspr_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Fecha en la que se asigna el proyecto a un usuario
     * @ORM\Column(name="uspr_assignation_date", type="datetime", nullable=true)
     */
    protected $assignationDate;

    /**
     * Usuario que se asigna al proyecto
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="uspr_user_id", referencedColumnName="user_id")
     * @Assert\NotBlank()
     */
    protected $user;

    /**
     * Proyecto que se asigna al usuario
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="uspr_project_id", referencedColumnName="proj_id")
     */
    protected $project;

    /**
     * Rol que se asigna al usuario para participar en el proyecto
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="uspr_role_id", referencedColumnName="role_id", nullable=true)
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

    function getProject() {
        return $this->project;
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

    function setProject($project) {
        $this->project = $project;
    }

    function setRole($role) {
        $this->role = $role;
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
