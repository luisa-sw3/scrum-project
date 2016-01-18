<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Form\SettingType;
use BackendBundle\Entity\Settings;

class SettingsController extends Controller {

    
    /**
     * Permite obtener la pantalla de inicio del menu de configuraciones
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     * @return type
     */
    public function indexAction() {

        return $this->render('BackendBundle:Settings:index.html.twig', array(
            'menu' => 'menu_settings'
        ));
    }
    
    /**
     * Permite desplegar el formulario para la edicion de las configuraciones
     * globales de la aplicacion
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     * @return type
     */
    public function editAction() {

        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('BackendBundle:Settings')->findOneBy(array(), array());
        
        if(!$settings){
            $settings = new Settings();
        }
        
        $form = $this->createForm(SettingType::class, $settings);

        return $this->render('BackendBundle:Settings:edit.html.twig', array(
                    'settings' => $settings,
                    'form' => $form->createView(),
                    'menu' => 'menu_settings'
        ));
    }

    /**
     * Permite recibir, validar y almacenar la edicion de las configuraciones
     * globales de la aplicacion
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     * @param Request $request datos de la solicitud
     * @return type
     */
    public function updateAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('BackendBundle:Settings')->findOneBy(array(), array());
        
        if(!$settings){
            $settings = new Settings();
        }
        
        $form = $this->createForm(SettingType::class, $settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($settings);
            $em->flush();

            $this->get('session')->getFlashBag()->add('messageSuccess', $this->get('translator')->trans('backend.setting.settings_updated'));
            return $this->redirectToRoute('backend_settings');
        }

        return $this->render('BackendBundle:Settings:edit.html.twig', array(
                    'settings' => $settings,
                    'form' => $form->createView(),
                    'menu' => 'menu_settings'
        ));
    }

}
