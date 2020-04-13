<?php

namespace Cajudev\Rest\Utils;

use Cajudev\Rest\Utils\Parser\Parser;
use Cajudev\Rest\Exceptions\MissingConfigurationException;

class Setter
{
    const MODE_DEFAULT = 0;
    const MODE_SAFE = 1;
    const MODE_FORCE = 2;

    private object $_object;
    private array $_params;

    public function __construct(object $object, array $params)
    {
        $this->_object = $object;
        $this->_params = $params;
    }

    public function set(int $mode = self::MODE_DEFAULT)
    {
        foreach ($this->_params as $key => $value) {
            $this->_set($key, $value, $mode);
        }
    }

    private function _set(string $key, $value, $mode) {
        switch($mode) {
            case self::MODE_DEFAULT:
                return $this->_default($key, $value);
            case self::MODE_SAFE:
                return $this->_safe($key, $value);
            case self::MODE_FORCE:
                return $this->_force($key, $value);
        }
    }

    private function _default(string $key, $value) {
        if (property_exists($this->_object, $key)) {
            $this->_object->$key = $value;
        }
    }

    private function _safe(string $key, $value) {
        $setter = 'set' . ucfirst($key);
        if (method_exists($this->_object, $setter)) {
            $this->_object->$setter($value);
        }
    }

    private function _force(string $key, $value) {
        if (property_exists($this->_object, $key)) {
            $reflection = new \ReflectionProperty($this->_object, $key);
            $reflection->setAccessible(true);
            $reflection->setValue($this->_object, $value);
        }
    }
}
