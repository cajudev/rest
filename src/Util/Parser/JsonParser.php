<?php

namespace Cajudev\RestfulApi\Util\Parser;

class JsonParser implements Parser
{
    public function parse(string $string)
    {
        return new \Cajudev\Collection(json_decode($string, true));
    }
}
