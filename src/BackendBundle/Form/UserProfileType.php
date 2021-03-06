<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;

class UserProfileType extends AbstractType {

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
        $builder
                ->add('name', Type\TextType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.user.name')
                ))
                ->add('lastname', Type\TextType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.user.lastname')
                ))
                ->add('cellphone', Type\NumberType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.user.cellphone')
                ))
                ->add('biography', Type\TextareaType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.user.biography')
                ))
                ->add('password', Type\RepeatedType::class, array(
                    'first_options' => array('label' => $this->translator->trans('backend.user.password'), 'attr' => array('minlength' => 3, 'maxlength' => 15)),
                    'second_options' => array('label' => $this->translator->trans('backend.user.confirm_password'), 'attr' => array('minlength' => 3, 'maxlength' => 15)),
                    'required' => false,
                    'type' => Type\PasswordType::class,
                    'invalid_message' => $this->translator->trans('backend.user.password_not_match'),
                    'options' => array('label' => 'Password.'),
                ))
                ->add('profileImage', Type\FileType::class, array(
                    'label' => $this->translator->trans('backend.user.profile_image'),
                    'required' => false))
                ->add('submit', Type\SubmitType::class, array(
                    'label' => $this->translator->trans('backend.global.save_changes'),
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'backendbundle_userprofile_type';
    }

}
