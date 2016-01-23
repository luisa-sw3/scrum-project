<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;
use BackendBundle\Form\RoleType;

/**
 * User Role Controller
 */
class UserRoleController extends Controller {

    /**
     * Permite listar los roles que tiene la aplicacion
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     * @return type
     */
    public function indexAction($projectId) {
        $em = $this->getDoctrine()->getManager();

        $search = array('project' => $projectId);
        $order = array('name' => 'ASC');

        $project = $em->getRepository('BackendBundle:Project')->find($projectId);
        
        $roles = $em->getRepository('BackendBundle:Role')->findBy($search, $order);

        return $this->render('BackendBundle:Settings/Roles:index.html.twig', array(
                    'roles' => $roles,
                    'project' => $project,
                    'menu' => 'menu_projects'
        ));
    }

    /**
     * Permite desplegar el formulario para crear un nuevo rol, ademas de 
     * validar y almacenar un nuevo rol en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     * @param Request $request datos de la solicitud
     * @return type
     */
    public function newAction(Request $request, $projectId) {
        $role = new Entity\Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('BackendBundle:Project')->find($projectId);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $role->setProject($project);
            $role->setIsManuallyAdded(true);
            $em->persist($role);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user_role.creation_success_message'));
            return $this->redirectToRoute('backend_projects_roles', array('projectId' => $projectId));
        }

        return $this->render('BackendBundle:Settings/Roles:new.html.twig', array(
                    'role' => $role,
                    'project' => $project,
                    'form' => $form->createView(),
                    'menu' => 'menu_projects'
        ));
    }

    /**
     * Permite desplegar el formulario de edicion de un rol, ademas de 
     * validar y guardar los cambios del rol en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     * @param Request $request datos de la solicitud
     * @param \BackendBundle\Entity\Role $role rol a editar
     * @return type
     */
    public function editAction(Request $request, $projectId, Entity\Role $role) {
        $editForm = $this->createForm(RoleType::class, $role);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('BackendBundle:Project')->find($projectId);
        
        if (!$role->getIsManuallyAdded()) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.user_role.not_found_message'));
            return $this->redirectToRoute('backend_projects_roles', array('projectId' => $projectId));
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->persist($role);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user_role.update_success_message'));
            return $this->redirectToRoute('backend_projects_roles', array('projectId' => $projectId));
        }

        return $this->render('BackendBundle:Settings/Roles:edit.html.twig', array(
                    'role' => $role,
                    'project' => $project,
                    'edit_form' => $editForm->createView(),
                    'menu' => 'menu_projects'
        ));
    }

}
