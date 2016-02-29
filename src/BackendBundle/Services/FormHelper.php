<?php

namespace BackendBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use BackendBundle\Entity as Entity;

/*
 * FormHelper
 * Esta clase implementa metodos y variables utilizadas en algunos de los formularios
 * de la aplicacion
 */

class FormHelper {

    protected $container;
    protected $translator;

    /**
     * Constructor del servicio
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param ContainerInterface $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    public function getItemTypeOptions() {
        $item = new Entity\Item();
        $typeOptions = array(
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_USER_HISTORY)) => Entity\Item::TYPE_USER_HISTORY,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_FEATURE)) => Entity\Item::TYPE_FEATURE,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_TASK)) => Entity\Item::TYPE_TASK,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_BUG)) => Entity\Item::TYPE_BUG,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_IMPROVEMENT)) => Entity\Item::TYPE_IMPROVEMENT,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_CHANGE_REQUEST)) => Entity\Item::TYPE_CHANGE_REQUEST,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_IDEA)) => Entity\Item::TYPE_IDEA,
            $this->translator->trans($item->getTextType(Entity\Item::TYPE_INVESTIGATION)) => Entity\Item::TYPE_INVESTIGATION,
        );
        return $typeOptions;
    }

    public function getItemStatusOptions() {
        $item = new Entity\Item();
        $statusOptions = array(
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_NEW)) => Entity\Item::STATUS_NEW,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_INVESTIGATING)) => Entity\Item::STATUS_INVESTIGATING,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_CONFIRMED)) => Entity\Item::STATUS_CONFIRMED,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_NOT_A_BUG)) => Entity\Item::STATUS_NOT_A_BUG,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_BEING_WORKED_ON)) => Entity\Item::STATUS_BEING_WORKED_ON,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_NEAR_COMPLETION)) => Entity\Item::STATUS_NEAR_COMPLETION,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_READY_FOR_TESTING)) => Entity\Item::STATUS_READY_FOR_TESTING,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_TESTING)) => Entity\Item::STATUS_TESTING,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_CANCELED)) => Entity\Item::STATUS_CANCELED,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_POSTPONED)) => Entity\Item::STATUS_POSTPONED,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_DONE)) => Entity\Item::STATUS_DONE,
            $this->translator->trans($item->getTextStatus(Entity\Item::STATUS_FIXED)) => Entity\Item::STATUS_FIXED,
        );
        return $statusOptions;
    }

    public function getProjectEffortMethodOptions() {
        $project = new Entity\Project();
        $effortOptions = array(
            $this->translator->trans($project->getTextEffortMethod(Entity\Project::METHOD_TSHIRT_SIZE)) => Entity\Project::METHOD_TSHIRT_SIZE,
            $this->translator->trans($project->getTextEffortMethod(Entity\Project::METHOD_FIBONACCI)) => Entity\Project::METHOD_FIBONACCI);
        return $effortOptions;
    }

    public function getItemFibonacciOptions() {
        $fibonacciOptions = array(
            1 => 1,
            2 => 2,
            3 => 3,
            5 => 5,
            8 => 8,
            13 => 13,
            21 => 21,
            34 => 34,
            55 => 55,
            89 => 89,
            100 => 100);
        return $fibonacciOptions;
    }

    public function getItemTShirtOptions() {
        $tShirtOptions = array(
            'XS' => 'XS',
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            'XXL' => 'XXL',
        );
        return $tShirtOptions;
    }

}
