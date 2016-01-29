<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingsController extends Controller {

    const MENU = 'menu_project_settings';
    
    /**
     * Permite obtener la pantalla de inicio del menu de configuraciones
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 12/01/2016
     * @return type
     */
    public function indexAction() {

        return $this->render('BackendBundle:Settings:index.html.twig', array(
            'menu' => self::MENU
        ));
    }
}
