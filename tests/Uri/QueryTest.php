<?php

declare(strict_types=1);

/**
 * @project Castor Uri
 * @link https://github.com/castor-labs/uri
 * @package castor/uri
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license MIT
 * @copyright 2021 CastorLabs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Castor\Net\Uri;

use PHPUnit\Framework\TestCase;

/**
 * Class QueryTest.
 *
 * @internal
 * @coversNothing
 */
class QueryTest extends TestCase
{
    public function testItParsesQueryStringWithKeyAndValue(): void
    {
        $query = Query::parse('foo=bar&bar=foo');
        self::assertSame('bar', $query->get('foo')[0]);
        self::assertSame('foo', $query->get('bar')[0]);
    }

    public function testItParsesQueryStringWithMultipleKeysAndValues(): void
    {
        $query = Query::parse('foo=bar&foo=foo');
        self::assertSame('bar', $query->get('foo')[0]);
        self::assertSame('foo', $query->get('foo')[1]);
    }

    public function testItParsesStringWithNoValue(): void
    {
        $query = Query::parse('foo&foo=bar');
        self::assertSame('', $query->get('foo')[0]);
        self::assertSame('bar', $query->get('foo')[1]);
    }

    public function testItCorrectlyCastsToString(): void
    {
        $query = Query::create();
        $query->put('foo', '', 'bar');
        self::assertSame('foo&foo=bar', (string) $query);
    }

    public function testItCreatesFromAssociativeArray(): void
    {
        $query = Query::make([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);
        self::assertSame('foo=bar&bar=foo', $query->toStr());
    }

    public function testItIgnoresUriWithEmptyKey(): void
    {
        $query = Query::make([
            '' => 'bar',
            'bar' => 'foo',
        ]);
        self::assertSame('bar=foo', $query->toStr());
    }
}
