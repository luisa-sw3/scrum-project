<?php

namespace BackendBundle\Services;

use Doctrine\ORM\EntityManager;
use BackendBundle\Entity\Settings;

class AppSettings {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * Permite obtener una instancia con las configuraciones del proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function getSettings() {

        $settings = $this->em->getRepository('BackendBundle:Settings')->findOneBy(array(), array());

        if (!$settings) {
            $settings = new Settings();
            $settings->setDateFormat(Settings::DATE_FORMAT_1);
            $settings->setHourFormat(Settings::HOUR_FORMAT_1);
            $this->em->persist($settings);
            $this->em->flush();
        }

        return $settings;
    }

}
