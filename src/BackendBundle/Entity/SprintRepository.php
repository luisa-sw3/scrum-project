<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SprintRepository extends EntityRepository {

    /**
     * Permite listar los sprints de un proyecto segun el criterio de busqueda
     * @author Luisa F. Pereira 27/04/2016
     * @param string $projectId Id del projecto
     * @param string $userId Id del usuario
     * @return  array[Sprint] de horas estimadas
     */
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

    /**
     * Permite listar los sprints de un proyecto segun el criterio de busqueda
     * @author Luisa F. Pereira 27/04/2016
     * @param string $projectId Id del projecto
     * @param string $status Id del estado
     * @return  array[Sprint] de horas estimadas
     */
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

    /**
     * Permite listar los sprints de un proyecto segun el criterio de busqueda
     * @author Luisa F. Pereira 27/04/2016
     * @param string $projectId Id del projecto
     * @return  array[Sprint] de horas estimadas
     */
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

    /**
     * Permite listar los sprints de un proyecto segun el criterio de busqueda
     * @author Luisa F. Pereira 27/04/2016
     * @param string $projectId Id del projecto
     * @param string $usrId Id del usuario
     * @return  array[Sprint] de horas estimadas
     */
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
