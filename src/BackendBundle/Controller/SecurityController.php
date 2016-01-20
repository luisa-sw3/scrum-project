<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Entity as Entity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityController extends Controller {

    /**
     * Metodo encargado de validar la autenticacion de usuarios en el backend
     * @param Request $request
     * @return type
     */
    public function loginAction(Request $request) {
        $authenticationUtils = $this->get('security.authentication_utils');


        $userId = $request->get('confirmAccount');
        if ($userId != '') {
            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository('BackendBundle:User')->find(base64_decode($userId));

            if ($user instanceof Entity\User && !$user->getIsAccountConfirmed()) {
                //confirmamos su cuenta
                $user->setIsAccountConfirmed(true);
                $em->persist($user);
                $em->flush();
                
                //realizamos el logueo del usuario
                $token = new UsernamePasswordToken(
                        $user, $user->getPassword(), "backend", $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);

                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                
                
                $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user.confirmed_account_message'));
                
                return $this->redirect($this->generateUrl('backend_homepage'));
            }
            return $this->redirect($this->generateUrl('backend_login'));
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
                        'BackendBundle:Security:login.html.twig', array(
                    'last_username' => $lastUsername,
                    'error' => $error,
                        )
        );
    }

    public function loginCheckAction() {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

}
