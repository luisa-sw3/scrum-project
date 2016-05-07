<?php

namespace FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Entity as Entity;
use FrontendBundle\Form\UserType;

class DefaultController extends Controller {

    public function indexAction() {
        return $this->render('FrontendBundle:Default:index.html.twig');
    }

    /**
     * Esta funcion permite redireccionar a la aplicacion cuando no introducen 
     * la variable _locale en la url para identificar el idioma
     * @param Request $request
     * @return type
     */
    public function redirectAction(Request $request) {
        $locale = $this->container->getParameter('locale');
        $url = $this->generateUrl('frontend_homepage', array('_locale' => $locale));
        return $this->redirect($url);
    }
    
    public function featuresAction() {
        return $this->render('FrontendBundle:Default:features.html.twig');
    }

    public function whyAgileScrumAction() {
        return $this->render('FrontendBundle:Default:whyAgileScrum.html.twig');
    }

    /**
     * Esta funcion permite el registro de usuarios desde la parte publica
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 19/01/2016
     * @param Request $request datos de la solicitud
     * @return type
     */
    public function createAccountAction(Request $request) {

        $user = new Entity\User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $errorMessage = "";

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();


            //verificamos que no exista un usuario con el email ingresado
            $existUser = $em->getRepository('BackendBundle:User')->findOneByEmail($user->getEmail());
            if (!$existUser) {

                // verificamos si cambiaron o no la contraseña
                $plainPassword = $user->getPassword();

                //ponemos en minusculas el correo del usuario
                $user->setEmail(strtolower($user->getEmail()));

                //codificamos la contraseña del usuario
                $encoder = $this->container->get('security.password_encoder');
                $password = $encoder->encodePassword($user, $plainPassword);
                $user->setPassword($password);

                $user->setIsAccountConfirmed(false);
                $user->setStatus(Entity\User::STATUS_ACTIVE);

                $em->persist($user);
                $em->flush();

                $message = $this->get('translator')->trans('backend.user.message_account_created_1');
                $message .= '<strong>'.$user->getEmail().'</strong>';
                $message .= $this->get('translator')->trans('backend.user.message_account_created_2');
                $this->get('session')->getFlashBag()->add('messageFrontendSuccess', $message);

                $this->get('email_manager')->sendWelcomeEmail($user);

                return $this->redirectToRoute('frontend_homepage');
            } else {
                $this->get('session')->getFlashBag()->add('messageFrontendError', $this->get('translator')->trans('backend.user.message_email_exist'));
            }
        }

        return $this->render('FrontendBundle:Default:createAccount.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}
