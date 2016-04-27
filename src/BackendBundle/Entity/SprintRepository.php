<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SprintRepository extends EntityRepository {

    public function findSprintsByUserProject($projectId, $userId) {

        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT s
        FROM BackendBundle:Sprint s
        WHERE s.project = :projectId
        AND s.userOwner = :userId");

        $consult->setParameter('projectId', $projectId);
        $consult->setParameter('userId', $userId);

        return $consult->getResult();
    }

    public function findByStatus($projectId, $status) {

        $repository = $this->getEntityManager();

        $query = $repository->createQuery(" 
            Select s 
            FROM BackendBundle:Sprint s
            WHERE s.project = :projectId
            AND s.status = :status");
        $query->setParameter('projectId', $projectId);
        $query->setParameter('status', $status);

        return $query->getResult();
    }

    public function findByProject($projectId) {

        $repository = $this->getEntityManager();

        $query = $repository->createQuery(" 
            Select s 
            FROM BackendBundle:Sprint s
            WHERE s.project = :projectId
            ORDER BY s.name ASC");
        $query->setParameter('projectId', $projectId);

        return $query->getResult();
    }
    
    public function findByUser($projectId, $usrId) {

        $repository = $this->getEntityManager();

        $query = $repository->createQuery(" 
            Select s
            FROM BackendBundle:Sprint s
            JOIN BackendBundle:Item i WITH (i.sprint = s.id)
            WHERE s.project = :projectId
            AND i.designedUser = :usrId
            ORDER BY s.name ASC");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('usrId', $usrId);

        return $query->getResult();
    }

}