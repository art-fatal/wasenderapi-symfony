<?php

namespace WasenderApi\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wasenderapi');
        $rootNode = method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('wasenderapi');

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->defaultValue('')
                    ->info('Session API key for sending messages')
                ->end()
                ->scalarNode('personal_access_token')
                    ->defaultValue('')
                    ->info('Personal access token for session management endpoints')
                ->end()
                ->scalarNode('base_url')
                    ->defaultValue('https://www.wasenderapi.com/api')
                    ->info('Base URL of the WasenderAPI')
                ->end()
                ->arrayNode('webhook')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('secret')
                            ->defaultValue('')
                            ->info('Secret used to verify webhook signatures')
                        ->end()
                        ->scalarNode('path')
                            ->defaultValue('/wasender/webhook')
                            ->info('URL path for the webhook endpoint')
                        ->end()
                        ->scalarNode('signature_header')
                            ->defaultValue('x-webhook-signature')
                            ->info('HTTP header containing the webhook signature')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
