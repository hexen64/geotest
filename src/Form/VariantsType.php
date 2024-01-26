<?php

namespace App\Form;

use App\Entity\Variants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Valid;

class VariantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('id', HiddenType::class, [
                'data' => $options['data']->getId(),
                'required' => true
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
                ],
            ])
            ->add('not_base', CollectionType::class, [
                'entry_type' => VariantsRowsType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'label' => false,
                'mapped' => false,
                'data' => $options['not_base_rows'],
                'attr' => [
                    'class' => 'row variant-row'
                ]
            ])
            ->add('cnt', TextType::class, [
                'mapped' => false,
                'data' => 1,
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'count',
                ],
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'Некорректное значение.',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Добавить к заказу']);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();
                $rows = $data->getRows();
                $variantRows = $data->getVariantsRows();
                $data->setCnt(1);

                foreach ($rows as $row) {
                    if ($row->getIdk() !== $row->getIdl()) {
                        $form->add('type', HiddenType::class, [
                            'mapped' => false
                        ]);
                        break;
                    }
                }
            }
        );
    }


    public
    function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Variants::class,
            'base_rows' => null,
            'not_base_rows' => null,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'variants',
        ]);
    }

}


