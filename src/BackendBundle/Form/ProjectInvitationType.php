<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use BackendBundle\Entity\ProjectInvitation;

class ProjectInvitationType extends AbstractType {

    private $container;
    private $translator;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof ProjectInvitation) {
                $project = $data->getProject();

                $form->add('role', EntityType::class, array(
                    'class' => 'BackendBundle:Role',
                    'query_builder' => function (EntityRepository $er) use ($project) {
                        return $er->createQueryBuilder('r')
                                        ->where(($project != null ? "r.project = '" . $project->getId() . "'" : '1=1'))
                                        ->orderBy('r.name', 'ASC');
                    },
                    'label' => $this->translator->trans('backend.user_project.invite_as'),
                    'placeholder' => $this->translator->trans('backend.user_role.select_role'),
                ));
            }
        });

        $builder
                ->add('userId', Type\HiddenType::class, array(
                    'required' => false,
                    'mapped' => false,
                ))
                ->add('email', Type\TextType::class, array(
                    'required' => true,
                    'mapped' => false,
                    'label' => $this->translator->trans('backend.user_project.email_name_lastname')
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\ProjectInvitation'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'backendbundle_project_invitation_type';
    }

}
