<?php

namespace BackendBundle\Form\TimeTracking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;

class SearchTimeTrackType extends AbstractType {

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
                ->add('startDate', Type\TextType::class, array(
                    'required' => true,
                    'label' => ucwords($this->translator->trans('backend.global.from'))
                ))
                ->add('endDate', Type\TextType::class, array(
                    'required' => true,
                    'label' => ucwords($this->translator->trans('backend.global.until')),
                ))
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'backendbundle_search_time_track_type';
    }

}
