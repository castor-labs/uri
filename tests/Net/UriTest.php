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

namespace Castor\Net;

use Castor\Net\Uri\ParseError;
use Castor\Net\Uri\Query;
use Castor\Net\Uri\UserInfo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Castor\Net\Uri
 * @covers \Castor\Net\Uri\Query
 * @covers \Castor\Net\Uri\UserInfo
 */
class UriTest extends TestCase
{
    /**
     * @throws ParseError
     */
    public function testItParsesEmptyPath(): void
    {
        $this->testParse('https://example.com', Uri::fromParts(
            scheme: 'https',
            host: 'example.com'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesWithSlashPath(): void
    {
        $this->testParse('https://example.com/', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesPathWithHexEscaping(): void
    {
        $this->testParse('https://example.com/file%20one%26two', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/file one&two',
            rawPath: '/file%20one%26two'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesUser(): void
    {
        $this->testParse('ftp://webmaster@example.com/', Uri::fromParts(
            scheme: 'ftp',
            userinfo: 'webmaster',
            host: 'example.com',
            path: '/',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesEncodedUsername(): void
    {
        $this->testParse('ftp://john%20doe@example.com/', Uri::fromParts(
            scheme: 'ftp',
            userinfo: 'john doe',
            host: 'example.com',
            path: '/',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesEmptyQuery(): void
    {
        $this->testParse('https://example.com/?', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/',
        ), 'https://example.com/');
    }

    /**
     * @throws ParseError
     */
    public function testItParsesQueryEndingInQuestionMark(): void
    {
        $this->testParse('https://example.com/?foo=bar?', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/',
            rawQuery: 'foo=bar?',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesQuery(): void
    {
        $this->testParse('https://example.com/?q=go+language', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/',
            rawQuery: 'q=go+language',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesQueryWithHexEscaping(): void
    {
        $this->testParse('https://example.com/?q=go%20language', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/',
            rawQuery: 'q=go%20language',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesPercentOutsideQuery(): void
    {
        $this->testParse('https://example.com/a%20b?q=c+d', Uri::fromParts(
            scheme: 'https',
            host: 'example.com',
            path: '/a b',
            rawPath: '/a%20b',
            rawQuery: 'q=c+d'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesOpaque(): void
    {
        $this->testParse('https:example.com/?q=go+language', Uri::fromParts(
            scheme: 'https',
            path: 'example.com/',
            rawQuery: 'q=go+language'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesOpaque2(): void
    {
        $this->testParse('https:%2f%2fexample.com/?q=go+language', Uri::fromParts(
            scheme: 'https',
            path: '//example.com/',
            rawPath: '%2f%2fexample.com/',
            rawQuery: 'q=go+language'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesNoAuthorityWithPath(): void
    {
        $this->testParse('mailto:/webmaster@example.com', Uri::fromParts(
            scheme: 'mailto',
            path: '/webmaster@example.com',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesNonAuthority(): void
    {
        $this->testParse('mailto:webmaster@example.com', Uri::fromParts(
            scheme: 'mailto',
            path: 'webmaster@example.com'
        ));
    }

    /**
     * Unescaped :// in query should not create a scheme.
     *
     * @throws ParseError
     */
    public function testItParsesUnescapeSchemeInQuery(): void
    {
        $this->testParse('/foo?query=http://bad', Uri::fromParts(
            path: '/foo',
            rawQuery: 'query=http://bad'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesLeadingDoubleSlash(): void
    {
        $this->testParse('//foo', Uri::fromParts(
            host: 'foo'
        ));
    }

    /**
     * @throws ParseError
     */
    public function testItParsesLeadingDoubleSlashWithComponents(): void
    {
        $this->testParse('//user@foo/path?a=b', Uri::fromParts(
            userinfo: 'user',
            host: 'foo',
            path: '/path',
            rawQuery: 'a=b'
        ));
    }

    /**
     * Three slashes is not an authority. It's an invalid uri.
     *
     * @throws ParseError
     */
    public function testItDoesNotParseThreeSlashes(): void
    {
        $this->expectException(ParseError::class);
        Uri::parse('///threeslashes');
    }

    /**
     * @throws ParseError
     */
    public function testItParsesUsernameAndPassword(): void
    {
        $this->testParse('https://user:password@example.com', Uri::fromParts(
            scheme: 'https',
            userinfo: 'user:password',
            host: 'example.com',
        ));
    }

    /**
     * Unescaped @ in username should not confuse host.
     *
     * @throws ParseError
     */
    public function testItParsesWithUnescapedAtInUsername(): void
    {
        $this->testParse('https://j@ne:password@example.com', Uri::fromParts(
            scheme: 'https',
            userinfo: 'j@ne:password',
            host: 'example.com',
        ), 'https://j%40ne:password@example.com');
    }

    /**
     * Unescaped @ in password should not confuse host.
     *
     * @throws ParseError
     */
    public function testItParsesWithUnescapedAtInPassword(): void
    {
        $this->testParse('https://jane:p@ssword@example.com', Uri::fromParts(
            scheme: 'https',
            userinfo: 'jane:p@ssword',
            host: 'example.com',
        ), 'https://jane:p%40ssword@example.com');
    }

    /**
     * @throws ParseError
     */
    public function testItParsesUriWithJustScheme(): void
    {
        $this->testParse('https:', Uri::fromParts(
            scheme: 'https',
        ));
    }

    /**
     * @throws ParseError
     */
    public function testAccessorsWorkCorrectly(): void
    {
        $uri = Uri::parse('mysql://user:pass@localhost:3306/database?version=5.7#master');

        $this->assertSame('mysql', $uri->getScheme());
        $this->assertSame('user:pass@localhost:3306', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserinfo()->toString());
        $this->assertSame('user', $uri->getUserinfo()->getUser());
        $this->assertSame('pass', $uri->getUserinfo()->getPass());
        $this->assertSame('localhost:3306', $uri->getHost());
        $this->assertSame('localhost', $uri->getHostname());
        $this->assertSame('3306', $uri->getPort());
        $this->assertSame(3306, $uri->getPortNumber());
        $this->assertSame('/database', $uri->getPath());
        $this->assertSame('version=5.7', $uri->getRawQuery());
        $this->assertSame('master', $uri->getFragment());
        $this->assertEquals(Query::decode('version=5.7'), $uri->getQuery());
    }

    public function testMutationsWorkCorrectly(): void
    {
        $uri = Uri::parse('')
            ->withScheme('mysql')
            ->withUserinfo(UserInfo::parse('user:pass'))
            ->withHost('localhost:3306')
            ->withPath('/database')
            ->withQuery(Query::create()->add('version', '5.7'))
            ->withFragment('master')
        ;

        $this->assertSame('mysql://user:pass@localhost:3306/database?version=5.7#master', $uri->toString());
    }

    /**
     * @throws ParseError
     */
    public function testItDeterminesOpaqueAndAbsolute(): void
    {
        $a = Uri::parse('//example.com/path');
        $b = Uri::parse('https://example.com/path');
        $c = Uri::parse('mailto:user@example.com');
        $d = Uri::parse('hello');

        $this->assertFalse($a->isAbsolute());
        $this->assertFalse($a->isOpaque());

        $this->assertTrue($b->isAbsolute());
        $this->assertFalse($b->isOpaque());

        $this->assertTrue($c->isAbsolute());
        $this->assertTrue($c->isOpaque());

        $this->assertFalse($d->isAbsolute());
        $this->assertTrue($d->isOpaque());
    }

    /**
     * @throws ParseError
     */
    protected function testParse(string $in, Uri $expected, string $expectedStr = ''): void
    {
        if ('' === $expectedStr) {
            $expectedStr = $in;
        }

        $uri = Uri::parse($in);

        $this->assertEquals($expected, $uri);
        $this->assertSame($expectedStr, $uri->toString());
    }
}
