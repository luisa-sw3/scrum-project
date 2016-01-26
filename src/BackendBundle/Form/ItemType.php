<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;
use BackendBundle\Entity\Item;

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
        $builder
                ->add('title', Type\TextType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.title')
                ))
                ->add('description', Type\TextareaType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.item.description')
                ))
                ->add('type', Type\ChoiceType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.type'),
                    'choices' => array(
                        Item::TYPE_USER_HISTORY => Item::TYPE_USER_HISTORY,
                        Item::TYPE_TASK => Item::TYPE_TASK,
                        Item::TYPE_BUG => Item::TYPE_BUG,
                        Item::TYPE_IMPROVEMENT => Item::TYPE_IMPROVEMENT,
                        Item::TYPE_IDEA => Item::TYPE_IDEA,
                    )
                ))
                ->add('priority', Type\NumberType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.item.priority')
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
