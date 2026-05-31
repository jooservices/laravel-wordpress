<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services;

use JOOservices\Client\Client\ClientBuilder;
use Jooservices\LaravelWordPress\Enums\AuthType;
use Jooservices\LaravelWordPress\Exceptions\RemoteNotConfiguredException;
use Jooservices\LaravelWordPress\Models\Credential;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Support\PrependBaseUriMiddleware;
use JOOservices\WordPress\Sdk\Auth\BasicAuthenticator;
use JOOservices\WordPress\Sdk\Configs\AuthConfig;
use JOOservices\WordPress\Sdk\Configs\HttpConfig;
use JOOservices\WordPress\Sdk\Configs\SdkConfig;
use JOOservices\WordPress\Sdk\Http\ErrorMapper;
use JOOservices\WordPress\Sdk\Http\Middleware\AuthenticationMiddleware;
use JOOservices\WordPress\Sdk\Http\RequestBuilder;
use JOOservices\WordPress\Sdk\Http\ResponseDecoder;
use JOOservices\WordPress\Sdk\WordPressService as SdkWordPressService;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class RemoteClientFactory
{
    public function make(Site $site, ?Credential $credential = null): RemoteClient
    {
        $credential ??= $site->credentials()->where('is_default', true)->first();

        if (! $credential instanceof Credential || $credential->auth_type !== AuthType::ApplicationPassword) {
            throw new RemoteNotConfiguredException('A default application-password credential is required for remote WordPress calls.');
        }

        $auth = new AuthConfig((string) $credential->username, (string) $credential->secret);
        $http = new HttpConfig(
            timeout: (int) config('wordpress.connection.timeout', 15),
            maxRetries: (int) config('wordpress.connection.retries', 1),
        );
        $config = new SdkConfig($site->rest_api_base_url ?: $site->base_url, $auth, $http);
        $authorization = 'Basic '.base64_encode("{$config->auth->username}:{$config->auth->password}");

        // The SDK is still the remote API surface. We build its client with
        // base_uri set on the underlying Guzzle instance so relative endpoint
        // paths resolve correctly with jooservices/client 0.5.x.
        $client = ClientBuilder::create()
            ->withBaseUri($config->baseUrl)
            ->withTimeout($config->http->timeout)
            ->withConnectTimeout($config->http->connectTimeout)
            ->withOption('base_uri', $config->baseUrl)
            ->withOption('auth', [$config->auth->username, $config->auth->password])
            ->withOption('retries', $config->http->maxRetries)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', $authorization)
            ->withMiddleware(new PrependBaseUriMiddleware($config->baseUrl), 'base_uri')
            ->withMiddleware(new AuthenticationMiddleware(new BasicAuthenticator($config->auth)), 'auth')
            ->build();

        $serializer = new Serializer([
            new ArrayDenormalizer,
            new ObjectNormalizer(null, null, null, new ReflectionExtractor),
        ], [new JsonEncoder]);

        return new RemoteClient(new SdkWordPressService(
            $client,
            new RequestBuilder,
            new ResponseDecoder($serializer),
            new ErrorMapper,
        ));
    }
}
