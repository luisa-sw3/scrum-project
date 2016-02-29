<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;
use BackendBundle\Form\ProjectInvitationType;
use BackendBundle\Form\EditUserRoleType;
use Util\Util;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Project Team controller.
 */
class ProjectTeamController extends Controller {

    const MENU = 'menu_project_team';

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
        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        //buscamos los usuarios que ya estan asignados al proyecto
        $users = $em->getRepository('BackendBundle:UserProject')->findBy($search, $order);

        $forms = array();
        $i = 0;
        foreach ($users as $user) {

            $form = $this->container
                    ->get('form.factory')
                    ->createNamedBuilder(EditUserRoleType::FORM_PREFIX . $i, EditUserRoleType::class, $user)
                    ->getForm()
                    ->createView();
            array_push($forms, $form);
            $i++;
        }

        //buscamos las invitaciones que aun no han confirmado
        $search = array('project' => $project->getId(), 'status' => Entity\ProjectInvitation::STATUS_ACTIVE);
        $order = array('date' => 'DESC');
        $invitations = $em->getRepository('BackendBundle:ProjectInvitation')->findBy($search, $order);

        return $this->render('BackendBundle:ProjectTeam:index.html.twig', array(
                    'users' => $users,
                    'forms' => $forms,
                    'invitations' => $invitations,
                    'project' => $project,
                    'menu' => self::MENU
        ));
    }

    /**
     * Permite registrar invitaciones de usuarios a proyectos
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 18/01/2016
     */
    public function addAction(Request $request, $id) {
        $closeFancy = false;

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('BackendBundle:Project')->find($id);
        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            $closeFancy = true;
        }

        $projectInvitation = new Entity\ProjectInvitation();
        $projectInvitation->setProject($project);
        $form = $this->createForm(ProjectInvitationType::class, $projectInvitation);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            $parameters = $request->request->get('project_invitation');

            $user = $em->getRepository('BackendBundle:User')->find($parameters['userId']);

            if ($user) {

                //verificamos que el usuario no este asociado al proyecto actual
                $search = array('user' => $user->getId(), 'project' => $project->getId());
                $existsRelationship = $em->getRepository('BackendBundle:UserProject')->findOneBy($search);
                if (!$existsRelationship) {

                    //verificamos si este usuario tenia invitaciones pendientes y las cancelamos
                    $this->cancelPendingInvitations($user, $project);

                    $projectInvitation->setUser($user);
                    $projectInvitation->setProject($project);
                    $projectInvitation->setUserOwner($this->getUser());
                    $em->persist($projectInvitation);
                    $em->flush();

                    //enviamos el correo de notificacion
                    $this->get('email_manager')->sendProjectInvitationEmail($projectInvitation);

                    $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user_project.message_invitation_send'));
                    $closeFancy = true;
                } else {
                    $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.user_project.already_assigned_message'));
                }
            } else {
                $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.user.not_found_message'));
            }
        }

        return $this->render('BackendBundle:ProjectTeam:addCollaborator.html.twig', array(
                    'project' => $project,
                    'form' => $form->createView(),
                    'menu' => self::MENU,
                    'closeFancy' => $closeFancy
        ));
    }

    /**
     * Esta funcion permite cancelar invitaciones pendientes de un usuario
     * para que no queden varias invitaciones pendientes repetidas
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Entity\User $user
     * @param Entity\Project $project
     */
    private function cancelPendingInvitations($user, $project) {
        $em = $this->getDoctrine()->getManager();

        $search = array('user' => $user->getId(), 'project' => $project->getId());

        $invitations = $em->getRepository('BackendBundle:ProjectInvitation')->findBy($search);

        foreach ($invitations as $invitation) {
            $invitation->setStatus(Entity\ProjectInvitation::STATUS_CANCELED);
            $invitation->setCanceledDate(Util::getCurrentDate());
            $em->persist($invitation);
        }
        $em->flush();
    }

    /**
     * Esta funcion permite eliminar una invitacion de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteInvitationAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $invitationId = $request->request->get('invitationId');

        $response['result'] = "__OK__";
        $response['msg'] = "";

        $invitation = $em->getRepository('BackendBundle:ProjectInvitation')->find($invitationId);
        if (!$invitation) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.project_invitation.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $invitation->setStatus(Entity\ProjectInvitation::STATUS_CANCELED);
            $invitation->setCanceledDate(Util::getCurrentDate());
            $em->persist($invitation);
            $em->flush();
        } catch (\Exception $exc) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.project_invitation.error_canceling');
        }

        return new JsonResponse($response);
    }

    /**
     * Esta funcion permite eliminar una usuario de un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUserProjectAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $userProjectId = $request->request->get('userProjectId');

        $response['result'] = "__OK__";
        $response['msg'] = "";

        $userProject = $em->getRepository('BackendBundle:UserProject')->find($userProjectId);
        if (!$userProject || ($userProject && $userProject->getProject()->getId() != $id)) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.user.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $em->remove($userProject);
            $em->flush();
        } catch (\Exception $exc) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.user_project.error_cancel_assign');
        }

        return new JsonResponse($response);
    }

    /**
     * Esta funcion permite editar el que el usuario tiene asignado en un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/01/2016
     * @param Request $request datos de la solicitud
     * @param string $id identificador del proyecto
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function editUserRoleAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $userProjectId = $request->request->get('userProjectId');
        $roleId = $request->request->get('roleId');

        $response['result'] = "__OK__";
        $response['msg'] = "";

        $userProject = $em->getRepository('BackendBundle:UserProject')->find($userProjectId);
        if (!$userProject || ($userProject && $userProject->getProject()->getId() != $id)) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.user.not_found_message');
            return new JsonResponse($response);
        }

        if (!$this->container->get('access_control')->isAllowedProject($id)) {
            $response['result'] = '__KO__';
            $response['msg'] = $this->get('translator')->trans('backend.project.not_found_message');
            return new JsonResponse($response);
        }

        $role = $em->getRepository('BackendBundle:Role')->find($roleId);
        if (!$role) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.user_role.not_found_message');
            return new JsonResponse($response);
        }

        try {
            $userProject->setRole($role);
            $em->persist($userProject);
            $em->flush();
        } catch (\Exception $exc) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.user_project.error_edit_role');
        }

        return new JsonResponse($response);
    }

    /**
     * Permite desplegar la ficha descriptiva de un usuario asociado a un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 28/02/2016
     * @param string $id identificador del proyecto
     * @param string $userId identificador del usuario
     * @return type
     */
    public function viewAction($id, $userId) {

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('BackendBundle:Project')->find($id);
        if (!$project || ($project && !$this->container->get('access_control')->isAllowedProject($project->getId()))) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project.not_found_message'));
            return $this->redirectToRoute('backend_projects');
        }

        $userProject = $em->getRepository('BackendBundle:UserProject')->find($userId);
        if (!$userProject || ($userProject && $userProject->getProject()->getId() != $project->getId())) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.user.not_found_message'));
            return $this->redirectToRoute('backend_project_team', array('id' => $project->getId()));
        }

        return $this->render('BackendBundle:ProjectTeam:view.html.twig', array(
                    'project' => $project,
                    'userProject' => $userProject,
                    'menu' => self::MENU,
        ));
    }

}
