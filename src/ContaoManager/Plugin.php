<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class Plugin
 *
 * @package HeimrichHannot\SubColumnsBootstrapBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface, ExtensionPluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        $loadAfter = ['Subcolumns'];

        if (class_exists('onemarshall\AosBundle\AosBundle')) {
            $loadAfter[] = 'onemarshall\AosBundle\AosBundle';
        }

        return [
            BundleConfig::create('HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle')->setLoadAfter($loadAfter),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config_encore.yml'
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load("@SubColumnsBootstrapBundle/Resources/config/services.yml");
    }
}
