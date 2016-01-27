<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Form\ItemType;
use BackendBundle\Entity as Entity;

/**
 * Item controller.
 */
class ItemController extends Controller {

    /**
     * Permite listar el backlog de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 25/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
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
                    'menu' => 'menu_project_backlog'
        ));
    }

    /**
     * Permite crear un item en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 21/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function newAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $item = new Entity\Item();
        $item->setProject($project);
        
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
                    'menu' => 'menu_project_backlog'
        ));
    }

    /**
     * Permite editar la informacion de un item
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 27/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $itemId identificador del itemm
     * @return type
     */
    public function editAction(Request $request, $id, $itemId) {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('BackendBundle:Item')->find($itemId);

        if (!$item || ($item && $item->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.item.not_found_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $id));
        }
        
        $editForm = $this->createForm(ItemType::class, $item);
        $editForm->handleRequest($request);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            $em->persist($item);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.item.update_success_message'));
            return $this->redirectToRoute('backend_project_product_backlog', array('id' => $item->getProject()->getId()));
        }

        return $this->render('BackendBundle:Project/ProductBacklog:edit.html.twig', array(
                    'item' => $item,
                    'project' => $item->getProject(),
                    'edit_form' => $editForm->createView(),
                    'menu' => 'menu_project_backlog'
        ));
    }
    

}
