<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;
use BackendBundle\Form\ProjectInvitationType;

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
    
    /**
     * Permite registrar invitaciones de usuarios a proyectos
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     */
    public function addAction(Request $request, $id)
    {
        $closeFancy = false;
        
        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('BackendBundle:Project')->find($id);
        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            $closeFancy = true;
        }
        
        $projectInvitation = new Entity\ProjectInvitation();
        $form = $this->createForm(ProjectInvitationType::class, $projectInvitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            var_dump($_POST);die();
            
            $projectInvitation->setProject($project);
            //setRole
            //setUser
            $em->persist($projectInvitation);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user_project.message_invitation_send'));
            $closeFancy = true;
        }

        return $this->render('BackendBundle:ProjectTeam:addCollaborator.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
            'menu' => 'menu_projects',
            'closeFancy' => $closeFancy
        ));
    }

}
