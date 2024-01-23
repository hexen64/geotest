<?php

namespace App\Form;

use App\Entity\Rows;
use App\Entity\VariantsRows;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class VariantsRowsType extends AbstractType implements FormTypeInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('row', RowsType::class, [
                'required' => true,
                'data_class' => Rows::class,
                'label' => false,
                'row_attr' => [
                    'class' => 'variant-row-name'
                ]
            ]);


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $variantRow = $event->getData();
                $total = $variantRow->getRow()->getPrice() * $variantRow->getCnt();
                $form->add('base_count', HiddenType::class, [
                    'mapped' => false,
                    'data' => $variantRow->getCnt(),
                    'attr' => [
                        'class' => 'base_count'
                    ],
                ])->add('cnt', TextType::class, [
                    'mapped' => false,
                    'data' => $variantRow->getCnt(),
                    'required' => true,
                    'row_attr' => [
                        'class' => 'variant-row-count-sum',
                    ],
                    'attr' => [
                        'class' => 'count'
                    ],
                    'label' => false,
                    'constraints' => [
                        new Type([
                            'type' => 'numeric',
                            'message' => 'Некорректное значение.',
                        ])
                    ]
                ]);
            }

        );
    }


    public
    function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VariantsRows::class,
            'row' => Rows::class,
        ]);
    }
}
