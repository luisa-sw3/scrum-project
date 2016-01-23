<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;

class EditUserRoleType extends AbstractType {

    private $container;
    private $translator;
    
    const FORM_PREFIX = 'backendbundle_user_project_role_type'; 

    public function __construct(Container $container) {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('role', EntityType::class, array(
                    'class' => 'BackendBundle:Role',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->orderBy('r.name', 'ASC');
                    },
                    'label' => $this->translator->trans('backend.user_project.designed_role'),
                    'placeholder' => $this->translator->trans('backend.user_role.select_role'),
                ))
        ;
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
