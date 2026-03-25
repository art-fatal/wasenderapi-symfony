<?php

namespace WasenderApi\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class WasenderApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wasenderapi.api_key', $config['api_key']);
        $container->setParameter('wasenderapi.personal_access_token', $config['personal_access_token']);
        $container->setParameter('wasenderapi.base_url', $config['base_url']);
        $container->setParameter('wasenderapi.webhook.secret', $config['webhook']['secret']);
        $container->setParameter('wasenderapi.webhook.path', $config['webhook']['path']);
        $container->setParameter('wasenderapi.webhook.signature_header', $config['webhook']['signature_header']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
