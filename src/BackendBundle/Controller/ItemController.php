<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Form\ProjectType;
use BackendBundle\Form\SettingType;
use BackendBundle\Form\ItemType;
use BackendBundle\Entity as Entity;

/**
 * Item controller.
 */
class ItemController extends Controller {

    /**
     * Permite listar el backlog de un royecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 25/01/2016
     */
    public function productBacklogAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $search = array('project' => $project->getId());
        $order = array('priority' => 'DESC');

        $backlog = $em->getRepository('BackendBundle:Item')->findBy($search, $order);

        return $this->render('BackendBundle:Project/ProductBacklog:index.html.twig', array(
                    'project' => $project,
                    'backlog' => $backlog,
                    'menu' => 'menu_projects'
        ));
    }

    /**
     * Creates a new Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function newAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $item = new Entity\Item();

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $item->setProject($project);
            $item->setUserOwner($this->getUser());

            $em->persist($item);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.creation_success_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project/ProductBacklog:new.html.twig', array(
                    'project' => $project,
                    'form' => $form->createView(),
                    'menu' => 'menu_projects'
        ));
    }

    /**
     * Finds and displays a Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function showAction(Entity\Project $project) {
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
    public function editAction(Request $request, Entity\Project $project) {
        $deleteForm = $this->createDeleteForm($project);
        $editForm = $this->createForm(ProjectType::class, $project);
        $editForm->handleRequest($request);

        $settingsForm = $this->createForm(SettingType::class, $project->getSettings());
        $settingsForm->handleRequest($request);

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
                    'settings_form' => $settingsForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'menu' => 'menu_projects'
        ));
    }

    /**
     * Deletes a Project entity.
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     */
    public function deleteAction(Request $request, Entity\Project $project) {
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
    private function createDeleteForm(Entity\Project $project) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('backend_projects_delete', array('id' => $project->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

}
