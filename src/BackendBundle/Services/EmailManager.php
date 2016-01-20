<?php

namespace BackendBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use BackendBundle\Entity as Entity;
use Doctrine\ORM\EntityManager;

/*
 * EmailManager
 * Esta clase implementa metodos generalizados para la construccion y 
 * envio de correos electronicos en la aplicacion, los cuales pueden ser utilizados
 * como un servicio
 */
class EmailManager {

    protected $mailer;
    protected $request;
    protected $container;
    protected $translator;
    protected $em;
    
    const SENDER_GENERAL_EMAILS = 'myagilescrum@gmail.com';
    
    /**
     * Constructor del servicio encargado de enviar todos los correos de la aplicacion
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param RequestStack $requestStack
     * @param ContainerInterface $container
     * @param EntityManager $entityManager
     */
    public function __construct(RequestStack $requestStack, ContainerInterface $container, EntityManager $entityManager) {
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->mailer = $this->container->get('mailer');
        $this->em = $entityManager;
    }

    /**
     * Permite enviar el correo de bienvenida cuando el usuario se registra
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Entity\User $user
     */
    public function sendWelcomeEmail(Entity\User $user) {

        $message = \Swift_Message::newInstance()
                ->setSubject($this->translator->trans('backend.welcome_email.subject'))
                ->setFrom(self::SENDER_GENERAL_EMAILS)
                ->setTo($user->getEmail())
                ->setBody(
                $this->container->get('templating')->render(
                        'FrontendBundle:Email:welcomeEmail.html.twig', array('user' => $user,
                    'userId' => base64_encode($user->getId()))
                ), 'text/html'
                )
        ;
        $this->mailer->send($message);
    }
    
    /**
     * Permite enviar a un usuario el correo de invitacion a un proyecto
     * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 20/01/2016
     * @param Entity\ProjectInvitation $projectInvitation
     */
    public function sendProjectInvitationEmail(Entity\ProjectInvitation $projectInvitation) {

        $message = \Swift_Message::newInstance()
                ->setSubject($this->translator->trans('backend.project_invitation_email.subject'))
                ->setFrom(self::SENDER_GENERAL_EMAILS)
                ->setTo($projectInvitation->getuser()->getEmail())
                ->setBody(
                $this->container->get('templating')->render(
                        'BackendBundle:Email:projectInvitation.html.twig', array('projectInvitation' => $projectInvitation)
                ), 'text/html'
                )
        ;
        $this->mailer->send($message);
    }

}
