<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Plugin
 *
 * @package HeimrichHannot\SubColumnsBootstrapBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface, ConfigPluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        $loadAfter = [ContaoCoreBundle::class, 'Subcolumns'];

        if (class_exists('onemarshall\AosBundle\AosBundle')) {
            $loadAfter[] = 'onemarshall\AosBundle\AosBundle';
        }

        return [
            BundleConfig::create(SubColumnsBootstrapBundle::class)
                ->setLoadAfter($loadAfter),
        ];
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load("@SubColumnsBootstrapBundle/config/services.yaml");
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $resource = '@SubColumnsBootstrapBundle/config/routes.yaml';
        return $resolver->resolve($resource)->load($resource);
    }
}
