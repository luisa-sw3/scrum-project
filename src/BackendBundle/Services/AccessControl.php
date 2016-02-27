<?php

namespace BackendBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use BackendBundle\Entity as Entity;
use Doctrine\ORM\EntityManager;

/*
 * AccessControl
 * Esta clase implementa metodos para verificar reglas de control de acceso
 * para los usuarios logueados en la aplicacion
 */

class AccessControl {

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

    public function isAllowedProject($projectId) {
        if ($this->user) {
            $search = array('user' => $this->user->getId(), 'project' => $projectId);
            $userProject = $this->em->getRepository('BackendBundle:UserProject')->findOneBy($search);
            if ($userProject) {
                return true;
            }
        }
        return false;
    }

}
