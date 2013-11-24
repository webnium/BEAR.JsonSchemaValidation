BEAR.JsonSchmaValidation
========================

Validation with json schema for BEAR Resource 

Usage
-----
Install `Webnium\BEAR\JsonSchmaValidation\ValidationModule` in your AppModule.
Then this validator intercepts ResourceObject methods annotated with `@Validate`.

Validation process is below:

+ Retrieve schema definition specifed via `$ro->links['describedBy']`.
+ If schema does not have `link` property or has empty link property, do nothing.
+ Search an element of `link` property under constraint that `rel` property is  "self" and `method` property is called REST method(eg. `GET` when invocated method is `onGet`).
A
+ If found a link element, validate invocation arguments with `schema` property of it.
+ If not found, search a link element unser constraint that `rel` property is "self" and without `method` property.
+ If found that, validate invocation arguments with `schema` property of it.
+ If not found again, do nothing.

License
-------
This library is destributed under BSD-3-Clause license.
See LICENSE file for more infomation.
