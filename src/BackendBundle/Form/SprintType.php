<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SprintType extends AbstractType {

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

        $project = null;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data instanceof Entity\Project) {
                $project = $data->getProject();
            }
        });
        
        $builder
                ->add('name', Type\TextType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.sprint.name'),
                    'attr' => array(
                        'maxlength' => 255
                    )
                ))
                ->add('description', Type\TextareaType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.sprint.description')
                ))
                ->add('startDate', Type\DateType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.sprint.start_date'),
                    'placeholder' => array(
                        'year' => $this->translator->trans('backend.global.year'),
                        'month' => $this->translator->trans('backend.global.month'),
                        'day' => $this->translator->trans('backend.global.day')
                    ),
                    'format' => ($project != null ? $project->getSettings()->getPHPDateFormat() : 'y-M-d'),
                ))
                ->add('estimatedDate', Type\DateType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.sprint.estimated_date'),
                    'placeholder' => array(
                        'year' => $this->translator->trans('backend.global.year'),
                        'month' => $this->translator->trans('backend.global.month'),
                        'day' => $this->translator->trans('backend.global.day')
                    ),
                    'format' => ($project != null ? $project->getSettings()->getPHPDateFormat() : 'y-M-d'),
                ))
                ->add('isWorkingWeekends', Type\CheckboxType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.sprint.work_weekends'),
                    'attr' => array(
                    )
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Sprint'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'backendbundle_sprint_type';
    }

}
