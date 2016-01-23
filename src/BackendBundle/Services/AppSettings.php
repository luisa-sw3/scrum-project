<?php

namespace BackendBundle\Services;

use Doctrine\ORM\EntityManager;
use BackendBundle\Entity as Entity;

class AppSettings {

    private $em;
    private $tokenStorage;
    private $user;

    public function __construct(EntityManager $em, $tokenStorage) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        if ($this->tokenStorage->getToken()) {
            $this->user = $this->tokenStorage->getToken()->getUser();
        }
    }

    /**
     * Permite obtener el listado de invitaciones pendientes a proyectos
     * para el usuario logueado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @return array[Entity\ProjectInvitation] listado de invitaciones pendientes
     */
    public function getPendingInvitations() {
        $pendingInvitations = array();
        if ($this->user) {
            $search = array('user' => $this->user->getId(), 'status' => Entity\ProjectInvitation::STATUS_ACTIVE);
            $order = array('date' => 'ASC');
            $pendingInvitations = $this->em->getRepository('BackendBundle:ProjectInvitation')->findBy($search, $order);
        }
        return $pendingInvitations;
    }

    /**
     * Permite obtener el listado de projectos que tiene un usuario asociado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 21/01/2016
     * @return array[Entity\UserProject] listado de proyectos
     */
    public function getProjectsByUser() {
        $projects = array();
        if ($this->user) {
            $search = array('user' => $this->user->getId());
            $order = array('assignationDate' => 'DESC');
            $projects = $this->em->getRepository('BackendBundle:UserProject')->findBy($search, $order);
        }
        return $projects;
    }

    public function getDefaultDateFormat() {
        return Entity\Settings::DATE_FORMAT_1;
    }
    
    public function getDefaultHourFormat() {
        return Entity\Settings::HOUR_FORMAT_1;
    }
    
    public function getDefaultFullDateFormat() {
        return Entity\Settings::DATE_FORMAT_1.' '.Entity\Settings::DATE_FORMAT_1;
    }

}
