<?php

namespace App\Form;

use App\Entity\OrdersVariants;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;

class OrdersVariantsType extends AbstractType
{
//    private $entityManager;
//
//    public function __construct(EntityManagerInterface $entityManager)
//    {
//        $this->entityManager = $entityManager;
//
//    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('id', HiddenType::class)
            ->add('name', HiddenType::class)
            ->add('variant_id', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('cnt', TextType::class, [
                'attr' => [
                    'class' => 'count'
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
            $orderRows = $data->getRows()->toArray();
            $variant = $data->getVariant();
            $variantRows = $variant->getVariantsRows();
            $price = 0;
            $diff = 0;
            if (!empty($orderRows)) {
                foreach ($orderRows as $item) {
                    foreach ($variantRows as $vRow) {
                        $row = $vRow->getRow();
                        if ($vRow->getRowId() == $item->getRowId()) {
                            $price += $row->getPrice() * ($vRow->getCnt() + $item->getDiff());
                        } else {
                            $price += $row->getPrice() * $vRow->getCnt();
                        }
                    }
                }
            } else {
                foreach ($variantRows as $vRow) {
                    $price += $vRow->getRow()->getPrice() * $vRow->getCnt();
                }
            }
            $data->setPrice($price);
            $event->setData($data);
            $form = $event->getForm();
            $form->add('price', HiddenType::class);
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrdersVariants::class,
            'order_id' => null,
            'variants' => null
        ]);
    }
}
