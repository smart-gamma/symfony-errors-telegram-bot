<?php

namespace Gamma\ErrorsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GammaErrorsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $subscriberDefinition = $container->getDefinition('gamma_errors.subscriber.error_handler');
        if ($config['enabled']) {
            $subscriberDefinition->addTag('kernel.event_subscriber');
        }

        $handlerDefinition = $container->getDefinition('gamma_errors.debug.error_handler');
        $handlerDefinition->addArgument($config['log_strict']);

        $managerDefinition = $container->getDefinition('gamma_errors.manager.errors_manager');
        $managerDefinition->addMethodCall('setRepeatNotificationMinutes', [$config['repeat_notification_minutes']]);

        $channelsManagerDefinition = $container->getDefinition('gamma_errors.manager.channels_manager');

        if (array_key_exists('mail_channel', $config)) {
            $channelConfig = $config['mail_channel'];
            $mailChannelDefinition = $container->getDefinition('gamma_errors.channel.mail');
            $mailChannelDefinition->addMethodCall('setEmails', [$channelConfig['emails']]);
            $mailChannelDefinition->addMethodCall('setFromData', [$channelConfig['from_email'], $channelConfig['from_name']]);

            $channelsManagerDefinition->addMethodCall('addChannel', ['mail', $mailChannelDefinition]);
        }

        if (array_key_exists('telegram_channel', $config)) {
            $channelConfig = $config['telegram_channel'];
            $telegramChannelDefinition = $container->getDefinition('gamma_errors.channel.telegram');
            $telegramChannelDefinition->addMethodCall('setAuthKey', [$channelConfig['auth_key']]);
            $telegramChannelDefinition->addMethodCall('setChatId', [$channelConfig['chat_id']]);

            $channelsManagerDefinition->addMethodCall('addChannel', ['telegram', $telegramChannelDefinition]);
        }

        if (array_key_exists('slack_channel', $config)) {
            $channelConfig = $config['slack_channel'];
            $telegramChannelDefinition = $container->getDefinition('gamma_errors.channel.slack');
            $telegramChannelDefinition->addMethodCall('setWebhook', [$channelConfig['webhook']]);
            $telegramChannelDefinition->addMethodCall('setSlackChannel', [$channelConfig['channel']]);

            $channelsManagerDefinition->addMethodCall('addChannel', ['slack', $telegramChannelDefinition]);
        }
    }
}
