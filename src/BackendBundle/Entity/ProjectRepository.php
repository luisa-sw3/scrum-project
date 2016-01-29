<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository {

    /**
     * Permite buscar los proyectos en los que un usuario tiene participacion
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 21/01/2016
     * @param string $userId identificador del usuario
     * @return type
     */
    public function findProjectsByUser($userId) {

        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT pr
        FROM BackendBundle:Project pr
        JOIN BackendBundle:UserProject uspr WITH(pr.id = uspr.project)
        WHERE uspr.user = :userId
        ORDER BY pr.creationDate DESC");
        $consult->setParameter('userId', $userId);

        return $consult->getResult();
    }

}
