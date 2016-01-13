<?php

namespace BackendBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

class Indexer {

    /**
     * Este escucha permite esteblecer el consecutivo de las entidades 
     * al momento de ser almacenadas en base de datos
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        $className = get_class($entity);

        $entityManager = $args->getEntityManager();

        $reflectedClass = new \ReflectionClass($className);

        if ($reflectedClass->hasProperty('consecutive')) {
            $order = array('consecutive' => 'DESC');

            $lastItem = $entityManager->getRepository($className)->findOneBy(array(), $order);
            if ($lastItem != null) {
                $entity->setConsecutive($lastItem->getConsecutive() + 1);
            } else {
                $entity->setConsecutive(1);
            }

            $entityManager->flush();
        }
    }

}
