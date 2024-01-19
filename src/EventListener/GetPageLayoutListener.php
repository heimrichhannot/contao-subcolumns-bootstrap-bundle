<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

/**
 * @Hook("getPageLayout")
 */
class GetPageLayoutListener
{
    /**
     * @throws Exception
     */
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular)
    {
        $theme = $layout->getRelated('pid');
        if ($theme->subcolumns !== null) {
            SubColumnsBootstrapBundle::setProfile($theme->subcolumns);
        }
    }
}
