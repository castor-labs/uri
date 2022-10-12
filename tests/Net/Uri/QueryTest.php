<?php

declare(strict_types=1);

/**
 * @project Castor Uri
 * @link https://github.com/castor-labs/uri
 * @project castor/uri
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license BSD-3-Clause
 * @copyright 2022 Castor Labs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Castor\Net\Uri;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Castor\Net\Uri\Query
 */
class QueryTest extends TestCase
{
    public function testItDecodesSingleParams(): void
    {
        $this->testDecode('foo=bar', [
            'foo' => ['bar'],
        ]);
    }

    public function testItDecodesMultipleParams(): void
    {
        $this->testDecode('foo=bar&foo=foo', [
            'foo' => ['bar', 'foo'],
        ]);
    }

    public function testItDecodesEscaped(): void
    {
        $this->testDecode('q=go+language', [
            'q' => ['go language'],
        ]);
    }

    public function testItDecodesPercent(): void
    {
        $this->testDecode('q=go%20language', [
            'q' => ['go language'],
        ], 'q=go+language');
    }

    public function testItDecodesOnlyKey(): void
    {
        $this->testDecode('q', [
            'q' => [''],
        ], 'q');
    }

    protected function testDecode(string $in, array $items, string $roundTrip = ''): void
    {
        if ('' === $roundTrip) {
            $roundTrip = $in;
        }

        $query = Query::decode($in);

        $this->assertSame($items, $query->toArray());
        $this->assertSame($roundTrip, $query->encode());
    }
}
