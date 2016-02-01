<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Form\SprintType;
use BackendBundle\Entity as Entity;

/**
 * Sprint controller.
 */
class SprintController extends Controller {

    const MENU = 'menu_project_sprints';
    
    /**
     * Permite listar los sprints de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @return type
     */
    public function indexAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('BackendBundle:Project')->find($id);

        if (!$project) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $search = array('project' => $project->getId());
        $order = array('creationDate' => 'ASC');

        $sprints = $em->getRepository('BackendBundle:Sprint')->findBy($search, $order);

        return $this->render('BackendBundle:Project/Sprint:index.html.twig', array(
                    'project' => $project,
                    'sprints' => $sprints,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite crear un sprint en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
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

        $sprint = new Entity\Sprint();
        $sprint->setProject($project);
        
        $form = $this->createForm(SprintType::class, $sprint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sprint->setProject($project);
            $sprint->setUserOwner($this->getUser());

            $em->persist($sprint);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.sprint.creation_success_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:Project/Sprint:new.html.twig', array(
                    'project' => $project,
                    'form' => $form->createView(),
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite editar la informacion de un sprint
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $sprintId identificador del sprint
     * @return type
     */
    public function editAction(Request $request, $id, $sprintId) {
        $em = $this->getDoctrine()->getManager();
        $sprint = $em->getRepository('BackendBundle:Sprint')->find($sprintId);

        if (!$sprint || ($sprint && $sprint->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.sprint.not_found_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $id));
        }
        
        $editForm = $this->createForm(SprintType::class, $sprint);
        $editForm->handleRequest($request);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            $em->persist($sprint);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.sprint.update_success_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $sprint->getProject()->getId()));
        }

        return $this->render('BackendBundle:Project/Sprint:edit.html.twig', array(
                    'sprint' => $sprint,
                    'project' => $sprint->getProject(),
                    'edit_form' => $editForm->createView(),
                    'menu' => self::MENU
        ));
    }
    
    /**
     * Esta funcion permite listar el Sprint Backlog de un sprint determinado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/01/2016
     * @param Request $request
     * @param string $id identificador del proyecto
     * @param string $sprintId identificador del sprint
     * @return type
     */
    public function sprintBacklogAction(Request $request, $id, $sprintId) {
        $em = $this->getDoctrine()->getManager();

        $sprint = $em->getRepository('BackendBundle:Sprint')->find($sprintId);

        if (!$sprint || ($sprint && $sprint->getProject()->getId() != $id)) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.sprint.not_found_message'));
            return $this->redirectToRoute('backend_project_sprints', array('id' => $id));
        }

        $search = array('sprint' => $sprint->getId());
        $order = array('priority' => 'DESC');

        $sprintBacklog = $em->getRepository('BackendBundle:Item')->findBy($search, $order);

        return $this->render('BackendBundle:Project/Sprint:backlog.html.twig', array(
                    'project' => $sprint->getProject(),
                    'sprintBacklog' => $sprintBacklog,
                    'menu' => self::MENU
        ));
    }
    

}