<?php

namespace App\Form;

use App\Entity\Rows;
use App\Services\Formatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class RowsType extends AbstractType
{

    private $router;

    public function __construct(RouterInterface $router, private Formatter $formatter)
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
                $rowId = $data->getId();

                if ($data->getFixed()) {
                    $name = $data->getName();
                } else {
                    $nameRoute = $this->router->generate('app_row_show', [
                        'rowId' => $rowId,
                        'groupId' => null
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                    $name = '<a href="' . $nameRoute . '">' . $data->getName() . '</a>';
                }

                $price = $data->getPrice();
                $formattedPrice = $this->formatter->formatRub($price);

                $form
                    ->add('id', TextType::class, [
                        'label_html' => true,
                        'label' => $name,
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
                        'attr' => [
                            'class' => 'price',
                            'readonly' => 'readonly',
                            'style' => 'display:none'
                        ]
                    ]);

                if ($data->getFixed()) {
                    $form->add('fixed', HiddenType::class, ['mapped' => false]);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Rows::class,
        ]);
    }
}