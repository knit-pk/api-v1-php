<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';


    /**
     * Get root path of var directory
     *
     * @return string
     */
    public function getVarDir(): string
    {
        $varDir = (!empty($_ENV['APP_VAR_PATH'])) ? $_ENV['APP_VAR_PATH'] : dirname(__DIR__) . '/var';

        return $_ENV['APP_VAR_PATH'] = rtrim($varDir, '/\\');
    }


    public function getCacheDir(): string
    {
        return $this->getVarDir() . '/cache/' . $this->environment;
    }


    public function getLogDir(): string
    {
        return $this->getVarDir() . '/log';
    }


    public function registerBundles(): iterable
    {
        $contents = require dirname(__DIR__) . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }


    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $confDir = dirname(__DIR__) . '/config';
        $loader->load($confDir . '/packages/*' . self::CONFIG_EXTS, 'glob');
        if (is_dir($confDir . '/packages/' . $this->environment)) {
            $loader->load($confDir . '/packages/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        }
        $loader->load($confDir . '/services' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/services_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }


    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = dirname(__DIR__) . '/config';
        if (is_dir($confDir . '/routes/')) {
            $routes->import($confDir . '/routes/*' . self::CONFIG_EXTS, '/', 'glob');
        }
        if (is_dir($confDir . '/routes/' . $this->environment)) {
            $routes->import($confDir . '/routes/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        }
        $routes->import($confDir . '/routes' . self::CONFIG_EXTS, '/', 'glob');
    }
}
