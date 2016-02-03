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

class SearchItemType extends AbstractType {

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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof Item) {
                $project = $data->getProject();

                $form->add('item_designed_user', EntityType::class, array(
                    'class' => 'BackendBundle:User',
                    'query_builder' => function (EntityRepository $er) use ($project) {
                        return $er->createQueryBuilder('u')
                                        ->join('BackendBundle:UserProject', 'uspr')
                                        ->where('uspr.user = u.id')
                                        ->andWhere(($project != null ? "uspr.project = '" . $project->getId() . "'" : '1=1'))
                                        ->orderBy('u.name', 'ASC');
                    },
                    'required' => false,
                    'mapped' => false,
                    'label' => $this->translator->trans('backend.item.designed_user'),
                    'placeholder' => $this->translator->trans('backend.global.select'),
                ));
            }
        });

        $builder
                ->add('item_free_text', Type\TextType::class, array(
                    'required' => false,
                    'mapped' => false,
                    'label' => $this->translator->trans('backend.item.title_description'),
                    'attr' => array(
                        'maxlength' => 255,
                        'placeholder' => $this->translator->trans('backend.item.title_description'),
                    )
                ))
                ->add('item_type', Type\ChoiceType::class, array(
                    'required' => false,
                    'mapped' => false,
                    'label' => $this->translator->trans('backend.item.type'),
                    'choices' => $typeOptions,
                    'placeholder' => $this->translator->trans('backend.global.select'),
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
        return 'backendbundle_search_item_type';
    }

}
