<?php

namespace BackendBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use BackendBundle\Entity as Entity;
use Util\Util;

/**
 * Comando encargado de verificar los Sprints activos y calcular el esfuerzo 
 * restante para que se vea reflejado en el Burndown Chart
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/02/2016
 */
class UpdateBurndownChartCommand extends ContainerAwareCommand {

    protected function configure() {

        $this->setName('update:burndown-chart')->setDescription('Comando encargado 
            de verificar los Sprints activos y calcular el esfuerzo restante 
            para que se vea reflejado en el Burndown Chart');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $container = $this->getContainer();


        $output->writeln("Looking for active Sprints...");

        $em = $container->get('doctrine')->getManager();

        //buscamos los Sprints activos
        $sprints = $em->getRepository('BackendBundle:Sprint')->findByStatus(Entity\Sprint::STATUS_IN_PROCESS);

        if (!empty($sprints)) {

            $output->writeln(count($sprints) . " Sprints found.");

            foreach ($sprints as $sprint) {

                //buscamos todos los items asociados al Sprint
                $search = array('sprint' => $sprint->getId());
                $order = array('priority' => 'DESC');
                $allSprintItems = $em->getRepository('BackendBundle:Item')->findBy($search, $order);

                $estimatedTime = 0;
                $workedTime = 0;
                $remainingTime = 0;

                //recorremos los items del Sprint para calcular los tiempos
                foreach ($allSprintItems as $item) {
                    $estimatedTime += $item->getEstimatedHours();
                    $workedTime += $item->getWorkedHours();

                    if ($item->isActive() && $item->getEstimatedHours() > $item->getWorkedHours()) {
                        $remainingTime += ($item->getEstimatedHours() - $item->getWorkedHours());
                    }
                }

                $sprint->setEstimatedTime($estimatedTime);
                $sprint->setWorkedTime($workedTime);
                $sprint->setRemainingTime($remainingTime);
                $em->persist($sprint);

                $output->writeln("Estimated, worked and remaining time updated to " . $sprint->getName() . ' in Project ' . $sprint->getProject()->getName());

                //verificamos si la fecha actual hace parte del Sprint para calcular el tiempo restante
                $searchSprintDay = array('date' => Util::getCurrentDate(), 'sprint' => $sprint->getId());
                $sprintDay = $em->getRepository('BackendBundle:SprintDay')->findOneBy($searchSprintDay);
                if ($sprintDay) {
                    $sprintDay->setRemainingWork($remainingTime);
                    $em->persist($sprintDay);
                }
            }
            $em->flush();
        } else {
            $output->writeln("No active Sprints found.");
        }
    }

}
