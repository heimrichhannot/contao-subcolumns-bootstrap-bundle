<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Asset;

use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FrontendAsset implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public function addAsset(): void
    {
        $this->addPageEntrypoint('contao-subcolumns-bootstrap-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-subcolumns-bootstrap-bundle' => 'bundles/subcolumnsbootstrap/js/contao-subcolumns-bootstrap-bundle.fe.min.js|static',
            ]
        ]);
    }
}