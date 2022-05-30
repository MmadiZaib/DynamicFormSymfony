<?php


namespace App\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DynamicFormExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new DynamicFormConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('dynamic_form.config', $config);
    }

    public function getAlias()
    {
        return 'dynamic_form';
    }
}
