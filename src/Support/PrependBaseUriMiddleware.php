<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Support;

use Closure;
use GuzzleHttp\Psr7\Uri;
use JOOservices\Client\Contracts\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class PrependBaseUriMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $baseUri,
    ) {}

    public function __invoke(RequestInterface $request, array $options, Closure $next): ResponseInterface
    {
        if ($request->getUri()->getScheme() !== '') {
            return $next($request, $options);
        }

        $path = ltrim((string) $request->getUri(), '/');
        $request = $request->withUri(new Uri(rtrim($this->baseUri, '/').'/'.$path));

        return $next($request, $options);
    }
}
