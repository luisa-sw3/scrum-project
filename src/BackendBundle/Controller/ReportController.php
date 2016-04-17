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
class ReportController extends Controller {

    const MENU = 'menu_project_reports';

    /**
     * Permite mostrar los tipos de reportes que se pueden generar
     * @author Luisa Pereira 28/03/2016
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

        return $this->render('BackendBundle:Project/Report:index.html.twig', array(
                    'project' => $project,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite mostrar el reporte del proyecto en cual estamos parados
     * @author Jorge dd/mm/aaaa
     * @author Luisa Pereira 16/04/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function indexProjectAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $totalItems = $em->getRepository('BackendBundle:Item')->findByType($project, "3");

        $doneTask = $em->getRepository('BackendBundle:Item')->findByTypeStatus($project, "11", "3");

        $totalHours = $em->getRepository('BackendBundle:Item')->totalWorkHours($project);

        $taskHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByType($project, "3");

        $canceledTasks = $em->getRepository('BackendBundle:Item')->findByTypeStatus($project, "9", "3");

        $ppTasks = $em->getRepository('BackendBundle:Item')->findByTypeStatus($project, "10", "3");

        $foundErr = $em->getRepository('BackendBundle:Item')->findByType($project, "4");

        $fixedErr = $em->getRepository('BackendBundle:Item')->findByTypeStatus($project, "12", "4");

        $errHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByType($project, "4");

        $totalChanreRqst = $em->getRepository('BackendBundle:Item')->findByType($project, "6");

        $doneChangeRqst = $em->getRepository('BackendBundle:Item')->findByTypeStatus($project, "11", "6");

        $crHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByType($project, "6");

        $sprints = $em->getRepository('BackendBundle:Sprint')->findByProject($project);

        $sprintsDone = $em->getRepository('BackendBundle:Sprint')->findByStatus($project, "4");


        return $this->render('BackendBundle:Project/Report:projectReport.html.twig', array(
                    'project' => $project,
                    'totalItems' => $totalItems,
                    'done' => $doneTask,
                    'workHours' => $totalHours,
                    'taskHours' => $taskHours,
                    'tCanceled' => $canceledTasks,
                    'tPostponed' => $ppTasks,
                    'foundErr' => $foundErr,
                    'fixedErr' => $fixedErr,
                    'errHrs' => $errHours,
                    'totalCR' => $totalChanreRqst,
                    'doneCR' => $doneChangeRqst,
                    'crHrs' => $crHours,
                    'sprints' => $sprints,
                    'doneSprints' => $sprintsDone,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite seleccionar el tipo de reporte que se desea generar.
     * Puede ser por un usuario o todos y el sprint specifico o todos los sprints
     * @author Luisa Pereira 28/03/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function indexUserAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $users = $em->getRepository('BackendBundle:User')->findUsersByProject($id);

        return $this->render('BackendBundle:Project/Report:userReportIndex.html.twig', array(
                    'project' => $project,
                    'users' => $users,
                    'menu' => self::MENU
        ));
    }

    public function getSprintsByUserAction(Request $request, $id) {
        $parameters = $request->request;
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $userId = $parameters->get('user_id');
        $html = '';

        //si el servicio seleccionado es todos devolver html con todos los usuarios
        if ($userId == 'all') {

            $sprints = $em->getRepository('BackendBundle:Sprint')->findByProject($project);

            // se recorren los grupo y se arman los options
            foreach ($sprints as $sprint) {
                $html = $html . '<option value=' . $sprint->getId() . ' >' . $sprint->getName() . '</option>';
            }

            $response['result'] = '__OK__';
            $response['html'] = $html;
            $r = new Response(json_encode($response));
            $r->headers->set('Content-Type', 'application/json');
            return $r;
        }

        // listado de sprints segun el usuario seleccionado
        $sprints = $em->getRepository('BackendBundle:Sprint')->findSprintsByUserProject($id, $userId);

        // se recorren los grupo y se arman los options
        foreach ($sprints as $sprint) {
            $html = $html . '<option value=' . $sprint->getId() . ' >' . $sprint->getName() . '</option>';
        }

        $response['result'] = '__OK__';
        $response['html'] = $html;
        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

}
