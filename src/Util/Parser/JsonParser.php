<?php

namespace Cajudev\RestfulApi\Util\Parser;

use Doctrine\Common\Collections\ArrayCollection;

class JsonParser implements Parser
{
    public function parse(string $string)
    {
        return new ArrayCollection(json_decode($string, true));
    }
}
