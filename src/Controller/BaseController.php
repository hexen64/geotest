<?php

namespace App\Controller;

use App\Services\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{

    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {

        $legacyParam = $this->parameterBag->get('app_menu_items');

        // Merge the app_menu_items variable with the other parameters
        $parameters = array_merge($parameters, ['app_menu_items' => $legacyParam]);

        // Render the template and pass the merged parameters
        return parent::render($view, $parameters, $response);
    }
}
