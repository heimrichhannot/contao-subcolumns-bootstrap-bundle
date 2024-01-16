<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(ColumnSetPartElementController::TYPE, category="subcolumns")
 */
class ColumnSetPartElementController extends AbstractContentElementController
{
    public const TYPE = 'columnset_part';

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        // TODO: Implement getResponse() method.
        return null;
    }
}