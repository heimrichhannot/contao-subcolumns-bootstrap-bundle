<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(ColumnSetEndElementController::TYPE, category="subcolumns")
 */
class ColumnSetEndElementController extends AbstractContentElementController
{
    public const TYPE = 'columnset_end';

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        // TODO: Implement getResponse() method.
        return new Response();
    }
}