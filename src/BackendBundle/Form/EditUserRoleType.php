<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use BackendBundle\Entity\UserProject;

class EditUserRoleType extends AbstractType {

    private $container;
    private $translator;
    protected $projectId;

    const FORM_PREFIX = 'backendbundle_user_project_role_type';

    public function __construct(Container $container) {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->projectId = 'd77b362c-c0a2-11e5-a099-0f99d2511fa6';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof UserProject) {

                $project = $data->getProject();

                $form->add('role', EntityType::class, array(
                    'class' => 'BackendBundle:Role',
                    'query_builder' => function (EntityRepository $er) use ($project) {
                        return $er->createQueryBuilder('r')
                                        ->where(($project != null ? "r.project = '" . $project->getId() . "'" : '1=1'))
                                        ->orderBy('r.name', 'ASC');
                    },
                    'label' => $this->translator->trans('backend.user_project.designed_role'),
                    'placeholder' => $this->translator->trans('backend.user_role.select_role'),
                ));
            }
        });

        //$builder->add();
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\UserProject'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return self::FORM_PREFIX;
    }

}
