<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class EncoreExtension implements EncoreExtensionInterface
{

    /**
     * @inheritDoc
     */
    public function getBundle(): string
    {
        return SubColumnsBootstrapBundle::class;
    }

    /**
     * @inheritDoc
     */
    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-subcolumns-bootstrap-bundle', 'src/Resources/public/js/contao-subcolumns-bootstrap-bundle.fe.es6.js')
                ->addJsEntryToRemoveFromGlobals('contao-subcolumns-bootstrap-bundle')
        ];
    }
}