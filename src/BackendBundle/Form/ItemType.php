<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use BackendBundle\Entity\Item;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ItemType extends AbstractType {

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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof Item) {
                $project = $data->getProject();

                $form
                        ->add('designedUser', EntityType::class, array(
                            'class' => 'BackendBundle:User',
                            'query_builder' => function (EntityRepository $er) use ($project) {
                                return $er->createQueryBuilder('u')
                                        ->join('BackendBundle:UserProject', 'uspr')
                                        ->where('uspr.user = u.id')
                                        ->andWhere(($project != null ? "uspr.project = '" . $project->getId() . "'" : '1=1'))
                                        ->orderBy('u.name', 'ASC');
                            },
                            'required' => false,
                            'label' => $this->translator->trans('backend.item.designed_user'),
                            'placeholder' => $this->translator->trans('backend.global.select'),
                        ))
                        ->add('sprint', EntityType::class, array(
                            'class' => 'BackendBundle:Sprint',
                            'query_builder' => function (EntityRepository $er) use ($project) {
                                return $er->createQueryBuilder('s')
                                        ->where(($project != null ? "s.project = '" . $project->getId() . "'" : '1=1'))
                                        ->orderBy('s.name', 'ASC');
                            },
                            'required' => false,
                            'label' => $this->translator->trans('backend.item.sprint'),
                            'placeholder' => $this->translator->trans('backend.global.select'),
                ));
            }
        });

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
                ->add('status', Type\ChoiceType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.status'),
                    'choices' => $statusOptions,
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
                    'label' => $this->translator->trans('backend.item.effort_estimation'),
                    'choices' => $fibonacciOptions,
                ))
                ->add('effortTShirt', Type\ChoiceType::class, array(
                    'required' => false,
                    'placeholder' => $this->translator->trans('backend.global.select'),
                    'label' => $this->translator->trans('backend.item.effort_estimation'),
                    'choices' => $tShirtOptions,
                ))
                ->add('estimatedHours', Type\NumberType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.item.estimated_hours'),
                    'attr' => array(
                        'maxlength' => 4
                    )
                ))
                ->add('workedHours', Type\NumberType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.item.worked_hours'),
                    'attr' => array(
                        'maxlength' => 4
                    )
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
        return 'backendbundle_item_type';
    }

}
