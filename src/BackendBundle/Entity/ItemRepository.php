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

        if (isset($search['item_type'])) {
            $where .= ' AND i.type = :item_type ';
            $parameters['item_type'] = $search['item_type'];
        }

        if (isset($search['item_designed_user'])) {
            $where .= ' AND i.designedUser = :item_designed_user ';
            $parameters['item_designed_user'] = $search['item_designed_user'];
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

    //------------------ Consultas para los reportes ------------------//

    /**
     * Permite listar los items de un proyecto segun el criterio de busqueda
     * @author Luisa Pereira 27/04/2016
     * @param string $projectId Id del projecto
     * @param string $usrId Id del usuario
     * @param string $sprintId Id del sprint
     * @param string $type Id del tipo de item
     * @param string $status Id del estado de item
     * @return array[Item] listado de items
     */
    public function findByTypeStatusUserSprint($projectId, $usrId, $sprintId, $type, $status) {

        $repository = $this->getEntityManager();

        $consulta = " Select i FROM BackendBundle:Item i ";
        $condicion = "";

        $query = $repository->createQuery($consulta . $condicion);


        // lista de items (por tipo) de un proyecto
        if ($usrId == 'all' && $sprintId == 'all' && $status == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('type', $type);

            return $query->getResult();
        }

        // lista de items (por tipo y por sprint) de un proyecto
        if ($usrId == 'all' && $status == 'all') {
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

        // lista de items (por tipo y por usuario) de un proyecto
        if ($sprintId == 'all' && $status == 'all') {
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

        // lista de items (por tipo, por usuario y por sprint)de un proyecto
        if ($status == 'all') {
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

        // lista de items (por tipo y estado) de un proyecto
        if ($usrId == 'all' && $sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId            
            AND i.type = :type
            AND i.status = :status");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('type', $type);
            $query->setParameter('status', $status);

            return $query->getResult();
        }

        // lista de items (por tipo, estado y usuario) de un proyecto
        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId            
            AND i.type = :type
            AND i.status = :status
            AND i.designedUser = :uId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('type', $type);
            $query->setParameter('status', $status);
            $query->setParameter('uId', $usrId);

            return $query->getResult();
        }

        // lista de items (por tipo, estado y sprint) de un proyecto
        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId            
            AND i.type = :type
            AND i.status = :status
            AND i.sprint = :sId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('type', $type);
            $query->setParameter('status', $status);
            $query->setParameter('sId', $sprintId);

            return $query->getResult();
        }

        // lista de items (por tipo, estado, usuario y sprint) de un proyecto
        $query = $repository->createQuery(" 
            Select i 
            FROM BackendBundle:Item i
            WHERE i.project = :projectId            
            AND i.type = :type
            AND i.status = :status
            AND i.designedUser = :uId
            AND i.sprint = :sId");

        $query->setParameter('projectId', $projectId);
        $query->setParameter('type', $type);
        $query->setParameter('status', $status);
        $query->setParameter('uId', $usrId);
        $query->setParameter('sId', $sprintId);

        return $query->getResult();
    }

    /**
     * Permite calcular el total de horas trabajadas en un proyecto segun 
     * el criterio de busqueda
     * @author Luisa F. Pereira 27/04/2016
     * @param string $projectId Identificador del projecto
     * @param string $type Id del tipo de item
     * @param string $usrId Id del usuario
     * @param string $sprintId Id del sprint
     * @return  total de horas trabajadas
     */
    public function totalWorkHoursByTypeUserSprint($projectId, $type, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        // suma el total de horas trabajadas en todo el proyecto
        if ($usrId == 'all' && $sprintId == 'all' && $type == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId");

            $query->setParameter('projectId', $projectId);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas trabajadas en un item de un proyecto
        if ($usrId == 'all' && $sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('type', $type);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas trabajadas en un sprint X
        if ($usrId == 'all' && $type == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas trabajadas en todo el proyecto por usuario X
        if ($sprintId == 'all' && $type == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.workedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('usrId', $usrId);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas trabajadas en un sprint X y un item especifico
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

        // suma el total de horas trabajadas en todo el proyecto por usuario X y un item especifico
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

        // suma el total de horas trabajadas en todo el proyecto por usuario X, sprint X y un item especifico

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

    /**
     * Permite calcular el total de horas estimadas en un proyecto segun 
     * el criterio de busqueda
     * @author Luisa F. Pereira 27/04/2016
     * @param string $projectId Identificador del projecto
     * @param string $type Id del tipo de item
     * @param string $usrId Id del usuario
     * @param string $sprintId Id del sprint
     * @return  total de horas estimadas
     */
    public function totalEstHoursByTypeUserSprint($projectId, $type, $usrId, $sprintId) {

        $repository = $this->getEntityManager();

        // suma el total de horas estimadas en todo el proyecto
        if ($usrId == 'all' && $sprintId == 'all' && $type == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId");

            $query->setParameter('projectId', $projectId);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas estimadas en un item de un proyecto
        if ($usrId == 'all' && $sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('type', $type);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas estimadas en un sprint X
        if ($usrId == 'all' && $type == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas estimadas en todo el proyecto por usuario X
        if ($sprintId == 'all' && $type == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('usrId', $usrId);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas estimadas en un sprint X y un item especifico
        if ($usrId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.sprint = :sId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('sId', $sprintId);
            $query->setParameter('type', $type);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas estimadas en todo el proyecto por usuario X y un item especifico
        if ($sprintId == 'all') {
            $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
            FROM BackendBundle:Item i
            WHERE i.project = :projectId
            AND i.designedUser = :usrId
            AND i.type = :type");

            $query->setParameter('projectId', $projectId);
            $query->setParameter('usrId', $usrId);
            $query->setParameter('type', $type);

            return $query->getSingleScalarResult();
        }

        // suma el total de horas estimadas en todo el proyecto por usuario X, sprint X y un item especifico

        $query = $repository->createQuery(" 
            Select SUM(i.estimatedHours) AS totalHours
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

}
