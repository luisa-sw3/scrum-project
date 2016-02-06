<?php

namespace BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use BackendBundle\Entity as Entity;

class ProjectType extends AbstractType {

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
                $project = $data;
            }
        });

        $currentYear = (new \DateTime('now'))->format('Y') - 1;
        $yearsForStart = $yearsEstimated = array();
        for ($i = 0; $i < 3; $i++) {
            array_push($yearsForStart, $currentYear);
            if ($i > 0) {
                array_push($yearsEstimated, $currentYear);
            }
            $currentYear++;
        }
        array_push($yearsEstimated, $currentYear);

        $builder
                ->add('name', Type\TextType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.project.name')
                ))
                ->add('description', Type\TextareaType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.project.description')
                ))
                ->add('startDate', Type\DateType::class, array(
                    'required' => true,
                    'label' => $this->translator->trans('backend.project.start_date'),
                    'placeholder' => array(
                        'year' => $this->translator->trans('backend.global.year'),
                        'month' => $this->translator->trans('backend.global.month'),
                        'day' => $this->translator->trans('backend.global.day')
                    ),
                    'format' => ($project != null ? $project->getSettings()->getPHPDateFormat() : 'y-M-d'),
                    'years' => $yearsForStart,
                ))
                ->add('estimatedDate', Type\DateType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.project.estimated_date'),
                    'placeholder' => array(
                        'year' => $this->translator->trans('backend.global.year'),
                        'month' => $this->translator->trans('backend.global.month'),
                        'day' => $this->translator->trans('backend.global.day')
                    ),
                    'format' => ($project != null ? $project->getSettings()->getPHPDateFormat() : 'y-M-d'),
                    'years' => $yearsEstimated,
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\Project'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'backendbundle_project_type';
    }

}
