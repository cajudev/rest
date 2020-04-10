<?php

namespace Cajudev\Rest;

use Cajudev\Rest\Exception\MissingConfigurationException;

class EntityManager
{
    public static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            if (!($conn = Config::getInstance()->get('database'))) {
                throw new MissingConfigurationException('Parâmetro [database] não encontrado no arquivo config.json');
            }

            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                [__ROOT__ . '/src/entity'],
                $isDevMode = __DEV__,
                $proxyDir = null,
                $cache = null,
                $useSimpleAnnotationReader = false
            );

            $namingStrategy = new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy(CASE_LOWER);
            $config->setNamingStrategy($namingStrategy);

            self::$instance = \Doctrine\ORM\EntityManager::create($conn, $config);
        }
        return self::$instance;
    }
}
