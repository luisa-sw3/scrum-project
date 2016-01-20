<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Form\UserProfileType;

class UserController extends Controller {

    /**
     * Permite desplegar el formulario de edicion del perfil del usuario logueado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 26/12/2015
     * @return type
     */
    public function editProfileAction() {

        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);

        return $this->render('BackendBundle:User:editProfile.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Permite validar y almacenar los cambios en el perfil del usuario logueado
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 26/12/2015
     * @param Request $request
     * @return type
     */
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

    /**
     * Esta funcion permite realizar una busqueda de usuarios que se pueden invitar a un
     * proyecto determinado mediante un autocompletar
     * @param Request $request datos de la solicitud
     * @return JsonResponse
     */
    public function searchUsersAutocompleteAction(Request $request) {

        //palabra buscada
        $originalTerm = $request->query->get('term');
        $term = mb_convert_case('%' . $originalTerm . '%', MB_CASE_TITLE, "UTF-8");

        $projectId = null;

        if (!empty($request->get('projectId'))) {
            $projectId = $request->get('projectId');
        }

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('BackendBundle:User')->findUsersAutocomplete($term, $projectId);

        if (empty($users)) {

            if (!$projectId) {
                $noMemberMessage = "* " . $originalTerm . $this->get('translator')->trans('backend.user_project.not_a_member');
            } else {
                $noMemberMessage = "* " . $originalTerm . $this->get('translator')->trans('backend.user_project.not_a_project_member');
            }
            $emptyItem['id'] = 0;
            $emptyItem['label'] = $noMemberMessage;
            $emptyItem['value'] = $noMemberMessage;

            $users[0] = $emptyItem;
        }

        $response = new JsonResponse($users);
        return $response;
    }

}
