<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ItemRepository extends EntityRepository {

    /**
     * Permite realizar busquedas simples y avanzadas sobre los items de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 02/02/2016
     * @param array[string] $search
     * @param string $order
     * @param string $direction
     * @return type
     */
    public function findItems($search = array(), $order = array()) {

        $where = '';
        $parameters = array();
        if (isset($search['project'])) {
            $where .= ' AND i.project = :project ';
            $parameters['project'] = $search['project'];
        }

        if (isset($search['sprint'])) {
            if ($search['sprint'] != null && $search['sprint'] != Sprint::ALL_SPRINTS) {
                $where .= ' AND i.sprint :sprint ';
                $parameters['sprint'] = $search['sprint'];
            } else {
                $where .= ' AND i.sprint IS NOT NULL ';
            }
        } else {
            $where .= ' AND i.sprint IS NULL ';
        }

        if (isset($search['parent'])) {
            if ($search['parent'] != null) {
                $where .= ' AND i.parent :parent ';
                $parameters['parent'] = $search['parent'];
            }
        } else {
            $where .= ' AND i.parent IS NULL ';
        }

        if (isset($search['item_free_text'])) {
            $where .= ' AND (i.title LIKE :item_free_text OR i.description LIKE :item_free_text)';
            $parameters['item_free_text'] = "%" . $search['item_free_text'] . "%";
        }

        $orderBy = ' ORDER BY i.priority DESC';
        if (!empty($order)) {
            if (isset($order['sprint'])) {
                $orderBy = ' ORDER BY s.startDate ' . $order['sprint'] . ', i.priority DESC';
            }
        }


        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT i
        FROM BackendBundle:Item i
        LEFT JOIN BackendBundle:Sprint s WITH (i.sprint = s.id)
        WHERE 1=1 " . $where . $orderBy);
        $consult->setParameters($parameters);

        return $consult;
    }

    /**
     * Permite encontrar todos la familia que se deriva de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 16/02/2016
     * @param string $parentId Identificador del item padre
     * @return array[Item] listado de items
     */
    public function findAllChildren($parentId) {
        $orderBy = ' ORDER BY i.creationDate DESC';

        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT i
        FROM BackendBundle:Item i
        LEFT JOIN BackendBundle:Item parent1 WITH (i.parent = parent1.id)
        LEFT JOIN BackendBundle:Item parent2 WITH (parent1.parent = parent2.id)
        LEFT JOIN BackendBundle:Item parent3 WITH (parent2.parent = parent3.id)
        LEFT JOIN BackendBundle:Item parent4 WITH (parent3.parent = parent4.id)
        LEFT JOIN BackendBundle:Item parent5 WITH (parent4.parent = parent5.id)
        LEFT JOIN BackendBundle:Item parent6 WITH (parent5.parent = parent6.id)
        WHERE i.parent = :parentId 
        OR parent1.parent = :parentId
        OR parent2.parent = :parentId
        OR parent3.parent = :parentId
        OR parent4.parent = :parentId
        OR parent5.parent = :parentId
        OR parent6.parent = :parentId
        " . $orderBy);
        $consult->setParameter('parentId', $parentId);

        return $consult->getResult();
    }
    
    public function findByType($projectId, $type) {

        $repository = $this->getEntityManager();
        
        $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.type = :type");
        $query->setParameter('projectId', $projectId);
        $query->setParameter('type', $type);
        
        return $query->getResult();
    }

    public function findByStatus($projectId, $status) {

        $repository = $this->getEntityManager();
        
        $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.status = :status");
        $query->setParameter('projectId', $projectId);
        $query->setParameter('status', $status);
        
        return $query->getResult();
    }
    
    public function findByTypeStatus($projectId, $status, $type) {

        $repository = $this->getEntityManager();
        
        $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.status = :status
            AND i.type = :type");
        $query->setParameter('projectId', $projectId);
        $query->setParameter('status', $status);
        $query->setParameter('type', $type);
        
        return $query->getResult();
    }
    
    public function totalWorkHours($projectId) {

        $repository = $this->getEntityManager();
        
        $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId");
        $query->setParameter('projectId', $projectId);
        
        
        return $query->getSingleScalarResult();
    }
    
    public function totalWorkHoursByType($projectId, $type) {

        $repository = $this->getEntityManager();
        
        $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.type = :type");
        $query->setParameter('projectId', $projectId);
        $query->setParameter('type', $type);
        
        return $query->getSingleScalarResult();
    }

    public function findByTypeUserSprint($projectId, $type, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);
            $query->setParameter('type', $type);

            return $query->getResult();
        }

        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :uId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('uId', $usrId);
            $query->setParameter('type', $type);

            return $query->getResult();
        }

        $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :uId
            AND i.sprint = :sId
            AND i.type = :type");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('uId', $usrId);
        $query->setParameter('sId', $sprintId);
        $query->setParameter('type', $type);

        return $query->getResult();
    }

    public function findByTypeStatusUserSprint($projectId, $type, $status, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId
            AND i.type = :type
            AND i.status = :status");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);
            $query->setParameter('type', $type);
            $query->setParameter('status', $status);

            return $query->getResult();
        }

        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :uId
            AND i.type = :type
            AND i.status = :status");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('uId', $usrId);
            $query->setParameter('type', $type);
            $query->setParameter('status', $status);

            return $query->getResult();
        }

        $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :uId
            AND i.sprint = :sId
            AND i.type = :type
            AND i.status = :status");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('uId', $usrId);
        $query->setParameter('sId', $sprintId);
        $query->setParameter('type', $type);
        $query->setParameter('status', $status);

        return $query->getResult();
    }

    public function totalEstHoursByUserSprint($projectId, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por todos los usuarios que existen y un sprint seleccionado
         */
        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);

            return $query->getSingleScalarResult();
        }

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por un usuario seleccionado y todos los sprints de este
         */
        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('usrId', $usrId);

            return $query->getSingleScalarResult();
        }

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por un usuario seleccionado y un sprint seleccionado
         */
        $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId
            AND i.sprint = :sId");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('usrId', $usrId);
        $query->setParameter('sId', $sprintId);

        return $query->getSingleScalarResult();
    }

    public function totalWorkHoursByUserSprint($projectId, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por todos los usuarios que existen y un sprint seleccionado
         */
        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);

            return $query->getSingleScalarResult();
        }

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por un usuario seleccionado y todos los sprints de este
         */
        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('usrId', $usrId);

            return $query->getSingleScalarResult();
        }

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por un usuario seleccionado y un sprint seleccionado
         */
        $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId
            AND i.sprint = :sId");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('usrId', $usrId);
        $query->setParameter('sId', $sprintId);

        return $query->getSingleScalarResult();
    }

    public function totalWorkHoursByTypeUserSprint($projectId, $type, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por todos los usuarios que existen y un sprint seleccionado
         */
        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);
            $query->setParameter('type', $type);

            return $query->getSingleScalarResult();
        }

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por un usuario seleccionado y todos los sprints de este
         */
        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('usrId', $usrId);
            $query->setParameter('type', $type);

            return $query->getSingleScalarResult();
        }

        /* Consulta que permite calcular las horas estimadas en un projecto
         * por un usuario seleccionado y un sprint seleccionado
         */
        $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId
            AND i.sprint = :sId
            AND i.type = :type");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('usrId', $usrId);
        $query->setParameter('sId', $sprintId);
        $query->setParameter('type', $type);

        return $query->getSingleScalarResult();
    }

    public function totalEstHours($projectId) {

        $repository = $this->getEntityManager();

        $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId");
        $query->setParameter('projectId', $projectId);


        return $query->getSingleScalarResult();
    }

}
