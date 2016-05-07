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

class MoveItemToProjectType extends AbstractType {

    private $container;
    private $translator;
    private $tokenStorage;
    
    const MOVE_TO_PROJECT = 'move';
    const COPY_TO_PROJECT = 'copy';
    const ACTION_METHOD_CASCADE = 'cascade';
    const ACTION_METHOD_SIMPLE = 'simple';

    public function __construct(Container $container, $tokenStorage) {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->tokenStorage = $tokenStorage;
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
                $userId = $this->tokenStorage->getToken()->getUser()->getId();
                $form
                        ->add('user_project', EntityType::class, array(
                            'class' => 'BackendBundle:UserProject',
                            'mapped' => false,
                            'query_builder' => function (EntityRepository $er) use ($project, $userId) {
                                return $er->createQueryBuilder('usp')
                                        ->join("BackendBundle:Project", "p")
                                        ->where("usp.user = '".$userId."'")
                                        ->andWhere("usp.project <> '".$project->getId()."'")
                                        ->orderBy('p.name', 'ASC');
                            },
                            'required' => true,
                            'label' => $this->translator->trans('backend.item.select_project'),
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
                        'Move' => self::MOVE_TO_PROJECT,
                        'Copy' => self::COPY_TO_PROJECT
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
        return 'backendbundle_item_move_to_project_type';
    }

}
