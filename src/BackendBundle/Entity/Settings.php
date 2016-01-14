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
    const DATE_FORMAT_2 = 'Y-M-d';
    const DATE_FORMAT_3 = 'm-d-Y';
    const DATE_FORMAT_4 = 'M-d-Y';
    const DATE_FORMAT_5 = 'd-m-Y';
    const DATE_FORMAT_6 = 'd-M-Y';

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

    function getFullDateFormat() {
        return $this->getDateFormat() . ' ' . $this->getHourFormat();
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

    /**
     * Permite obtener el formato en PHP para setear las fechas en los 
     * formularios y demas partes de la aplicacion
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 14/01/2016
     * @param string $format
     * @return string
     */
    public function getPHPDateFormat($format = null) {

        if (!$format) {
            $format = $this->getDateFormat();
        }

        $phpFormat = '';
        switch ($format) {
            case self::DATE_FORMAT_1:
                $phpFormat = 'y-M-d';
                break;
            case self::DATE_FORMAT_2:
                $phpFormat = 'y-M-d';
                break;
            case self::DATE_FORMAT_3:
                $phpFormat = 'M-d-y';
                break;
            case self::DATE_FORMAT_4:
                $phpFormat = 'M-d-y';
                break;
            case self::DATE_FORMAT_5:
                $phpFormat = 'd-M-y';
                break;
            case self::DATE_FORMAT_6:
                $phpFormat = 'd-M-y';
                break;
            default:
                break;
        }
        return $phpFormat;
    }

}
