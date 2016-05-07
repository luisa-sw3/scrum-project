<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ItemSimpleType extends AbstractType {

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

        $typeOptions = $this->container->get('form_helper')->getItemTypeOptions();
        $statusOptions = $this->container->get('form_helper')->getItemStatusOptions();
        $fibonacciOptions = $this->container->get('form_helper')->getItemFibonacciOptions();
        $tShirtOptions = $this->container->get('form_helper')->getItemTShirtOptions();

        $builder
                ->add('title', Type\TextType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.title'),
                    'attr' => array(
                        'maxlength' => 255
                    )
                ))
                ->add('description', Type\TextareaType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.item.description')
                ))
                ->add('type', Type\ChoiceType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.type'),
                    'choices' => $typeOptions,
                ))
                ->add('priority', Type\RangeType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.priority'),
                    'attr' => array(
                        'min' => 0,
                        'max' => 100,
                    )
                ))
                ->add('effortFibonacci', Type\ChoiceType::class, array(
                    'required' => false,
                    'placeholder' => $this->translator->trans('backend.global.select'),
                    'label' => $this->translator->trans('backend.item.complexity'),
                    'choices' => $fibonacciOptions,
                ))
                ->add('effortTShirt', Type\ChoiceType::class, array(
                    'required' => false,
                    'placeholder' => $this->translator->trans('backend.global.select'),
                    'label' => $this->translator->trans('backend.item.complexity'),
                    'choices' => $tShirtOptions,
                ))
                ->add('estimatedHours', Type\NumberType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.item.estimated_hours'),
                    'attr' => array(
                        'maxlength' => 4
                    )
                ))
                ->add('saveAndContinue', SubmitType::class, array(
                    'label' => $this->translator->trans('backend.global.save_and_continue'),
                ))
                ->add('saveAndExit', SubmitType::class, array(
                    'label' => $this->translator->trans('backend.global.save_and_exit')
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Item'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'backendbundle_item_single_type';
    }

}
