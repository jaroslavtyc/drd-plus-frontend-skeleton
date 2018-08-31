<?php
declare(strict_types=1);

namespace DrdPlus\Tests\FrontendSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\Request;
use Granam\Tests\Tools\TestWithMockery;

class RequestTest extends TestWithMockery
{
    public static function getCrawlerUserAgents(): array
    {
        return [
            'Mozilla/5.0 (compatible; SeznamBot/3.2; +http://napoveda.seznam.cz/en/seznambot-intro/)',
            'User-Agent: Mozilla/5.0 (compatible; SeznamBot/3.2-test4; +http://napoveda.seznam.cz/en/seznambot-intro/)',
            'Googlebot'
        ];
    }

    public static function getNonCrawlerUserAgents(): array
    {
        return [
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0', // Firefox
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36' // Chrome
        ];
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_detect_czech_seznam_bot(): void
    {
        $request = new Request(new Bot());
        foreach (static::getCrawlerUserAgents() as $crawlerUserAgent) {
            self::assertTrue(
                $request->isVisitorBot($crawlerUserAgent),
                'Directly passed crawler has not been recognized: ' . $crawlerUserAgent
            );
            $_SERVER['HTTP_USER_AGENT'] = $crawlerUserAgent;
            self::assertTrue(
                $request->isVisitorBot(),
                'Crawler has not been recognized from HTTP_USER_AGENT: ' . $crawlerUserAgent
            );
        }
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_do_not_get_non_bot_browsers_marked_as_bots(): void
    {
        $request = new Request(new Bot());
        foreach (static::getNonCrawlerUserAgents() as $nonCrawlerUserAgent) {
            self::assertFalse(
                $request->isVisitorBot($nonCrawlerUserAgent),
                'Directly passed browser has been wrongly marked as a bot: ' . $nonCrawlerUserAgent
            );
            $_SERVER['HTTP_USER_AGENT'] = $nonCrawlerUserAgent;
            self::assertFalse(
                $request->isVisitorBot(),
                'Browser has been wrongly marked as a bot from HTTP_USER_AGENT: ' . $nonCrawlerUserAgent
            );
        }
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_get_current_url_even_if_query_string_is_not_set(): void
    {
        $request = new Request(new Bot());
        unset($_SERVER['QUERY_STRING']);
        self::assertSame('', $request->getCurrentUrl());
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_get_current_url_with_updated_query_parameters(): void
    {
        $request = new Request(new Bot());
        $_GET = ['foo' => 123, 'bar' => 456];
        self::assertSame('?foo=0&bar=456&baz=OK', $request->getCurrentUrl(['foo' => false, 'baz' => 'OK']));
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_get_current_url_with_updated_query_parameters_even_if_get_is_not_set(): void
    {
        $request = new Request(new Bot());
        unset($_GET);
        $currentUrl = $request->getCurrentUrl(['foo' => true]);
        global $_GET; // because backup globals do not works for unset global
        $_GET = [];
        self::assertSame('?foo=1', $currentUrl);
    }

    /**
     * @test
     * @backupGlobals enabled
     * @dataProvider provideRequestUri
     * @param null|string $requestUri
     * @param string $expectedCurrentUri
     */
    public function I_can_get_current_request_uri(?string $requestUri, string $expectedCurrentUri): void
    {
        $request = new Request(new Bot());
        if ($requestUri === null) {
            unset($_SERVER['REQUEST_URI']);
        } else {
            $_SERVER['REQUEST_URI'] = $requestUri;
        }
        self::assertSame($expectedCurrentUri, $request->getCurrentBaseUrl());
    }

    public function provideRequestUri(): array
    {
        return [
            [null, ''],
            ['/', '/'],
            ['/update/web', '/update/web'],
        ];
    }
}