<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sprint controller.
 */
class ReportController extends Controller {

    const MENU = 'menu_project_reports';

    /**
     * Permite mostrar los tipos de reportes que se pueden generar
     * @author Luisa Pereira 28/03/2016
     * @param Request $request
     * @param string $id id del proyecto
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
     * Permite mostrar el reporte del proyecto en cual nos encontramos
     * @author Jorge dd/mm/aaaa
     * @author Luisa Pereira 16/04/2016
     * @param Request $request
     * @param string $id id del proyecto
     */
    public function indexProjectAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $totalItems = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "all");
        $doneTask = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "11");
        $totalHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "all", "all", "all");
        $taskHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "3", "all", "all");
        $canceledTasks = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "9");
        $ppTasks = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "10");
        $foundErr = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "4", "all");
        $fixedErr = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "4", "12");
        $errHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "4", "all", "all");
        $totalChangeRqst = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "6", "all");
        $doneChangeRqst = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "6", "11");
        $crHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "6", "all", "all");

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
                    'totalCR' => $totalChangeRqst,
                    'doneCR' => $doneChangeRqst,
                    'crHrs' => $crHours,
                    'sprints' => $sprints,
                    'doneSprints' => $sprintsDone,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite generar la vista de las opciones a seleccionar
     * Puede ser por un usuario o todos y el sprint specifico o todos los sprints
     * @author Luisa Pereira 28/03/2016
     * @param Request $request
     * @param string $id id del proyecto
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

    /**
     * Permite generar el filtro de sprints basado en el usuario seleccionado
     * Puede ser por un usuario o todos
     * @author Luisa Pereira 23/04/2016
     * @param Request $request
     * @param string $id id del proyecto
     */
    public function getSprintsByUserAction(Request $request, $id) {
        $parameters = $request->request;
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $userId = $parameters->get('user_id');
        $html = '';


        $select = $this->get('translator')->trans('backend.report.sprintSelect');
        $all = $this->get('translator')->trans('backend.report.sprintAll');
        $empty = $this->get('translator')->trans('backend.report.sprint_empty_list');

        //si el servicio seleccionado es la opcion del select devolver html vacio
        if ($userId == 'select') {

            $html = '<option value=" ">  </option>';

            $response['result'] = '__OK__';
            $response['html'] = $html;
            $r = new Response(json_encode($response));
            $r->headers->set('Content-Type', 'application/json');
            return $r;
        }

        //si el servicio seleccionado es todos devolver html con todos los sprints
        if ($userId == 'all') {

            $sprints = $em->getRepository('BackendBundle:Sprint')->findByProject($project);

            if ($sprints) {
                $html = '<option value="' . 'select' . '"> -- ' . $select . ' --  </option>';
                $html = $html . '<option value="' . 'all' . '">' . $all . '</option>';
                // se recorren los grupo y se arman los options
                foreach ($sprints as $sprint) {
                    $html = $html . '<option value="' . $sprint->getId() . '">' . $sprint->getName() . '</option>';
                }
            } else {
                $html = '<option value="' . 'nosprint' . '"> ' . $empty . ' </option>';
            }

            $response['result'] = '__OK__';
            $response['html'] = $html;
            $r = new Response(json_encode($response));
            $r->headers->set('Content-Type', 'application/json');
            return $r;
        }


        // listado de sprints segun el usuario seleccionado
        $sprints = $em->getRepository('BackendBundle:Sprint')->findByUser($project, $userId);

        if ($sprints) {
            $html = '<option value="' . 'select' . '"> -- ' . $select . ' --  </option>';
            $html = $html . '<option value="' . 'all' . '">' . $all . '</option>';
            // se recorren los grupo y se arman los options
            foreach ($sprints as $sprint) {
                $html = $html . '<option value="' . $sprint->getId() . '">' . $sprint->getName() . '</option>';
            }
        } else {
            $html = '<option value="' . 'nosprint' . '">' . $empty . '</option>';
        }

        $response['result'] = '__OK__';
        $response['html'] = $html;
        $r = new Response(json_encode($response));
        $r->headers->set('Content-Type', 'application/json');
        return $r;
    }

    /**
     * Permite mostrar el reporte del proyecto por los valores seleccionados de
     * usuario y sprint
     * @author Jorge Cardona 17/04/2016
     * @author Luisa Pereira 27/04/2016
     * @param Request $request
     * @param string $id id del proyecto
     */
    public function userReportAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);


        $userId = $_GET['user_id'];
        $sprintId = $_GET['sprint_id'];

        $allUsrs = $this->get('translator')->trans('backend.report.userAll');
        $allSprints = $this->get('translator')->trans('backend.report.sprintAll');

        $user = '';
        $sprintName = '';

        if ($userId != "all") {
            $user = $em->getRepository('BackendBundle:User')->find($userId);
        } else {
            $user = $userId;
        }


        if ($sprintId !== 'all') {
            $sprintName = $em->getRepository('BackendBundle:Sprint')->find($sprintId);
        } else {
            $sprintName = $sprintId;
        }

        $taskAssigned = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, $userId, $sprintId, "3", "all");
        $doneTask = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, $userId, $sprintId, "3", "11");
        $estHours = $em->getRepository('BackendBundle:Item')->totalEstHoursByTypeUserSprint($project, "all", $userId, $sprintId);
        $totalHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "all", $userId, $sprintId);
        $errHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "4", $userId, $sprintId);
        $foundErr = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, $userId, $sprintId, "4", "all");

        return $this->render('BackendBundle:Project/Report:userReport.html.twig', array(
                    'project' => $project,
                    'userId' => $userId,
                    'sprintId' => $sprintId,
                    'userSelect' => $user,
                    'sprintSelect' => $sprintName,
                    'assignedTasks' => $taskAssigned,
                    'doneTask' => $doneTask,
                    'estHrs' => $estHours,
                    'totalHrs' => $totalHours,
                    'errHrs' => $errHours,
                    'errFound' => $foundErr,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite generar el reporte en un PDF del proyecto en cual nos encontramos
     * @author Jorge dd/mm/aaaa
     * @author Luisa Pereira 06/05/2016
     * @param Request $request
     * @param string $id id del proyecto
     */
    public function generateReportPDFAction($id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $totalItems = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "all");
        $doneTask = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "11");
        $totalHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "all", "all", "all");
        $taskHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "3", "all", "all");
        $canceledTasks = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "9");
        $ppTasks = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "3", "10");
        $foundErr = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "4", "all");
        $fixedErr = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "4", "12");
        $errHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "4", "all", "all");
        $totalChangeRqst = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "6", "all");
        $doneChangeRqst = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, "all", "all", "6", "11");
        $crHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "6", "all", "all");

        $sprints = $em->getRepository('BackendBundle:Sprint')->findByProject($project);
        $sprintsDone = $em->getRepository('BackendBundle:Sprint')->findByStatus($project, "4");

        $name = $this->get('translator')->trans('backend.report.reports');

        $html = $this->render(
                'BackendBundle:Project/Report/PDF:projectReport.html.twig', array(
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
            'totalCR' => $totalChangeRqst,
            'doneCR' => $doneChangeRqst,
            'crHrs' => $crHours,
            'sprints' => $sprints,
            'doneSprints' => $sprintsDone,
            'menu' => self::MENU
        ));


        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                    'orientation' => 'Landscape',
                    'title' => $project->getName() . '_' . $name,
                )), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . $name . '-' . $project->getName() . '.pdf'
                )
        );
    }

    /**
     * Permite generar el reporte en un PDF del proyecto por los valores seleccionados de
     * usuario y sprint
     * @author Jorge Cardona 17/04/2016
     * @author Luisa Pereira 06/05/2016
     * @param Request $request
     * @param string $id id del proyecto
     */
    public function generateUserReportPDFAction($id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        $userId = $_GET['user_id'];
        $sprintId = $_GET['sprint_id'];

        $allUsrs = $this->get('translator')->trans('backend.report.userAll');
        $allSprints = $this->get('translator')->trans('backend.report.sprintAll');

        $user = '';
        $sprintName = '';

        if ($userId != "all") {
            $user = $em->getRepository('BackendBundle:User')->find($userId)->__toString();
        } else {
            $user = $allUsrs;
        }


        if ($sprintId !== 'all') {
            $sprintName = $em->getRepository('BackendBundle:Sprint')->find($sprintId)->getName();
        } else {
            $sprintName = $allSprints;
        }

        $taskAssigned = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, $userId, $sprintId, "3", "all");
        $doneTask = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, $userId, $sprintId, "3", "11");
        $estHours = $em->getRepository('BackendBundle:Item')->totalEstHoursByTypeUserSprint($project, "all", $userId, $sprintId);
        $totalHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "all", $userId, $sprintId);
        $errHours = $em->getRepository('BackendBundle:Item')->totalWorkHoursByTypeUserSprint($project, "4", $userId, $sprintId);
        $foundErr = $em->getRepository('BackendBundle:Item')->findByTypeStatusUserSprint($project, $userId, $sprintId, "4", "all");

        $name = $this->get('translator')->trans('backend.report.users');

        $html = $this->render(
                'BackendBundle:Project/Report/PDF:userReport.html.twig', array(
            'project' => $project,
            'userId' => $userId,
            'sprintId' => $sprintId,
            'userSelect' => $user,
            'sprintSelect' => $sprintName,
            'assignedTasks' => $taskAssigned,
            'doneTask' => $doneTask,
            'estHrs' => $estHours,
            'totalHrs' => $totalHours,
            'errHrs' => $errHours,
            'errFound' => $foundErr,
            'menu' => self::MENU
        ));


        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                    'orientation' => 'Landscape',
                    'encoding' => 'utf-8',
                    'title' => $user . '-' . $project->getName(),
                )), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . $name . '-' . $user . '.pdf'
                )
        );
    }

}
