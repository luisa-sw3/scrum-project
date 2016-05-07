<?php

namespace BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TimeTrackingRepository extends EntityRepository {

    public function findUserTimeTracking($userId, $search = array()) {

        $em = $this->getEntityManager();

        $extraQuery = '';
        $parameters = array('userId' => $userId);
        if (isset($search['startDate'])) {
            $extraQuery .= ' AND time.date >= :startDate ';
            $parameters['startDate'] = $search['startDate'];
        }
        
        if (isset($search['endDate'])) {
            $extraQuery .= ' AND time.date <= :endDate ';
            $parameters['endDate'] = $search['endDate'];
        }
        
        if (isset($search['project'])) {
            $extraQuery .= ' AND time.project = :project ';
            $parameters['project'] = $search['project'];
        }

        $orderBy = " ORDER BY time.date DESC, time.startTime DESC ";
        
        $query = "
        SELECT time
        FROM BackendBundle:TimeTracking time
        WHERE time.user = :userId 
        AND time.endTime IS NOT NULL".$extraQuery.$orderBy;

        $consult = $em->createQuery($query);
        $consult->setParameters($parameters);

        return $consult->getResult();
    }
    
    /**
     * Permite obtener el total de segundos trabajado por un usuario
     * acorde a los parametros de busqueda
     * @param integer $userId identificador del usuario
     * @param array[string] $search parameros de busqueda
     * @return type
     */
    public function findWorkedTime($userId, $search = array()) {

        $em = $this->getEntityManager();

        $extraQuery = '';
        $parameters = array('userId' => $userId);
        if (isset($search['startDate'])) {
            $extraQuery .= ' AND time.date >= :startDate ';
            $parameters['startDate'] = $search['startDate'];
        }
        
        if (isset($search['endDate'])) {
            $extraQuery .= ' AND time.date <= :endDate ';
            $parameters['endDate'] = $search['endDate'];
        }
        
        if (isset($search['project'])) {
            $extraQuery .= ' AND time.project = :project ';
            $parameters['project'] = $search['project'];
        }

        $orderBy = " ORDER BY time.date DESC, time.startTime DESC ";
        
        $query = "
        SELECT SUM(time.workedTime)
        FROM BackendBundle:TimeTracking time
        WHERE time.user = :userId 
        AND time.endTime IS NOT NULL".$extraQuery.$orderBy;

        $consult = $em->createQuery($query);
        $consult->setParameters($parameters);

        return $consult->getSingleScalarResult();
    }

}
