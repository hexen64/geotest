<?php

namespace App\Form;

use App\Entity\Variants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('variant_id',  HiddenType::class, [
                'mapped' => false,
                'data' => $options['data']->getId()
            ])
            ->add('base', CollectionType::class, [
                'entry_type' => VariantsRowsType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'label' => false,
                'mapped' => false,
                'data' => $options['base_rows'],
                'attr' => [
                    'class' => 'row variant-row'
                ]

            ])
            ->add('not_base', CollectionType::class, [
                'entry_type' => VariantsRowsType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'mapped' => false,
                'data' => $options['not_base_rows'],
                'label' => false,
                'attr' => [
                    'class' => 'row variant-row'
                ]
            ])
            ->add('count', TextType::class, [
                'data' => 1,
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'count'
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Добавить к заказу']);


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {

                $form = $event->getForm();
                $variant = $event->getData();
                $rows = $variant->getRows();
                $cnt = 0;
                foreach ($rows as $row) {
                    if ($row->getIdk() !== $row->getIdl()) {
                        $cnt++;
                    }
                }
                if ($cnt > 0)
                    $form->add('type', ChoiceType::class, [
                        'choices' => [
                            'l' => 'ленточная',
                            'k' => 'коническая'
                        ],
                        'mapped' => false
                    ]);
            }
        );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Variants::class,
            'base_rows' => null,
            'not_base_rows' => null
        ]);
    }

}


