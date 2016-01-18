<?php

namespace FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    public function indexAction() {
        return $this->render('FrontendBundle:Default:index.html.twig');
    }
    
    public function aboutAction() {
        return $this->render('FrontendBundle:Default:about.html.twig');
    }
    
    public function samplePostAction() {
        return $this->render('FrontendBundle:Default:samplePost.html.twig');
    }
    
    public function contactAction() {
        return $this->render('FrontendBundle:Default:contact.html.twig');
    }

}
