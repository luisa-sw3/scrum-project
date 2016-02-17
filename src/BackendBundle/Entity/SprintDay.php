<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SprintDay
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 17/02/2016
 * @ORM\Table(name="sprint_day")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SprintDay {

    /**
     * @ORM\Id
     * @ORM\Column(name="spda_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Fecha de uno de los dias habiles del sprint
     * @ORM\Column(name="spda_date", type="date", nullable=true)
     */
    protected $date;

    /**
     * Sprint que se asigna al usuario
     * @ORM\ManyToOne(targetEntity="Sprint")
     * @ORM\JoinColumn(name="spda_sprint_id", referencedColumnName="sprint_id")
     */
    protected $sprint;


    function getId() {
        return $this->id;
    }
    
    function getDate() {
        return $this->date;
    }

    function getSprint() {
        return $this->sprint;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setSprint($sprint) {
        $this->sprint = $sprint;
    }
}