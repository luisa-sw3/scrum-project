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
    public function findItems($search = array()) {

        $where = '';
        $parameters = array();
        if (isset($search['project'])) {
            $where .= ' AND i.project = :project ';
            $parameters['project'] = $search['project'];
        }

        if (isset($search['sprint'])) {
            if ($search['sprint'] != null) {
                $where .= ' AND i.sprint :sprint ';
                $parameters['sprint'] = $search['sprint'];
            }
        } else {
            $where .= ' AND i.sprint IS NULL ';
        }

        if (isset($search['item_free_text'])) {
            $where .= ' AND (i.title LIKE :item_free_text OR i.description LIKE :item_free_text)';
            $parameters['item_free_text'] = "%" . $search['item_free_text'] . "%";
        }

        $orderBy = ' ORDER BY i.priority DESC';

        $em = $this->getEntityManager();
        $consult = $em->createQuery("
        SELECT i
        FROM BackendBundle:Item i
        WHERE 1=1 " . $where . $orderBy);
        $consult->setParameters($parameters);

        return $consult;
    }

}
