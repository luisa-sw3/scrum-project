<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;
use Util\Util;
use BackendBundle\Form\TimeTracking\TimeTrackType;
use BackendBundle\Form\TimeTracking\SearchTimeTrackType;
use BackendBundle\Services\TimeTracker;

/**
 * TimeTracking controller.
 */
class TimeTrackingController extends Controller {

    const MENU = 'menu_time_tracking';

    /**
     * Permite listar los registros de tiempo trabajado de los ultimos dias
     * para el usuario logueado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 22/03/2016
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $currentDate = Util::getCurrentDate();
        $search = array('startDate' => $currentDate->modify('-'.TimeTracker::DEFAULT_DAYS_TO_SEARCH.' days'), 'endDate' => Util::getCurrentDate());

        $timeTracking = $em->getRepository('BackendBundle:TimeTracking')
                ->findUserTimeTracking($this->getUser()->getId(), $search);

        //buscamos si hay alguna tarea activa para el usuario logueado
        $searchActive = array(
            'user' => $this->getUser()->getId(),
            'endTime' => null
        );
        $order = array('date' => 'DESC', 'startTime' => 'DESC');
        $timeTrack = $em->getRepository('BackendBundle:TimeTracking')->findOneBy($searchActive, $order);
        if (!$timeTrack instanceof Entity\TimeTracking) {
            $timeTrack = new Entity\TimeTracking();
            $timeTrack->setUser($this->getUser());
        } else {
            $workedTime = $this->container->get('time_tracker')
                    ->getSecondsBetweenDates($timeTrack->getStartTime(), Util::getCurrentDate());
            $timeTrack->setWorkedTime($workedTime);
        }
        $form = $this->createForm(TimeTrackType::class, $timeTrack);
        
        $searchForm = $this->createForm(SearchTimeTrackType::class);

        return $this->render('BackendBundle:TimeTracking:index.html.twig', array(
                    'time_track' => $timeTrack,
                    'time_tracking' => $timeTracking,
                    'form' => $form->createView(),
                    'searchForm' => $searchForm->createView(),
                    'menu' => self::MENU,
                    'search' => $search,
        ));
    }

    /**
     * Esta funcion permite obtener un listado de opciones con todos los items
     * activos de un proyecto.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 05/04/2016
     * @param Request $request
     * @return JsonResponse
     */
    public function getProjectItemsAction(Request $request) {

        $response = array('result' => '__OK__', 'msg' => '');
        $em = $this->getDoctrine()->getManager();

        $projectId = trim($request->request->get('projectId'));

        $project = $em->getRepository('BackendBundle:Project')->find($projectId);

        if (!$project || !$this->container->get('access_control')->isAllowedProject($projectId)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            return new JsonResponse($response);
        }

        try {

            $search = array('project' => $projectId,
                'status' => array(
                    Entity\Item::STATUS_NEW,
                    Entity\Item::STATUS_INVESTIGATING,
                    Entity\Item::STATUS_CONFIRMED,
                    Entity\Item::STATUS_BEING_WORKED_ON,
                    Entity\Item::STATUS_BUG_DETECTED,
                    Entity\Item::STATUS_TESTING,
            ));
            $order = array('consecutive' => 'DESC');

            $items = $em->getRepository('BackendBundle:Item')->findBy($search, $order);

            $html = $this->renderView('BackendBundle:TimeTracking:projectItems.html.twig', array(
                'items' => $items,
            ));

            $response['data']['html'] = $html;
        } catch (\Exception $ex) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite iniciar un contador de tiempo para una tarea determinada
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 06/04/2016
     * @param Request $request
     * @return JsonResponse
     */
    public function startTimeAction(Request $request) {

        $response = array('result' => '__KO__', 'msg' => '');
        $em = $this->getDoctrine()->getManager();

        $timeTrack = new Entity\TimeTracking();
        $timeTrack->setUser($this->getUser());
        $form = $this->createForm(TimeTrackType::class, $timeTrack);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $parameters = $request->request->get('backendbundle_time_track_type');

            $item = null;
            if (isset($parameters['taskId']) && !empty($parameters['taskId'])) {
                $item = $em->getRepository('BackendBundle:Item')->find($parameters['taskId']);
            }

            if ($item instanceof Entity\Item &&
                    $this->container->get('access_control')->isAllowedProject($item->getProject()->getId())) {
                
                $timeTrack->setItem($item);
                $timeTrack->setProject($item->getProject());

                $em->persist($timeTrack);
                $em->flush();

                $response['result'] = '__OK__';
                $response['timeId'] = $timeTrack->getId();
            } elseif (!empty($timeTrack->getDescription())) {
                $em->persist($timeTrack);
                $em->flush();

                $response['result'] = '__OK__';
                $response['timeId'] = $timeTrack->getId();
            } else {
                $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            }
        } else {
            $response['msg'] = $this->get('translator')->trans('backend.global.unknown_error');
        }
        return new JsonResponse($response);
    }

    /**
     * Permite iniciar un contador de tiempo para una tarea determinada
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 06/04/2016
     * @param Request $request
     * @return JsonResponse
     */
    public function stopTimeAction(Request $request) {

        $response = array('result' => '__KO__', 'msg' => '');
        $em = $this->getDoctrine()->getManager();

        $timeTrackId = trim(strip_tags($request->request->get('timeId')));

        if ($timeTrackId != '') {
            $timeTrack = $em->getRepository('BackendBundle:TimeTracking')->find($timeTrackId);

            if ($timeTrack instanceof Entity\TimeTracking &&
                    $this->container->get('access_control')->isAllowedProject($timeTrack->getProject()->getId())) {

                if (empty($timeTrack->getEndTime())) {
                    $timeTrack->setEndTime(Util::getCurrentDate());

                    $workedTime = $this->container->get('time_tracker')
                            ->getSecondsBetweenDates($timeTrack->getStartTime(), $timeTrack->getEndTime());
                    $timeTrack->setWorkedTime($workedTime);
                    $em->persist($timeTrack);
                    $em->flush();

                    $response['result'] = '__OK__';
                } else {
                    $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message').".";
                }
            } else {
                $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            }
        } else {
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
        }

        return new JsonResponse($response);
    }

    /**
     * Esta funcion permite obtener el codigo HTML correspondiente al listado 
     * actualizado de los registros de tiempo del usuario logueado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 07/04/2016
     * @param Request $request Datos de la solicitud
     * @return JsonResponse JSON con html de respuesta
     */
    public function updateTimeTrackingListAction(Request $request) {
        $response = array('result' => '__OK__');
        $em = $this->getDoctrine()->getManager();

        $startDate = $request->request->get('startDate');
        $startDate = new \DateTime($startDate);
        
        $endDate = $request->request->get('endDate');
        $endDate = new \DateTime($endDate);
        $endDate->setTime(23, 59, 59);
        
        $search = array('startDate' => $startDate, 'endDate' => $endDate);

        $timeTracking = $em->getRepository('BackendBundle:TimeTracking')
                ->findUserTimeTracking($this->getUser()->getId(), $search);

        $html = $this->renderView('BackendBundle:TimeTracking:timeList.html.twig', array(
            'time_tracking' => $timeTracking,
            'search' => $search,
        ));

        $response['html'] = $html;
        return new JsonResponse($response);
    }

    /**
     * Permite eliminar un registro de tiempo de la base de datos
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 07/04/2016
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTimeAction(Request $request) {

        $response = array('result' => '__KO__', 'msg' => '');
        $em = $this->getDoctrine()->getManager();

        $timeTrackId = trim(strip_tags($request->request->get('timeId')));

        if ($timeTrackId != '') {
            $timeTrack = $em->getRepository('BackendBundle:TimeTracking')->find($timeTrackId);

            if ($timeTrack instanceof Entity\TimeTracking &&
                    $this->container->get('access_control')->isAllowedProject($timeTrack->getProject()->getId()) &&
                    $timeTrack->getUser()->getId() == $this->getUser()->getId()) {

                $em->remove($timeTrack);
                $em->flush();

                $response['result'] = '__OK__';
            } else {
                $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
            }
        } else {
            $response['msg'] = $this->get('translator')->trans('backend.item.not_found_message');
        }

        return new JsonResponse($response);
    }

}
