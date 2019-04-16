<?php

namespace Gamma\ErrorsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gamma_errors');

        $rootNode
            ->children()
                ->scalarNode('enabled')->defaultFalse()->end()
                ->scalarNode('log_strict')->defaultFalse()->end()
                ->scalarNode('repeat_notification_minutes')->defaultValue(30)->end()
                ->arrayNode('mail_channel')
                    ->children()
                        ->arrayNode('emails')
                            ->performNoDeepMerging()
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('from_email')->defaultValue('error@example.com')->end()
                        ->scalarNode('from_name')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('telegram_channel')
                    ->children()
                        ->scalarNode('auth_key')->defaultValue('')->end()
                        ->scalarNode('chat_id')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('slack_channel')
                    ->children()
                        ->scalarNode('webhook')->defaultValue('')->end()
                        ->scalarNode('channel')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
