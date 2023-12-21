<?php

namespace App\Form;

use App\Entity\Rows;
use App\Entity\VariantsRows;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class RowsType extends AbstractType
{

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {
                $form = $event->getForm();
                $data = $event->getData();
                $name = $data->getName();
                $linkName = $this->router->generate('app_row_show', [
                    'rowId' => $data->getId(),
                    'groupId' => null
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                $name = '<a href="'.$linkName.'">'.$name.'</a>';
                $price = $data->getPrice();
                $formattedPrice = format_rub($price);

                $form
                    ->add('id', TextType::class, [
                        'label_html' => true,
                        'label' => $name,
                        'disabled' => true,
                        'attr' => [
                            'class' => 'row-name',
                            'readonly' => 'readonly',
                            'style' => 'display:none'
                        ],
                        'constraints' => [
                            new NotBlank(['message' => 'This field is required']),
                        ],
                    ])
                    ->add('price', TextType::class, [
                        'label_html' => true,
                        'label' => $formattedPrice,
                        'disabled' => true,
                        'attr' => [
                            'class' => 'row-price',
                            'readonly' => 'readonly',
                            'style' => 'display:none'
                        ],
                        'constraints' => [
                            new NotBlank(['message' => 'This field is required']),
                            new Type([
                                'type' => 'numeric',
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
            'data_class' => Rows::class,
        ]);
    }
}