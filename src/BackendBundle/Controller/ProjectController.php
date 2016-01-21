<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BackendBundle\Form\ProjectType;
use BackendBundle\Entity as Entity;

/**
 * Project controller.
 */
class ProjectController extends Controller
{
    /**
     * Lists all Project entities.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $search = array('userOwner' => $this->getUser()->getId());
        $projects = $em->getRepository('BackendBundle:Project')->findBy($search);

        return $this->render('BackendBundle:Project:index.html.twig', array(
            'projects' => $projects,
            'menu' => 'menu_projects'
        ));
    }

    /**
     * Creates a new Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function newAction(Request $request)
    {
        $project = new Entity\Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $project->setUserOwner($this->getUser());
            $em->persist($project);
            
            //asociamos el usuario al proyecto que acaba de crear
            $userProject = new Entity\UserProject();
            $userProject->setProject($project);
            $userProject->setUser($this->getUser());
            $em->persist($userProject);
            
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.project.creation_success_message'));
            return $this->redirectToRoute('backend_projects');
        }

        return $this->render('BackendBundle:Project:new.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
            'menu' => 'menu_projects'
        ));
    }

    /**
     * Finds and displays a Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function showAction(Entity\Project $project)
    {
        $deleteForm = $this->createDeleteForm($project);

        return $this->render('BackendBundle:Project:show.html.twig', array(
            'project' => $project,
            'delete_form' => $deleteForm->createView(),
            'menu' => 'menu_projects'
        ));
    }

    /**
     * Displays a form to edit an existing Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function editAction(Request $request, Entity\Project $project)
    {
        $deleteForm = $this->createDeleteForm($project);
        $editForm = $this->createForm(ProjectType::class, $project);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.project.update_success_message'));
            return $this->redirectToRoute('backend_projects');
        }

        return $this->render('BackendBundle:Project:edit.html.twig', array(
            'project' => $project,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'menu' => 'menu_projects'
        ));
    }

    /**
     * Deletes a Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function deleteAction(Request $request, Entity\Project $project)
    {
        $form = $this->createDeleteForm($project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();
        }
        
        $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.project.delete_success_message'));
        return $this->redirectToRoute('backend_projects');
    }

    /**
     * Creates a form to delete a Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     * @param Entity\Project $project The Project entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Entity\Project $project)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('backend_projects_delete', array('id' => $project->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
