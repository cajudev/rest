<?php

namespace Cajudev\RestfulApi;

use Cajudev\RestfulApi\Exception\MissingConfigurationException;

class EntityManager
{
    public static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                [PATH_ROOT . '/src/entity'],
                $isDevMode = __DEV__,
                $proxyDir = null,
                $cache = null,
                $useSimpleAnnotationReader = false
            );
            if (!($conn = Config::getInstance()->get('database'))) {
                throw new MissingConfigurationException('Parâmetro [database] não encontrado no arquivo config.json');
            }
            self::$instance = \Doctrine\ORM\EntityManager::create($conn->get(), $config);
        }
        return self::$instance;
    }
}
