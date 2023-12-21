<?php

namespace App\Form;

use App\Entity\Rows;
use App\Entity\VariantsRows;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use function Sodium\add;

class VariantsRowsType extends AbstractType implements FormTypeInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('row', RowsType::class, [
                'data_class' => Rows::class,
                'label' => false,
                'row_attr' => [
                    'class' => 'variant-row-name'
                ]
            ])
            ->add('cnt', IntegerType::class, [
                'row_attr' => [
                    'class' => 'variant-row-count-sum',
                ],
                'attr' => [
                    'class' => 'count'
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank(['message' => 'This field is required']),
                    new Type([
                        'type' => 'integer',
                        'message' => 'Invalid value',
                    ]),
                ],

            ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {

                $form = $event->getForm();
                $variantRow = $event->getData();
                $total = $variantRow->getRow()->getPrice() * $variantRow->getCnt();
                $formattedPrice = format_rub($total);

                $form->add('base_count', HiddenType::class, [
                    'mapped' => false,
                    'data' => $variantRow->getCnt(),
                    'constraints' => [
                        new NotBlank(['message' => 'This field is required']),
                        new Type([
                            'type' => 'integer',
                            'message' => 'Invalid value',
                        ]),
                    ],
                ]);

            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VariantsRows::class,
            'row' => Rows::class,
        ]);
    }
}
