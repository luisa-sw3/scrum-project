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

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }
        
        $users = $em->getRepository('BackendBundle:User')->findUsersByProject($id);
        
        $search = array('project' => $project->getId());
        $order = array('creationDate' => 'ASC');

        $sprints = $em->getRepository('BackendBundle:Sprint')->findBy($search, $order);

        return $this->render('BackendBundle:Project/Report:userReportIndex.html.twig', array(
                    'project' => $project,
                    'users' => $users,
                    'sprints' => $sprints,
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
    public function userReportAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }
        
        $users = $em->getRepository('BackendBundle:User')->findUsersByProject($id);
        
        $search = array('project' => $project->getId());
        $order = array('creationDate' => 'ASC');

        $sprints = $em->getRepository('BackendBundle:Sprint')->findBy($search, $order);

        return $this->render('BackendBundle:Project/Report:userReport.html.twig', array(
                    'project' => $project,
                    'users' => $users,
                    'sprints' => $sprints,
                    'menu' => self::MENU
        ));
    }
}