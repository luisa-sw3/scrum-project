<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SprintRepository extends EntityRepository {

    public function findSprintsByUserProject($projectId, $userId) {

        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT s
        FROM BackendBundle:Sprint s
        JOIN BackendBundle:UserSprint usr WITH(s.id = usr.user)
        WHERE s.project = :projectId
        AND usr.user = :userId");

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

    public function findByProyect($projectId, $status) {

        $repository = $this->getEntityManager();

        $query = $repository->createQuery(" 
            Select s 
            FROM BackendBundle:Sprint s
            WHERE s.project = :projectId");
        $query->setParameter('projectId', $projectId);

        return $query->getResult();
    }

}
