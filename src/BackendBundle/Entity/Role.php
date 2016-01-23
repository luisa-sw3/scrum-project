<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Role
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="role")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Role {

    use Consecutive;

    /**
     * @ORM\Id
     * @ORM\Column(name="role_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Nombre del Rol
     * @ORM\Column(name="role_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Descripcion de las caracteristicas o funciones del rol
     * @ORM\Column(name="role_description", type="text", nullable=true)
     */
    protected $description;
    
    /**
     * Boolean para saber si el rol fue añadido manualmente
     * @ORM\Column(name="role_is_manually_added", type="boolean", nullable=true)
     */
    protected $isManuallyAdded;
    
    /**
     * Proyecto al que pertenece el Rol
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="role_project_id", referencedColumnName="proj_id", nullable=true)
     */
    protected $project;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }
    
    function getIsManuallyAdded() {
        return $this->isManuallyAdded;
    }

    function setIsManuallyAdded($isManuallyAdded) {
        $this->isManuallyAdded = $isManuallyAdded;
    }
    
    function getProject() {
        return $this->project;
    }

    function setProject($project) {
        $this->project = $project;
    }

    public function __toString() {
        return $this->getName();
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        
    }

}
