<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $parameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('app_parameter', [$this, 'getAppParameter']),
        ];
    }

    public function getAppParameter($name)
    {
        return $this->parameters->get($name);
    }
}