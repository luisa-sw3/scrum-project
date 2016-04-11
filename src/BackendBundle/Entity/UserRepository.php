<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    /**
     * Permite encontrar los usuarios para invitar a u proyecto, se puede realizar
     * la busqueda solamente sobre los usuarios de un proyecto determinado
     * @param type $term
     * @param type $projectId
     * @return type
     */
    public function findUsersAutocomplete($term, $projectId = null) {

        $projectSQL = '';
        if ($projectId) {
            $projectSQL = ' AND u.id IN(
                    SELECT IDENTITY(up.user)
                    FROM BackendBundle:UserProject up
                    WHERE up.project = :projectId) ';
        }

        $em = $this->getEntityManager();
        $abbr = "CONCAT(CONCAT(CONCAT(CONCAT(u.name,' '),u.lastname), ' - '), u.email)";
        $consult = $em->createQuery("
        SELECT u.id, " . $abbr . " AS value, " . $abbr . " AS label
        FROM BackendBundle:User u
        WHERE u.name LIKE :term ".$projectSQL."
        ORDER BY u.name ASC");
        $consult->setParameter('term', $term);

        if ($projectId) {
            $consult->setParameter('projectId', $projectId);
        }
        return $consult->getResult();
    }

    /**
     * Permite listar todos los usuarios dentro de un proyecto
     * @param string $projectId
     * @return type
     */
    public function findUsersByProject($projectId) {

        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT u
        FROM BackendBundle:User u
        JOIN BackendBundle:UserProject uspr WITH(u.id = uspr.user)
        WHERE uspr.project = :projectId");
        $consult->setParameter('projectId', $projectId);

        return $consult->getResult();
    }

}
