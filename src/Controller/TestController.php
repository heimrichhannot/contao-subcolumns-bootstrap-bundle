<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao/test", name=TestController::class, defaults={"_scope" = "backend", "_token_check" = false})
 */
class TestController extends AbstractController
{

    public function __construct()
    {
    }

    public function __invoke(): Response
    {
        return new Response('Oke.');
    }

}