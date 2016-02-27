<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Form\SprintType;
use BackendBundle\Entity as Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Util\Util;

/**
 * Sprint controller.
 */
class SprintController extends Controller {

    const MENU = 'menu_project_sprints';

    /**
     * Permite listar los sprints de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function indexAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $search = array('project' => $project->getId());
        $order = array('creationDate' => 'ASC');

        $sprints = $em->getRepository('BackendBundle:Sprint')->findBy($search, $order);

        return $this->render('BackendBundle:Project/Sprint:index.html.twig', array(
                    'project' => $project,
                    'sprints' => $sprints,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite crear un sprint en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function newAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $sprint = new Entity\Sprint();
        $sprint->setProject($project);

        $form = $this->createForm(SprintType::class, $sprint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $sprint->setProject($project);
            $sprint->setUserOwner($this->getUser());

            $em->persist($sprint);
            $em->flush();

            $startDate = clone $sprint->getStartDate();
            $endDate = clone $sprint->getEstimatedDate();

            //recorremos las fechas desde la inicial hasta la final del sprint
            while ($startDate <= $endDate) {

                $currentDate = $startDate->format('Y-m-d');

                //se verifica si la fecha actual viene en el POST
                $sendDate = $request->request->get($currentDate);

                if ($sendDate) {
                    $sprintDay = new Entity\SprintDay();
                    $sprintDay->setDate(new \DateTime($sendDate));
                    $sprintDay->setSprint($sprint);
                    $em->persist($sprintDay);
                }
                $startDate->modify('+1 day');
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.sprint.creation_success_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project/Sprint:new.html.twig', array(
                    'project' => $project,
                    'sprint' => $sprint,
                    'form' => $form->createView(),
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite editar la informacion de un sprint
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $sprintId identificador del sprint
     * @return type
     */
    public function editAction(Request $request, $id, $sprintId) {
        $em = $this->getDoctrine()->getManager();
        $sprint = $em->getRepository('BackendBundle:Sprint')->find($sprintId);

        if (!$sprint || ($sprint && $sprint->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.sprint.not_found_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $id));
        }
        
        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $editForm = $this->createForm(SprintType::class, $sprint);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em->persist($sprint);
            $em->flush();

            //buscamos posibles dias del Sprint que no esten en el rango de fechas para eliminarlos
            $outOfRangeDays = $em->getRepository('BackendBundle:SprintDay')->findDaysOutOfRange($sprintId, $sprint->getStartDate(), $sprint->getEstimatedDate());
            foreach ($outOfRangeDays as $outDay) {
                $em->remove($outDay);
            }

            $startDate = clone $sprint->getStartDate();
            $endDate = clone $sprint->getEstimatedDate();

            //recorremos las fechas desde la inicial hasta la final del sprint
            while ($startDate <= $endDate) {

                $currentDate = $startDate->format('Y-m-d');

                //se verifica si la fecha actual viene en el POST
                $sendDate = $request->request->get($currentDate);

                if ($sendDate) {
                    //si viene en el POST verificamos si no esta en el sistema para el sprint
                    $searchSprintDate = array('sprint' => $sprint->getId(), 'date' => new \DateTime($sendDate));
                    $sprintDate = $em->getRepository('BackendBundle:SprintDay')->findOneBy($searchSprintDate);

                    //si no esta en el sistema para el sprint la creamos, la creamos
                    if (!$sprintDate) {
                        $sprintDay = new Entity\SprintDay();
                        $sprintDay->setDate(new \DateTime($sendDate));
                        $sprintDay->setSprint($sprint);
                        $em->persist($sprintDay);
                    }
                } else {
                    //si la fecha actual no viene en el post verificamos si esta en el sistema para el sprint para eliminarla
                    $searchSprintDate = array('sprint' => $sprint->getId(), 'date' => new \DateTime($currentDate));
                    $sprintDate = $em->getRepository('BackendBundle:SprintDay')->findOneBy($searchSprintDate);
                    if ($sprintDate) {
                        $em->remove($sprintDate);
                    }
                }

                $startDate->modify('+1 day');
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.sprint.update_success_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $sprint->getProject()->getId()));
        }

        return $this->render('BackendBundle:Project/Sprint:edit.html.twig', array(
                    'sprint' => $sprint,
                    'project' => $sprint->getProject(),
                    'edit_form' => $editForm->createView(),
                    'menu' => self::MENU
        ));
    }

    /**
     * Esta funcion permite listar el Sprint Backlog de un sprint determinado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $sprintId identificador del sprint
     * @return type
     */
    public function sprintBacklogAction(Request $request, $id, $sprintId) {
        $em = $this->getDoctrine()->getManager();

        $sprint = $em->getRepository('BackendBundle:Sprint')->find($sprintId);

        if (!$sprint || ($sprint && $sprint->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.sprint.not_found_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $id));
        }
        
        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $search = array('sprint' => $sprint->getId());
        $order = array('priority' => 'DESC');
        $allSprintItems = $em->getRepository('BackendBundle:Item')->findBy($search, $order);

        $estimatedTime = 0;
        $workedTime = 0;
        $remainingTime = 0;
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

        //verificamos si el Sprint esta en proceso
        if ($sprint->getStatus() == Entity\Sprint::STATUS_IN_PROCESS) {
            //verificamos si la fecha actual hace parte del Sprint para calcular el tiempo restante
            $searchSprintDay = array('date' => Util::getCurrentDate(), 'sprint' => $sprintId);
            $sprintDay = $em->getRepository('BackendBundle:SprintDay')->findOneBy($searchSprintDay);
            if ($sprintDay) {
                $sprintDay->setRemainingWork($remainingTime);
                $em->persist($sprintDay);
            }
        }
        $em->flush();

        $search['parent'] = NULL;
        $sprintBacklog = $em->getRepository('BackendBundle:Item')->findBy($search, $order);


        //logica para pintar la grafica Burdown del Sprint
        $days = $em->getRepository('BackendBundle:SprintDay')->findBy(array('sprint' => $sprintId), array('date' => 'ASC'));
        $sprintDays = count($days);

        $listDays = array();
        for ($i = 0; $i < $sprintDays; $i++) {
            $listDays[$i] = $days[$i]->getDate()->format($sprint->getProject()->getSettings()->getPHPDateFormat());
        }

        $estimatedTimePerDay = 0;
        $idealArray = array();
        if ($sprintDays > 0 && $sprint->getEstimatedTime() > 0) {
            $estimatedTimePerDay = number_format(($sprint->getEstimatedTime() / $sprintDays), 1);
            $idealArray = range(0, $sprint->getEstimatedTime() - 1, $estimatedTimePerDay);
        }

        $idealXArray = array();
        foreach ($idealArray as $value) {
            $value = trim($value);
            $idealXArray[] = 'Day ' . $value;
        }

        //datos del avance del sprint
        $actualArray = array();
        for ($i = 0; $i < $sprintDays; $i++) {
            $actualArray[$i] = $days[$i]->getRemainingWork();
        }

        return $this->render('BackendBundle:Project/Sprint:backlog.html.twig', array(
                    'project' => $sprint->getProject(),
                    'sprint' => $sprint,
                    'sprintBacklog' => $sprintBacklog,
                    'menu' => self::MENU,
                    'idealXArray' => $idealXArray,
                    'idealArray' => array_reverse($idealArray),
                    'actualArray' => $actualArray,
                    'listDays' => $listDays,
        ));
    }

    /**
     * Esta funcion permite modificar el estado de un Sprint
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/02/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @param string $sprintId identificador del sprint
     * @return \BackendBundle\Controller\JsonResponse JSON con datos de respuesta
     */
    public function modifyStatusAction(Request $request, $id, $sprintId) {
        $response = array('result' => '__OK__', 'msg' => $this->get('translator')->trans('backend.sprint.update_success_message'));
        $em = $this->getDoctrine()->getManager();
        $status = $request->request->get('status');
        $sprint = $em->getRepository('BackendBundle:Sprint')->find($sprintId);

        if (!$sprint || ($sprint && $sprint->getProject()->getId() != $id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.sprint.not_found_message');
            return new JsonResponse($response);
        }
        
        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $sprint->setStatus($status);
            $em->persist($sprint);
            $em->flush();
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite cargar dinamicamente el HTML corrspondiente a la edicion de los
     * dias laborales de un Sprint
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 27/02/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @param string $sprintId identificador del Sprint
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function htmlSprintDaysAction(Request $request, $id, $sprintId) {
        $response = array('result' => '__OK__', 'msg' => '');
        $em = $this->getDoctrine()->getManager();
        $startDate = $request->request->get('startDate');
        $estimatedDate = $request->request->get('estimatedDate');

        $project = $em->getRepository('BackendBundle:Project')->find($id);
        $sprint = $em->getRepository('BackendBundle:Sprint')->find($sprintId);
        $workingWeekends = $request->request->get('workingWeekends');

        if (!$sprint || ($sprint && $sprint->getProject()->getId() != $id)) {
            if ($sprintId == 0) {
                $sprint = new Entity\Sprint();
            } else {
                $response['result'] = '__KO__';
                $response['msg'] = $this->get('translator')->trans('backend.sprint.not_found_message');
                return new JsonResponse($response);
            }
        }

        try {
            $html = $this->renderView('BackendBundle:Project/Sprint:sprintDates.html.twig', array(
                'startDate' => $startDate,
                'estimatedDate' => $estimatedDate,
                'workingWeekends' => $workingWeekends,
                'sprint' => $sprint,
                'project' => $project,
            ));
            $response['html'] = $html;
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }
        return new JsonResponse($response);
    }

}
