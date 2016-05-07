<?php

namespace BackendBundle\Form\TimeTracking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use BackendBundle\Entity\TimeTracking;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use BackendBundle\Entity\Sprint;

class TimeTrackType extends AbstractType {

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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof TimeTracking) {
                $user = $data->getUser();

                $form
                        ->add('project', EntityType::class, array(
                            'class' => 'BackendBundle:Project',
                            'query_builder' => function (EntityRepository $er) use ($user) {
                                return $er->createQueryBuilder('p')
                                        ->join('BackendBundle:UserProject', 'uspr')
                                        ->where('uspr.project = p.id')
                                        ->andWhere(($user != null ? "uspr.user = '" . $user->getId() . "'" : '1=1'))
                                        ->orderBy('p.name', 'ASC');
                            },
                            'required' => true,
                            'label' => $this->translator->trans('backend.global.project'),
                            'placeholder' => $this->translator->trans('backend.item.select_project'),
                ));
            }
        });

        $builder
                ->add('taskId', Type\ChoiceType::class, array(
                    'required' => true,
                    'mapped' => false,
                    'label' => $this->translator->trans('backend.global.task')
                ))
                ->add('description', Type\TextareaType::class, array(
                    'required' => false,
                    'label' => $this->translator->trans('backend.global.description'),
                    'attr' => array(
                        'placeholder' => $this->translator->trans('backend.time_tracking.task_description')
                    )
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\TimeTracking'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'backendbundle_time_track_type';
    }

}
