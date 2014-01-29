<?php
/**
 * This file is part of Webnium BEAR.JsonSchemaValidation
 *
 * URL: https://github.com/webnium/BEAR.JsonSchemaValidation/
 */

namespace Webnium\BEAR\JsonSchemaValidation\Interceptor;

use \PHPUnit_Framework_TestCase as TestCase;
use \Phake;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Link;
use Ray\Aop\MethodInvocation;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator as JsonValidator;
use ReflectionMethod;

/**
 * Basic test case for JsonSchemaValidator
 */
class JsonSchemaValidatorTest extends TestCase
{
    /** @var ResourceObject */
    private $ro;

    /** @var MethodInvocation */
    private $invocation;

    /** @var JsonSchemaValidator */
    private $validator;

    /**
     * setup
     */
    public function setUp()
    {
        $this->ro = new Resource\Mock;

        $this->invocation = Phake::mock('Ray\Aop\MethodInvocation');

        Phake::when($this->invocation)->getThis()->thenReturn($this->ro);
        Phake::when($this->invocation)->proceed()->thenReturn($this->ro);
        Phake::when($this->invocation)->getArguments()->thenReturn([]);
        Phake::when($this->invocation)->getMethod()->thenReturn(new ReflectionMethod('Webnium\BEAR\JsonSchemaValidation\Interceptor\Resource\Mock', 'onPost'));

        $this->validator = new JsonSchemaValidator;
        $this->validator->setRetriever(new UriRetriever);
        $this->validator->setValidator(new JsonValidator);
    }

    /**
     * @test
     */
    public function neverProceedWhenArgumentsAreInvalid()
    {
        $this->validator->invoke($this->invocation);

        Phake::verify($this->invocation, Phake::never())->proceed();
    }

    /**
     * @test
     */
    public function returnedObjectHasErrorsAttributeInBodyPropertyWhenArgumentsAreInvalid()
    {
        $ro = $this->validator->invoke($this->invocation);

        $this->assertArrayHasKey('errors', $ro->body, 'should have "errors" in body');
        $this->assertContains(['property' => 'name', 'message' => 'is missing and it is required'], $ro->body['errors']);
        $this->assertContains(['property' => 'number', 'message' => 'is missing and it is required'], $ro->body['errors']);
        $this->assertCount(2, $ro->body['errors'], 'should have 2 errors');
    }

    /**
     * @test
     */
    public function returnedObjectHasCodeBadRequestWhenArgumentAreInvalid()
    {
        $ro = $this->validator->invoke($this->invocation);

        $this->assertSame(400, $ro->code);
    }

    /**
     * @test
     */
    public function retiriveSchemaUsingDescribedByRelationOfTheResourceObject()
    {
        $retriever = Phake::mock('JsonSchema\Uri\UriRetriever');
        $this->validator->setRetriever($retriever);

        $this->validator->invoke($this->invocation);

        Phake::verify($retriever)->retrieve($this->ro->links['describedBy'][Link::HREF]);
    }

    /**
     * @test
     */
    public function callProceedWhenArgumentsAreValid()
    {
        Phake::when($this->invocation)->getArguments()->thenReturn([
            'name' => 'efaljfal',
            'option' => 'fugafu',
            'number' => 5
        ]);

        $this->validator->invoke($this->invocation);

        Phake::verify($this->invocation)->proceed();
    }

    /**
     * @test
     */
    public function validateWithSchemaInLinkWithoutMethodWhenSpecifiedMethodSchemaDoesNotExist()
    {
        Phake::when($this->invocation)->getMethod()->thenReturn(new ReflectionMethod('Webnium\BEAR\JsonSchemaValidation\Interceptor\Resource\Mock', 'onGet'));
        Phake::when($this->invocation)->getArguments()->thenReturn([
            'name' => 'felfef'
        ]);

        $this->validator->invoke($this->invocation);

        Phake::verify($this->invocation)->proceed();
    }

    /**
     * @test
     *
     * @expectedException Webnium\BEAR\JsonSchemaValidation\Exception\InvalidBinding
     */
    public function shouldThrowInvalidBindingExceptionWhenInvocatedMethodIsNotAHttpVerbMethod()
    {
        Phake::when($this->invocation)->getMethod()->thenReturn(new ReflectionMethod('Webnium\BEAR\JsonSchemaValidation\Interceptor\Resource\Mock', 'hello'));

        $this->validator->invoke($this->invocation);
    }
}
