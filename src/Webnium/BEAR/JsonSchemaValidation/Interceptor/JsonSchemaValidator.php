<?php
/**
 * This file is part of Webnium BEAR.JsonSchemaValidation
 *
 * URL: https://github.com/webnium/BEAR.JsonSchemaValidation/
 */

namespace Webnium\BEAR\JsonSchemaValidation\Interceptor;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;
use BEAR\Resource\Code;
use Ray\Aop\MethodInvocation;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\NamedArgsInterface;
use Ray\Di\Di\Inject;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator as JsonValidator;
use Webnium\BEAR\JsonSchemaValidation\Exception;

/**
 * Validator for BEAR ResourceObjects
 *
 */
class JsonSchemaValidator implements MethodInterceptor
{
    /** @var UriRetriever */
    private $retriever;

    /** @var JsonValidator */
    private $validator;

    /** @var NamedArgsInterface */
    private $namedArgs;

    /**
     * Inject NamedArgs
     *
     * @param NamedArgsInterface $namedArgs
     * @Inject
     */
    public function setNamedArgs(NamedArgsInterface $namedArgs)
    {
        $this->namedArgs = $namedArgs;
    }

    /**
     * Inject UriRetriever
     *
     * @param UriRetriever $retriever
     * @Inject
     */
    public function setRetriever(UriRetriever $retriever)
    {
        $this->retriever = $retriever;
    }

    /**
     * Inject Json Validator
     *
     * @param JsonValidator $validator
     * @Inject
     */
    public function setValidator(JsonValidator $validator)
    {
        $this->validator = $validator;
    }


    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();

        if (!preg_match('/^on(.+)$/', $invocation->getMethod()->name, $matches)) {
            throw new Exception\InvalidBinding;
        }

        $method = strtolower($matches[1]);

        $schema = $this->retrieveSchemaForMethod($ro, $method);
        if ($schema === null) {
            return $invocation->proceed();
        }

        $this->validator->check((object)json_decode(json_encode($this->namedArgs->get($invocation))), $schema);

        if ($this->validator->isValid()) {
            return $invocation->proceed();
        }

        $ro->code = Code::BAD_REQUEST;
        $ro->body = ['errors' => $this->validator->getErrors()];

        return $ro;
    }

    /**
     * Retrieve schema for method
     *
     * @param ResourceObject $ro
     * @param string         $method
     *
     * @return stdClass|null schema for the method
     */
    private function retrieveSchemaForMethod(ResourceObject $ro, $method)
    {
        $schema = $this->retriever->retrieve($ro->links['describedBy'][Link::HREF]);
        if (empty($schema->links)) {
            return null;
        }

        $methodSchema = null;

        foreach ($schema->links as $link) {
            if ($link->rel !== 'self') {
                continue;
            }

            if (empty($link->method)) {
                $methodSchema = $link->schema;
                continue;
            }

            if (strtolower($link->method) === $method) {
                $methodSchema = $link->schema;
                break;
            }
        }

        return $methodSchema;
    }
}
