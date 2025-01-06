<?php

namespace OCA\MetadataGenerator\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use Psr\Log\LoggerInterface;
use OCP\ILogger;

class Application extends App implements IBootstrap {
    public const APP_ID = 'metadatagenerator';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    /**
     * Register services during app registration.
     */
    public function register(IRegistrationContext $context): void {
        // Register the LoggerInterface
        #$context->registerService(LoggerInterface::class, function ($c) {
        #    return $c->query(ILogger::class);
        #});
        $context->registerService(\Psr\Log\LoggerInterface::class, function ($c) {
            return $c->query(\OCP\ILogger::class);
        });
    }

    /**
     * Perform actions during the app boot process.
     */
    public function boot(IBootContext $context): void {
        $logger = $context->getAppContainer()->query(LoggerInterface::class);
        $logger->info(self::APP_ID . ' app bootstrapped successfully.');
    }
}
