<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
 * @ORM\Table(name="settings")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Settings {

    /**
     * Constantes para pintar los formatos de fecha en la aplicacion
     */
    const DATE_FORMAT_1 = 'Y-m-d';
    const DATE_FORMAT_2 = 'm-d-Y';
    const DATE_FORMAT_3 = 'd-m-Y';
    
    /**
     * Constantes para pintar los formatos de hora en la aplicacion
     */
    const HOUR_FORMAT_1 = 'H:i';
    const HOUR_FORMAT_2 = 'H:i:s';
    const HOUR_FORMAT_3 = 'h:i a';
    const HOUR_FORMAT_4 = 'h:i:s a';
    
    /**
     * @ORM\Id
     * @ORM\Column(name="sett_id", type="string", length=36)
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * Formato de fecha
     * @ORM\Column(name="sett_format_date", type="string", length=25, nullable=true)
     * @Assert\NotBlank()
     */
    protected $dateFormat;
    
    /**
     * Formato de hora
     * @ORM\Column(name="sett_format_hour", type="string", length=25, nullable=true)
     * @Assert\NotBlank()
     */
    protected $hourFormat;


    function getId() {
        return $this->id;
    }

    function getDateFormat() {
        return $this->dateFormat;
    }

    function setDateFormat($dateFormat) {
        $this->dateFormat = $dateFormat;
    }
    
    function getHourFormat() {
        return $this->hourFormat;
    }

    function setHourFormat($hourFormat) {
        $this->hourFormat = $hourFormat;
    }
    
    function getFullDateFormat(){
        return $this->getDateFormat().' '.$this->getHourFormat();
    }

    public function __toString() {
        return $this->getDateFormat();
    }

    /**
     * Set Page initial status before persisting
     * @ORM\PrePersist
     */
    public function setDefaults() {
        
    }

}
