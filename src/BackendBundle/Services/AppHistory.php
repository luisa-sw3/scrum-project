<?php

namespace BackendBundle\Services;

use Doctrine\ORM\EntityManager;
use BackendBundle\Entity as Entity;

class AppHistory {

    private $em;
    private $tokenStorage;
    private $container;
    private $translator;

    public function __construct(EntityManager $em, $tokenStorage, $container) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /**
     * Permite ingresar en base de datos una accion en el historial de eventos 
     * de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Entity\Item $item
     * @param integer $action
     * @param string|null $sufix
     */
    public function saveItemHistory($item, $action, $additionalData = null, $sufix = null) {

        $history = new Entity\ItemHistory();
        $history->setAction($action);
        $history->setActionSufix($sufix);
        if (is_array($additionalData) && !empty($additionalData)) {
            $history->setAdditionalData(json_encode($additionalData));
        }
        $history->setItem($item);
        $history->setUserOwner($this->tokenStorage->getToken()->getUser());

        $this->em->persist($history);
        $this->em->flush();
    }

    public function findItemChanges(Entity\Item $previousItem, Entity\Item $newItem) {

        if ($previousItem->getType() != $newItem->getType()) {
            $changes = array('before' => $this->translator->trans($previousItem->getTextType()), 'after' => $this->translator->trans($newItem->getTextType()));
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_TYPE_MODIFIED, $changes);
        }

        if ($previousItem->getTitle() != $newItem->getTitle()) {
            $changes = array('before' => $previousItem->getTitle(), 'after' => $newItem->getTitle());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_TITLE_MODIFIED, $changes);
        }

        if ($previousItem->getDescription() != $newItem->getDescription()) {
            $changes = array('before' => $previousItem->getDescription(), 'after' => $newItem->getDescription());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_DESCRIPTION_MODIFIED, $changes);
        }

        if ($previousItem->getPriority() != $newItem->getPriority()) {
            $changes = array('before' => $previousItem->getPriority(), 'after' => $newItem->getPriority());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_PRIORITY_MODIFIED, $changes);
        }

        if ($previousItem->getDesignedUser() != $newItem->getDesignedUser()) {

            if (!$previousItem->getDesignedUser() && $newItem->getDesignedUser()) {
                $changes = array('before' => $this->translator->trans('backend.item.no_user'), 'after' => $newItem->getDesignedUser() . "");
                $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_USER_ASSIGNED, $changes, " : " . $newItem->getDesignedUser());
            } elseif ($previousItem->getDesignedUser() && $newItem->getDesignedUser()) {
                $changes = array('before' => $previousItem->getDesignedUser() . "", 'after' => $newItem->getDesignedUser() . "");
                $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_USER_REASSIGNED, $changes, " : " . $newItem->getDesignedUser());
            } elseif ($previousItem->getDesignedUser() && !$newItem->getDesignedUser()) {
                $changes = array('before' => $previousItem->getDesignedUser() . "", 'after' => $this->translator->trans('backend.item.no_user'));
                $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_USER_ASSIGN_CLEARED, $changes);
            }
        }

        if ($previousItem->getEstimatedHours() != $newItem->getEstimatedHours()) {
            $changes = array('before' => $previousItem->getEstimatedHours(), 'after' => $newItem->getEstimatedHours());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_ESTIMATED_HOURS_MODIFIED, $changes);
        }

        if ($previousItem->getEffortFibonacci() != $newItem->getEffortFibonacci()) {
            $changes = array('before' => $previousItem->getEffortFibonacci(), 'after' => $newItem->getEffortFibonacci());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_ESTIMATED_EFFORT_MODIFIED, $changes);
        }

        if ($previousItem->getEffortTShirt() != $newItem->getEffortTShirt()) {
            $changes = array('before' => $previousItem->getEffortTShirt(), 'after' => $newItem->getEffortTShirt());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_ESTIMATED_EFFORT_MODIFIED, $changes);
        }

        if ($previousItem->getWorkedHours() != $newItem->getWorkedHours()) {
            $changes = array('before' => $previousItem->getWorkedHours(), 'after' => $newItem->getWorkedHours());
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_WORKED_HOURS_MODIFIED, $changes);
        }

        if ($previousItem->getSprint() != $newItem->getSprint()) {
            if (!$previousItem->getSprint() && $newItem->getSprint()) {
                $changes = array('before' => $this->translator->trans('backend.item.no_sprint'), 'after' => $newItem->getSprint() . "");
                $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_SPRINT_ASSIGNED, $changes, " : " . $newItem->getSprint());
            } elseif ($previousItem->getSprint() && $newItem->getSprint()) {
                $changes = array('before' => $previousItem->getSprint() . "", 'after' => $newItem->getSprint() . "");
                $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_SPRINT_MOVED, $changes, " : " . $newItem->getSprint());
            } elseif ($previousItem->getSprint() && !$newItem->getSprint()) {
                $changes = array('before' => $previousItem->getSprint() . "", 'after' => $this->translator->trans('backend.item.no_sprint'));
                $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_MOVED_PRODUCT_BACKLOG, $changes);
            }
        }

        if ($previousItem->getStatus() != $newItem->getStatus()) {
            $changes = array('before' => $this->translator->trans($previousItem->getTextStatus()), 'after' => $this->translator->trans($newItem->getTextStatus()));
            $this->saveItemHistory($newItem, Entity\ItemHistory::ITEM_STATUS_MODIFIED, $changes);
        }
    }

}
