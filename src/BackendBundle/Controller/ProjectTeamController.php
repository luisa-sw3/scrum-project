<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Project Team controller.
 */
class ProjectTeamController extends Controller {

    /**
     * Permite listar los usuarios que estan asignados a un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     * @param string $id identificador del proyecto
     * @return type
     */
    public function indexAction($id) {
        $em = $this->getDoctrine()->getManager();

        $search = array('project' => $id);
        $order = array('assignationDate' => 'ASC');

        $project = $em->getRepository('BackendBundle:Project')->find($id);
        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $users = $em->getRepository('BackendBundle:UserProject')->findBy($search, $order);

        return $this->render('BackendBundle:ProjectTeam:index.html.twig', array(
                    'users' => $users,
                    'project' => $project,
                    'menu' => 'menu_projects'
        ));
    }

}
