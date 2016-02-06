<?php

namespace BackendBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BackendBundle\Entity as Entity;

/**
 * Description of LoadUserData
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 24/12/2015
 */
class LoadUserData implements FixtureInterface, ContainerAwareInterface {

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function load(ObjectManager $manager) {

        //creamos el primer usuario del sistema
        $user = new Entity\User();
        $user->setName('Cesar');
        $user->setLastname('Giraldo');
        $user->setEmail('cnaranjo@kijho.com');
        $user->setStatus(Entity\User::STATUS_ACTIVE);

        $plainPassword = 'aaa';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);

        $manager->persist($user);
        
        $manager->flush();
    }

}
