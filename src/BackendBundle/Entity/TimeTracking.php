<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Util\Util;

/**
 * Time Tracking
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 22/03/2016
 * @ORM\Table(name="time_tracking")
 * @ORM\Entity(repositoryClass="BackendBundle\Entity\TimeTrackingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TimeTracking {

    /**
     * @ORM\Id
     * @ORM\Column(name="track_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Usuario quien realiza la tarea relacionada en el registro
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="track_user_id", referencedColumnName="user_id", nullable=false)
     */
    protected $user;

    /**
     * Item al cual puede estar relacionado el registro de tiempo
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="track_item_id", referencedColumnName="item_id", nullable=true)
     */
    protected $item;

    /**
     * Proyecto al que esta relacionado el registro de tiempo
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="track_project_id", referencedColumnName="proj_id")
     */
    protected $project;

    /**
     * Descripcion de la tarea realizada en el tiempo registrado
     * @ORM\Column(name="track_description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * Dia en el cual se realiza el registro de tiempo
     * @ORM\Column(name="track_date", type="date", nullable=false)
     * @Assert\NotBlank()
     */
    protected $date;

    /**
     * Hora en la que inicia el registro de tiempo
     * @ORM\Column(name="track_start_time", type="datetime", nullable=false)
     * @Assert\NotBlank()
     */
    protected $startTime;

    /**
     * Hora en la que finaliza el registro de tiempo
     * @ORM\Column(name="track_end_time", type="datetime", nullable=true)
     */
    protected $endTime;

    /**
     * Tiempo trabajado en el registro (almacenado en segundos)
     * @ORM\Column(name="track_worked_time", type="integer", nullable=true)
     */
    protected $workedTime;

    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getItem() {
        return $this->item;
    }

    function getProject() {
        return $this->project;
    }

    function getDescription() {
        return $this->description;
    }

    function getDate() {
        return $this->date;
    }

    function getStartTime() {
        return $this->startTime;
    }

    function getEndTime() {
        return $this->endTime;
    }

    function getWorkedTime() {
        return $this->workedTime;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setItem($item) {
        $this->item = $item;
    }

    function setProject($project) {
        $this->project = $project;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setStartTime($startTime) {
        $this->startTime = $startTime;
    }

    function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    function setWorkedTime($workedTime) {
        $this->workedTime = $workedTime;
    }

    public function __toString() {
        if (!empty($this->getItem())) {
            return $this->getItem()->getTitle();
        }
        return $this->getDescription();
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        if ($this->getDate() === null) {
            $this->setDate(Util::getCurrentDate());
        }
        if ($this->getStartTime() === null) {
            $this->setStartTime(Util::getCurrentDate());
        }

        if ($this->getStartTime() != null && $this->getEndTime() != null) {
            $startTime = $this->getStartTime();
            $interval = $startTime->diff($this->getEndTime());

            var_dump($interval->format("%H:%I:%S"));
            die();
        }
    }

    /**
     * Permite obtener el tiempo transcurrido en formato natural
     * @return string
     */
    public function getTimeOnNaturalLanguage() {
        $startTime = $this->getStartTime();
        $interval = $startTime->diff($this->getEndTime());

        $hours = $interval->format("%H");
        $minutes = $interval->format("%I");
        $seconds = $interval->format("%S");
        
        $naturalTime = '';
        if ((int)$hours > 0) {
            $naturalTime .= (int)$hours.' h. ';
            $naturalTime .= (int)$minutes.' min. ';
            $naturalTime .= (int)$seconds.' s.';
        } elseif ((int)$minutes > 0) {
            $naturalTime .= (int)$minutes.' min. ';
            $naturalTime .= (int)$seconds.' s.';
        } elseif ((int)$seconds > 0) {
            $naturalTime .= (int)$seconds.' s.';
        } 
        return $naturalTime;
    }

}
