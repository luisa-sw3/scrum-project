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
        $item = new Item();

        $typeOptions = array(
            $this->translator->trans($item->getTextType(Item::TYPE_USER_HISTORY)) => Item::TYPE_USER_HISTORY,
            $this->translator->trans($item->getTextType(Item::TYPE_FEATURE)) => Item::TYPE_FEATURE,
            $this->translator->trans($item->getTextType(Item::TYPE_TASK)) => Item::TYPE_TASK,
            $this->translator->trans($item->getTextType(Item::TYPE_BUG)) => Item::TYPE_BUG,
            $this->translator->trans($item->getTextType(Item::TYPE_IMPROVEMENT)) => Item::TYPE_IMPROVEMENT,
            $this->translator->trans($item->getTextType(Item::TYPE_CHANGE_REQUEST)) => Item::TYPE_CHANGE_REQUEST,
            $this->translator->trans($item->getTextType(Item::TYPE_IDEA)) => Item::TYPE_IDEA,
            $this->translator->trans($item->getTextType(Item::TYPE_INVESTIGATION)) => Item::TYPE_INVESTIGATION,
        );

        $statusOptions = array(
            $this->translator->trans($item->getTextStatus(Item::STATUS_NEW)) => Item::STATUS_NEW,
            $this->translator->trans($item->getTextStatus(Item::STATUS_INVESTIGATING)) => Item::STATUS_INVESTIGATING,
            $this->translator->trans($item->getTextStatus(Item::STATUS_CONFIRMED)) => Item::STATUS_CONFIRMED,
            $this->translator->trans($item->getTextStatus(Item::STATUS_NOT_A_BUG)) => Item::STATUS_NOT_A_BUG,
            $this->translator->trans($item->getTextStatus(Item::STATUS_BEING_WORKED_ON)) => Item::STATUS_BEING_WORKED_ON,
            $this->translator->trans($item->getTextStatus(Item::STATUS_NEAR_COMPLETION)) => Item::STATUS_NEAR_COMPLETION,
            $this->translator->trans($item->getTextStatus(Item::STATUS_READY_FOR_TESTING)) => Item::STATUS_READY_FOR_TESTING,
            $this->translator->trans($item->getTextStatus(Item::STATUS_TESTING)) => Item::STATUS_TESTING,
            $this->translator->trans($item->getTextStatus(Item::STATUS_CANCELED)) => Item::STATUS_CANCELED,
            $this->translator->trans($item->getTextStatus(Item::STATUS_POSTPONED)) => Item::STATUS_POSTPONED,
            $this->translator->trans($item->getTextStatus(Item::STATUS_DONE)) => Item::STATUS_DONE,
            $this->translator->trans($item->getTextStatus(Item::STATUS_FIXED)) => Item::STATUS_FIXED,
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof Item) {
                $project = $data->getProject();

                $form->add('designedUser', EntityType::class, array(
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
