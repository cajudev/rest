<?php

namespace Cajudev\Rest\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Query
{
    /**
     * @var bool
     */
    public $sortable;

    /**
     * @var bool
     */
    public $searchable;
}
