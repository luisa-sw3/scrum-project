<?php

namespace BackendBundle\Entity;

/**
 * Este trait es utilizado en todas las entidades para no duplicar el codigo
 * del consecutivo
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 */
trait Consecutive {

    /**
     * @ORM\Column(name="consecutive_id", type="integer", nullable=true)
     */
    protected $consecutive;

    function getConsecutive() {
        return $this->consecutive;
    }

    function setConsecutive($consecutive) {
        $this->consecutive = $consecutive;
    }

}
