<?php

namespace App\Form;

use App\Entity\Orders;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrdersType extends AbstractType
{

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected static array $delivery = [
        'На склад заказчика' => 'firm',
        'На склад транспортной компании' => 'firm+sklad',
        'Самовывоз' => 'self',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $request = $this->requestStack->getCurrentRequest();
        $cookies = $request->cookies;
        $fio = $cookies->get('fio');
        $phone = $cookies->get('phone');
        $email = $cookies->get('email');
        $firm = $cookies->get('firm');
        $address = $cookies->get('address');
        $order = $options['data'];
        $keys = array_keys(self::$delivery);

        $builder
            ->add('id', HiddenType::class, [
                'mapped' => false,
                'data' => $order->getId(),
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Не заполнено'])
                ]
            ])
            ->add(
                'url',
                HiddenType::class, [
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new Length(['max' => 0]),
                    ],
                ]
            )->add(
                'fio',
                TextType::class,
                [
                    'required' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Как вас зовут',
                        'class' => 'w100',
                    ],
                    'data' => $fio,
                    'constraints' => [
                        new NotBlank(['message' => 'Не заполнено']),
                        new Length(['max' => 255]),
                    ],
                ]
            )->add(
                'phone',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Телефон',
                        'class' => 'w100',
                    ],
                    'data' => $phone,
                ]
            )->add(
                'email',
                TextType::class,
                [
                    'required' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'E-mail',
                        'class' => 'w100',
                    ],
                    'data' => $email,
                    'constraints' => [
                        new NotBlank(['message' => 'Не заполнен']),
                        new Email(['message' => 'Неправильный e-mail']),
                    ],
                ]
            )->add(
                'firm',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Организация',
                        'class' => 'w100',
                    ],
                    'data' => $firm,
                ]
            )->add(
                'address',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Ваш адрес (для доставки)',
                        'class' => 'w100',
                    ],
                    'data' => $address,
                ]
            )->add(
                'comment',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Дополнительные сведения',
                        'class' => 'w100',
                    ],
                    'data' => '',
                ]
            )
            ->add('delivery', ChoiceType::class, [
                    'required' => false,
                    'label' => false,
                    'choices' => self::$delivery,
                    'expanded' => true,
                    'multiple' => false,
                    'data' => 'firm',
                    'attr' => [
                        'style' => 'display:none',
                    ],
                ]
            )->add(
                'ordersVariants',
                CollectionType::class,
                [
                    'label' => false,
                    'entry_type' => OrdersVariantsType::class,
                    'entry_options' => [
                        'order_id' => $order->getId(),
                        'variants' => $order->getVariants(),
                    ],
                    'attr' => [
                        'style' => 'display:none',
                    ],
                ]
            )
            ->add(
                'ordersRows',
                CollectionType::class,
                [
                    'label' => false,
                    'entry_type' => OrdersRowsType::class,
                    'entry_options' => [
                        'order_id' => $order->getId(),
                        'rows' => $order->getRows(),
                    ],
                    'attr' => [
                        'style' => 'display:none',
                    ],
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Отправить заказ', 'attr' => [
                'class' => 'btn btn-primary btn-lg w100 btn_order'
            ]]);

        $builder->get('delivery')->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
            'variants' => null
        ]);
    }

    public static function getDelivery ()
    {
        return self::$delivery;
    }
}
