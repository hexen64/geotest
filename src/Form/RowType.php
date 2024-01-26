<?php

namespace App\Form;

use App\Entity\Rows;
use App\Services\Formatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class RowType extends AbstractType
{

    public function __construct(private Formatter $formatter)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('save', SubmitType::class, ['label' => 'Добавить к заказу']);
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {
                $form = $event->getForm();
                $row = $event->getData();
                $form = $event->getForm();
                $name = $row->getName();
                $price = $row->getPrice();
                $form
                    ->add('id', HiddenType::class, [
                            'data' => $row->getId(),
                            'label' => $name
                        ]
                    )
                    ->add('price', HiddenType::class, [
                            'data' => $price,
                            'label_html' => true,
                            'label' => $this->formatter->formatRub($price),
                            'required' => true,
                            'attr' => [
                                'class' => 'price',
                                'readonly' => 'readonly',
                            ]
                        ]
                    )
                    ->add('count', TextType::class,
                        [
                            'label' => false,
                            'mapped' => false,
                            'attr' => ['class' => 'count', 'autocomplete' => 'off'],
                            'data' => 1,
                            'constraints' => [
                                new Type([
                                    'type' => 'numeric',
                                    'message' => 'Некорректное значение.',
                                ])
                            ]
                        ]
                    );
                if ($row->getIdk() !== $row->getIdl()) {
                    $form->add('type', HiddenType::class, [
                        'data' => 'l'
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Rows::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'row',
        ]);
    }
}
