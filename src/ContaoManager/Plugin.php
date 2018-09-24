<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class Plugin
 *
 * @package HeimrichHannot\SubColumnsBootstrapBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle')->setLoadAfter(['Subcolumns']),
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $file = '@SubColumnsBootstrapBundle/Resources/config/config.yml';

        $loader->load($file);
    }
}
