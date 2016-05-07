<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use BackendBundle\Entity\Item;
use BackendBundle\Entity\Sprint;

class MoveItemToSprintType extends AbstractType {

    private $container;
    private $translator;

    const MOVE_TO_SPRINT = 'move';
    const COPY_TO_SPRINT = 'copy';
    const ACTION_METHOD_CASCADE = 'cascade';
    const ACTION_METHOD_SIMPLE = 'simple';

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
            if ($data instanceof Item) {
                $project = $data->getProject();
                $sprintId = ($data->getSprint() ? $data->getSprint()->getId() : 0);
                $form
                        ->add('new_sprint', EntityType::class, array(
                            'class' => 'BackendBundle:Sprint',
                            'mapped' => false,
                            'query_builder' => function (EntityRepository $er) use ($project, $sprintId) {
                                return $er->createQueryBuilder('s')
                                        ->where(($project != null ? "s.project = '" . $project->getId() . "' "
                                        . "AND s.id <> '".$sprintId."' "
                                        . "AND (s.status = ".Sprint::STATUS_PLANNED." OR s.status = ".Sprint::STATUS_IN_PROCESS.")" : "1=1"))
                                        ->orderBy('s.name', 'ASC');
                            },
                            'required' => true,
                            'label' => $this->translator->trans('backend.item.select_sprint'),
                            'placeholder' => $this->translator->trans('backend.global.select'),
                ));
            }
        });

        $builder
                ->add('action', Type\ChoiceType::class, array(
                    'required' => true,
                    'mapped' => false,
                    'multiple' => false,
                    'expanded' => true,
                    'label' => $this->translator->trans('backend.global.select_action'),
                    'choices' => array(
                        'Move' => self::MOVE_TO_SPRINT,
                        'Copy' => self::COPY_TO_SPRINT
                    ),
                ))
                ->add('method', Type\ChoiceType::class, array(
                    'required' => true,
                    'mapped' => false,
                    'multiple' => false,
                    'expanded' => true,
                    'label' => $this->translator->trans('backend.global.select_method'),
                    'choices' => array(
                        'Cascade' => self::ACTION_METHOD_CASCADE,
                        'Simple' => self::ACTION_METHOD_SIMPLE),
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
        return 'backendbundle_item_move_to_sprint_type';
    }

}
