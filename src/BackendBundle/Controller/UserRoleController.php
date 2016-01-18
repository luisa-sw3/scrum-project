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
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $order = array('name' => 'ASC');

        $roles = $em->getRepository('BackendBundle:Role')->findBy(array(), $order);

        return $this->render('BackendBundle:Settings/Roles:index.html.twig', array(
                    'roles' => $roles,
                    'menu' => 'menu_settings'
        ));
    }

    /**
     * Permite desplegar el formulario para crear un nuevo rol, ademas de 
     * validar y almacenar un nuevo rol en el sistema
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     * @param Request $request datos de la solicitud
     * @return type
     */
    public function newAction(Request $request) {
        $role = new Entity\Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $role->setIsManuallyAdded(true);
            $em->persist($role);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user_role.creation_success_message'));
            return $this->redirectToRoute('backend_settings_user_roles');
        }

        return $this->render('BackendBundle:Settings/Roles:new.html.twig', array(
                    'role' => $role,
                    'form' => $form->createView(),
                    'menu' => 'menu_settings'
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
    public function editAction(Request $request, Entity\Role $role) {
        $editForm = $this->createForm(RoleType::class, $role);
        $editForm->handleRequest($request);

        if (!$role->getIsManuallyAdded()) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.user_role.not_found_message'));
            return $this->redirectToRoute('backend_settings_user_roles');
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user_role.update_success_message'));
            return $this->redirectToRoute('backend_settings_user_roles');
        }

        return $this->render('BackendBundle:Settings/Roles:edit.html.twig', array(
                    'role' => $role,
                    'edit_form' => $editForm->createView(),
                    'menu' => 'menu_settings'
        ));
    }

}
