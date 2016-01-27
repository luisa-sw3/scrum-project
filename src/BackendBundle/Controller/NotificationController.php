<?php

namespace BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;
use Util\Util;

/**
 * Notification controller.
 */
class NotificationController extends Controller {

    /**
     * Permite listar las notificaciones pendientes del usuario logueado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 22/01/2016
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $rejectInvitation = 0;
        if (!empty($request->get('rejectInvitation'))) {
            $rejectInvitation = $request->get('rejectInvitation');
        }

        $search = array('user' => $this->getUser()->getId(), 'status' => Entity\ProjectInvitation::STATUS_ACTIVE);
        $order = array('date' => 'ASC');
        $invitations = $em->getRepository('BackendBundle:ProjectInvitation')->findBy($search, $order);

        return $this->render('BackendBundle:Notification:index.html.twig', array(
                    'invitations' => $invitations,
                    'rejectInvitation' => $rejectInvitation,
                    'menu' => 'menu_home'
        ));
    }

    /**
     * Permite a un usuario aceptar invitaciones a proyectos
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 22/01/2016
     * @param string $id identificador de la invitacion
     * @return type
     */
    public function acceptAction($id) {

        $em = $this->getDoctrine()->getManager();
        $invitation = $em->getRepository('BackendBundle:ProjectInvitation')->find($id);

        if (!$invitation ||
                ($invitation && $invitation->getStatus() != Entity\ProjectInvitation::STATUS_ACTIVE ||
                $invitation && $invitation->getUser()->getId() != $this->getUser()->getId())) {
            $this->get('session')->getFlashBag()->add('messageError', $this->get('translator')->trans('backend.project_invitation.invitation_not_found'));
            return $this->redirectToRoute('backend_notifications');
        }

        //asignamos el usuario al proyecto
        $userProject = new Entity\UserProject();
        $userProject->setProject($invitation->getProject());
        $userProject->setRole($invitation->getRole());
        $userProject->setUser($invitation->getUser());
        $em->persist($userProject);

        //marcamos la invitacion como aceptada
        $invitation->setStatus(Entity\ProjectInvitation::STATUS_ACCEPTED);
        $invitation->setAcceptedDate(Util::getCurrentDate());
        $em->persist($invitation);

        $em->flush();

        $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.project_invitation.invitation_accepted_message'));
        return $this->redirectToRoute('backend_projects_view', array('id' => $invitation->getProject()->getId()));
    }

    /**
     * Esta funcion permite rechazar una invitacion a un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 22/01/2016
     * @param Request $request datos de la solicitud
     * @return JsonResponse JSON con mensaje de respuesta
     */
    public function rejectAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $invitationId = $request->request->get('invitationId');

        $response['result'] = "__OK__";
        $response['msg'] = "";

        $invitation = $em->getRepository('BackendBundle:ProjectInvitation')->find($invitationId);
        if (!$invitation ||
                ($invitation && $invitation->getStatus() != Entity\ProjectInvitation::STATUS_ACTIVE ||
                $invitation && $invitation->getUser()->getId() != $this->getUser()->getId())) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.project_invitation.invitation_not_found');
            return new JsonResponse($response);
        }

        try {
            $invitation->setStatus(Entity\ProjectInvitation::STATUS_REJECTED);
            $invitation->setCanceledDate(Util::getCurrentDate());
            $em->persist($invitation);
            $em->flush();
        } catch (\Exception $exc) {
            $response['result'] = "__KO__";
            $response['msg'] = $this->get('translator')->trans('backend.project_invitation.error_rejecting');
        }

        return new JsonResponse($response);
    }

}
