<?php

namespace App\Form;

use App\Entity\OrdersRows;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;

class OrdersRowsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('rowId', HiddenType::class)
            ->add('cnt', TextType::class, [
                'attr' => [
                    'class' => 'count',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'Некорректное значение.',
                    ])
                ]
            ]);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $row = $data->getRow();
            $price = $row->getPrice();
            $type = $data->getType();

            $form = $event->getForm();
            $form->add('price', HiddenType::class, [
                'mapped' => false,
                'data' => $price
            ]);
            if ($type) {
                $form->add('type', HiddenType::class);
            }
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrdersRows::class,
            'order_id' => null,
            'rows' => null
        ]);
    }
}
