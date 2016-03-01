<?php

namespace BackendBundle\Services;

use Doctrine\ORM\EntityManager;
use BackendBundle\Entity as Entity;

class AppHistory {

    private $em;
    private $tokenStorage;

    public function __construct(EntityManager $em, $tokenStorage) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Permite ingresar en base de datos una accion en el historial de eventos 
     * de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Entity\Item $item
     * @param integer $action
     * @param string|null $sufix
     */
    public function saveItemHistory($item, $action, $additionalData = null, $sufix = null) {

        $history = new Entity\ItemHistory();
        $history->setAction($action);
        $history->setActionSufix($sufix);
        if (is_array($additionalData) && !empty($additionalData)) {
            $history->setAdditionalData(json_encode($additionalData));
        }
        $history->setItem($item);
        $history->setUserOwner($this->tokenStorage->getToken()->getUser());

        $this->em->persist($history);
        $this->em->flush();
    }

}
