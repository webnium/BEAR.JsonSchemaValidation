BEAR.JsonSchmaValidation
========================
[![Build Status](https://travis-ci.org/webnium/BEAR.JsonSchemaValidation.png?branch=master)](https://travis-ci.org/webnium/BEAR.JsonSchemaValidation)

Overview
--------
Validation with json schema for BEAR Resource 

Usage
-----
Install `Webnium\BEAR\JsonSchmaValidation\ValidationModule` in your AppModule.
Then this validator intercepts ResourceObject methods annotated with `@Validate`.

Validation process is below:

1. Retrieve schema definition specifed via `$ro->links['describedBy']`.
2. If schema does not have `link` property or has empty link property, do nothing.
3. Search an element of `link` property under constraint that `rel` property is  "self" and `method` property is called REST method(eg. `GET` when invocated method is `onGet`).
4. If found a link element, validate invocation arguments with `schema` property of it.
5. If not found, search a link element unser constraint that `rel` property is "self" and without `method` property.
6. If found that, validate invocation arguments with `schema` property of it.
7. If not found again, do nothing.

License
-------
This library is destributed under BSD-3-Clause license.
See LICENSE file for more infomation.
