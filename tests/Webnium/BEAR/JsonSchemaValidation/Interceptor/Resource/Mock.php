<?php

namespace Webnium\BEAR\JsonSchemaValidation\Interceptor\Resource;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;

class Mock extends ResourceObject
{
    public function __construct()
    {
        $this->links = [
            'describedBy' => [Link::HREF => 'file://' . __DIR__ . '/schema/default.json']
        ];
    }

    /**
     * on post
     */
    public function onGet()
    {
        return $this;
    }

    /**
     * on post
     */
    public function onPost()
    {
        return $this;
    }
}
