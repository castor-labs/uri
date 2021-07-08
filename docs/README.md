# Introduction

Castor Uri provides the `Castor\Net\Uri` value object. This is an immutable object
with a simple api that represents an RFC 3986 compliant Uniform Resource Identifier.

It also provides a helper `Castor\Net\Uri\Query` object, useful for parsing a query
string and read/mutate its values. It is a perfect replacement for php `parse_str`
function.

## Parsing a URI

You can easily parse a URI calling the `Castor\Net\Uri::parse()` method and then
access the parts of the URI very easily.

```php
<?php

use Castor\Net\Uri;

$uri = Uri::parse('https://example.com');
echo $uri->getScheme();         // Prints "https"
echo $uri->getHost();           // Prints "example.com";
echo $uri->getPort();           // Prints "";
echo $uri->getDefaultPort();    // Prints "80";
echo $uri->getPath();           // Prints "";
echo $uri->getQuery();          // Prints "";
echo $uri->getFragment();       // Prints "";
echo $uri;                      // Prints "https://example.com";
```

By design, parts that are not present in the URI are returned as an empty string.
This is to ensure type consistency and better practices, as often more than
a `NULL` check is necessary to ensure correctness of a program.

### A note on invalid URIs

An invalid URI is any URI containing invalid characters and/or lacking a scheme. If you
pass an invalid URI to the `Castor\Net\Uri::parse` method, a `Castor\Net\InvalidUri` error
will be thrown explaining why the URI is invalid. You are advised to properly handle the
exception.

If you want to avoid the exception handling, or if you simply need to check whether a
string is a valid URI, you can use the `Castor\Net\Uri::isValid` static method.

## Mutating the Uri

The `Castor\Net\Uri` class provides a `with` api that can mutate every component
of the URI. This mutation returns a new reference, so the original reference is
preserved. Keep this in mind when working with this class. This, for example, changes
the path of the URI. Note how the original reference is preserved.

```php
<?php

use Castor\Net\Uri;

$uri = Uri::parse('https://example.com');
echo $uri->withPath('/')->toStr();  // Prints "https://example.com/login"
echo $uri->toStr();                 // Prints "https://example.com"
```

## Parsing Query Parameters

The `Castor\Net\Uri::getQuery()` method returns a string with the query parameters,
without the preceding question mark (`?`) character. This string can be fed directly
to the `Castor\Net\Uri\Query::parse()` method to get a `Castor\Net\Uri\Query` object
that allows you to easily read and mutate a query string.

```php
<?php

use Castor\Net\Uri;

$uri = Uri::parse('https://example.com?foo=bar&bar=foo');
$query = Uri\Query::parse($uri->getQuery());
$query->get('foo');
$query->add('bar2', '');
$query->del('foo');
$query->put('bar', 'foo2');
```

You must note that, unlike the `Castor\Net\Uri` object, the `Castor\Net\Uri\Query` object
is mutable, because it's sole purpose of existence is to help build query strings. It
provides a good api to change it's state.

### A note on multiple parameters with the same name

One very important detail about this query parser is that it supports multiple parameters
with the same name. If you have worked with the PHP `$_GET` global, you'll know that it
contains an associative array. This means that if you get a query string `bar=foo&bar=bar`,
you'll lose the first `bar` value (`foo`) and your `$_GET` global will contain
only `['bar' => 'bar']` as an entry.

The `Castor\Net\Uri\Query::get()` method returns an array, so you can handle those cases
and don't lose information. So, for the `bar=foo&bar=bar` example above you'll get:

```php

use Castor\Net\Uri;

$query = Uri\Query::parse('bar=foo&bar=bar');
echo $query->get('bar')[0];     // Prints "foo"
echo $query->get('bar')[1];     // Prints "bar"
```

If you know in advance that you'll be processing only one query parameter, then you can 
go straight into the first index of the parameter array and use the null coalesce operator
to handle defaults.

```php

use Castor\Net\Uri;

$query = Uri\Query::parse('bar=foo&bar=bar');
echo $query->get('page')[0] ?? null;     // Prints "" because is null
```
