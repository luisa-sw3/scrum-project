<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BackendBundle\Entity\Project;
use BackendBundle\Form\ProjectType;

/**
 * Project controller.
 */
class ProjectController extends Controller
{
    /**
     * Lists all Project entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $search = array('userOwner' => $this->getUser()->getId());
        $projects = $em->getRepository('BackendBundle:Project')->findBy($search);

        return $this->render('BackendBundle:Project:index.html.twig', array(
            'projects' => $projects,
        ));
    }

    /**
     * Creates a new Project entity.
     *
     */
    public function newAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('backend_projects_show', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project:new.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Project entity.
     *
     */
    public function showAction(Project $project)
    {
        $deleteForm = $this->createDeleteForm($project);

        return $this->render('BackendBundle:Project:show.html.twig', array(
            'project' => $project,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     */
    public function editAction(Request $request, Project $project)
    {
        $deleteForm = $this->createDeleteForm($project);
        $editForm = $this->createForm(ProjectType::class, $project);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('backend_projects_edit', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project:edit.html.twig', array(
            'project' => $project,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Project entity.
     *
     */
    public function deleteAction(Request $request, Project $project)
    {
        $form = $this->createDeleteForm($project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();
        }

        return $this->redirectToRoute('backend_projects');
    }

    /**
     * Creates a form to delete a Project entity.
     *
     * @param Project $project The Project entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Project $project)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('backend_projects_delete', array('id' => $project->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
