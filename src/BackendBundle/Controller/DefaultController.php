<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BackendBundle\Entity as Entity;

class DefaultController extends Controller {

    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

//        $i = 0;
//        while ($i < 10) {
//            $role = new Entity\Role();
//            $role->setName('role 1');
//            $em->persist($role);
//            
//            $item = new Entity\Item();
//            $item->setTitle('item 1');
//            $item->setType(Entity\Item::TYPE_USER_HISTORY);
//            $em->persist($item);
//            $i++;
//        }
//        $em->flush();
//        $user = new Entity\User();
//        $user->setName('Cesar');
//        $user->setLastname('Giraldo');
//        $user->setEmail('cnaranjo@kijho.com');
//
//        $plainPassword = 'aaa';
//        $encoder = $this->container->get('security.password_encoder');
//        $encoded = $encoder->encodePassword($user, $plainPassword);
//
//        $user->setPassword($encoded);
//
//        $em->persist($user);
//        $em->flush();

        $user = $this->getUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER_ACTIVE')) {
        }
        
        return $this->render('BackendBundle:Default:index.html.twig');
    }

}
