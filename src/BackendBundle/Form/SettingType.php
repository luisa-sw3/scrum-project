<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;
use BackendBundle\Entity\Settings;

class SettingType extends AbstractType {

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
                ->add('dateFormat', Type\ChoiceType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.setting.date_format'),
                    'choices' => array(Settings::DATE_FORMAT_1 => Settings::DATE_FORMAT_1,
                        Settings::DATE_FORMAT_2 => Settings::DATE_FORMAT_2,
                        Settings::DATE_FORMAT_3 => Settings::DATE_FORMAT_3)
                ))
                ->add('hourFormat', Type\ChoiceType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.setting.hour_format'),
                    'choices' => array(Settings::HOUR_FORMAT_1 => Settings::HOUR_FORMAT_1,
                        Settings::HOUR_FORMAT_2 => Settings::HOUR_FORMAT_2,
                        Settings::HOUR_FORMAT_3 => Settings::HOUR_FORMAT_3,
                        Settings::HOUR_FORMAT_4 => Settings::HOUR_FORMAT_4)
                ))
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
            'data_class' => 'BackendBundle\Entity\Settings'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'backendbundle_settings_type';
    }

}
