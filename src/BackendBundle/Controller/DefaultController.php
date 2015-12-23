<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;

class DefaultController extends Controller {

    
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        
        $i = 0;
        while ($i < 10) {
            $role = new Entity\Role();
            $role->setName('role 1');
            $em->persist($role);
            
            $item = new Entity\Item();
            $item->setName('item 1');
            $em->persist($item);
            $i++;
        }
        $em->flush();

        return $this->render('BackendBundle:Default:index.html.twig');
    }

}
