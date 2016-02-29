<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * Project
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="BackendBundle\Entity\ProjectRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Project {

    use Consecutive;

    /**
     * Constantes para los metodos de estimacion de esfuerzo
     */
    const METHOD_TSHIRT_SIZE = 1;
    const METHOD_FIBONACCI = 2;

    /**
     * @ORM\Id
     * @ORM\Column(name="proj_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Nombre del Proyecto
     * @ORM\Column(name="proj_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Descripcion general del proyecto
     * @ORM\Column(name="proj_description", type="text", nullable=true)
     */
    protected $description;

    /**
     * Fecha de creacion del proyecto en el sistema
     * @ORM\Column(name="proj_creation_date", type="datetime", nullable=true)
     */
    protected $creationDate;

    /**
     * Fecha de iniciación del proyecto
     * @ORM\Column(name="proj_start_date", type="datetime", nullable=false)
     * @Assert\NotBlank()
     */
    protected $startDate;

    /**
     * Fecha estimada para la finalizacion del proyecto
     * @ORM\Column(name="proj_estimated_date", type="datetime", nullable=true)
     */
    protected $estimatedDate;

    /**
     * Usuario quien realiza la creación del proyecto
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="proj_user_owner", referencedColumnName="user_id")
     */
    protected $userOwner;

    /**
     * Configuraciones del proyecto
     * @ORM\ManyToOne(targetEntity="Settings")
     * @ORM\JoinColumn(name="proj_settings_id", referencedColumnName="sett_id", nullable=true)
     */
    protected $settings;

    /**
     * Ultimo consecutivo de los items creados en un proyecto
     * @ORM\Column(name="proj_last_item_consecutive", type="integer", nullable=true)
     */
    protected $lastItemConsecutive;

    /**
     * Ultimo consecutivo de los sprints creados en un proyecto
     * @ORM\Column(name="proj_last_sprint_consecutive", type="integer", nullable=true)
     */
    protected $lastSprintConsecutive;

    /**
     * Numero entero para saber el metodo utilizado para la estimacion 
     * del esfuerzo de los items de un proyecto (1 = Tallas de Camisas, 2 Serie Fibonacci)
     * @ORM\Column(name="proj_effort_method", type="integer", nullable=true)
     */
    protected $effortEstimationMethod;

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

    function getCreationDate() {
        return $this->creationDate;
    }

    function getStartDate() {
        return $this->startDate;
    }

    function getEstimatedDate() {
        return $this->estimatedDate;
    }

    function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    function setEstimatedDate($estimatedDate) {
        $this->estimatedDate = $estimatedDate;
    }

    function getUserOwner() {
        return $this->userOwner;
    }

    function setUserOwner(User $userOwner) {
        $this->userOwner = $userOwner;
    }

    function getSettings() {
        return $this->settings;
    }

    function setSettings($settings) {
        $this->settings = $settings;
    }

    public function __toString() {
        return $this->getName();
    }

    function getLastItemConsecutive() {
        return $this->lastItemConsecutive;
    }

    function setLastItemConsecutive($lastItemConsecutive) {
        $this->lastItemConsecutive = $lastItemConsecutive;
    }

    function getLastSprintConsecutive() {
        return $this->lastSprintConsecutive;
    }

    function setLastSprintConsecutive($lastSprintConsecutive) {
        $this->lastSprintConsecutive = $lastSprintConsecutive;
    }

    function getEffortEstimationMethod() {
        return $this->effortEstimationMethod;
    }

    function setEffortEstimationMethod($effortEstimationMethod) {
        $this->effortEstimationMethod = $effortEstimationMethod;
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getCreationDate() === null) {
            $this->setCreationDate(Util::getCurrentDate());
        }
    }

    /**
     * Permite obtener el valor de la variable de idioma para el metodo
     * de estimacion de esfuerzo usado para el proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 29/02/2016
     * @param type $method
     * @return string
     */
    public function getTextEffortMethod($method = null) {
        if (!$method) {
            $method = $this->getEffortEstimationMethod();
        }

        $langVar = '';
        switch ($method) {
            case self::METHOD_TSHIRT_SIZE:
                $langVar = 'backend.project.effort_method_tshirt';
                break;
            case self::METHOD_FIBONACCI:
                $langVar = 'backend.project.effort_method_fibonacci';
                break;
            default:
                break;
        }
        return $langVar;
    }

}
