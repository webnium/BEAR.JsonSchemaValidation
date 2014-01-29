<?php
/**
 * This file is part of the Webnium BEAR.JsonSchemaValidation package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace Webnium\BEAR\JsonSchemaValidation;

use Ray\Di\AbstractModule;

/**
 * Webnium BEAR.JsonSchemaValidation validator module
 */
class ValidationModule extends AbstractModule
{
    /**
     * Configure aspect binding
     *
     */
    protected function configure()
    {
        $validator = $this->requestInjection(__NAMESPACE__ . '\Interceptor\JsonSchemaValidator');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('BEAR\Resource\ResourceObject'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotaion\Validate'),
            [$validator]
        );
    }
}
