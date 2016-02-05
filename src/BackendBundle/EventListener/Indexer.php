<?php

namespace BackendBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use BackendBundle\Entity as Entity;

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

            $consecutive = null;

            if ($entity instanceof Entity\Item) {
                //buscamos la cantidad de items que tiene creado un proyecto para asignar el consecutivo
                $project = $entity->getProject();
                $consecutive = $project->getLastItemConsecutive() + 1;
                $project->setLastItemConsecutive($consecutive);
                $entityManager->persist($project);
            } elseif ($entity instanceof Entity\Sprint) {
                //buscamos la cantidad de items que tiene creado un proyecto para asignar el consecutivo
                $project = $entity->getProject();
                $consecutive = $project->getLastSprintConsecutive() + 1;
                $project->setLastSprintConsecutive($consecutive);
                $entityManager->persist($project);
            } else {
                $lastItem = $entityManager->getRepository($className)->findOneBy(array(), $order);
                $consecutive = $lastItem->getConsecutive() + 1;
            }

            if ($consecutive != null) {
                $entity->setConsecutive($consecutive);
            } else {
                $entity->setConsecutive(1);
            }

            $entityManager->flush();
        }
    }

}
