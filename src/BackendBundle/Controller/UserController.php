<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Form\UserProfileType;

class UserController extends Controller {

    public function editProfileAction() {

        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);

        return $this->render('BackendBundle:User:editProfile.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
        ));
    }

    public function updateProfileAction(Request $request) {

        $user = $this->getUser();

        $form = $this->createForm(UserProfileType::class, $user);

        $previousPassword = $user->getPassword();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            // verificamos si cambiaron o no la contraseña
            $plainPassword = $user->getPassword();

            if (!$plainPassword) {
                $user->setPassword($previousPassword);
            } else {
                //codificamos la contraseña del usuario
                $encoder = $this->container->get('security.password_encoder');
                $password = $encoder->encodePassword($user, $plainPassword);
                $user->setPassword($password);
            }

            //verificamos si se sube la imagen de perfil
            $file = $user->getProfileImage();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $fileDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/users';
                $file->move($fileDir, $fileName);
                $user->setProfileImagePath($fileName);
            }

            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.user.profile_updated'));
            return $this->redirectToRoute('backend_homepage');
        }

        return $this->render('BackendBundle:User:editProfile.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
        ));
    }

}
