<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Integration;

use Illuminate\Support\Facades\Http;
use Jooservices\LaravelWordPress\DTOs\Credentials\CredentialCreateData;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\AuthType;
use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class WordPressIntegrationTest extends TestCase
{
    public function test_real_wordpress_environment_supports_site_credentials_and_remote_user_list(): void
    {
        $baseUrl = getenv('WORDPRESS_TEST_BASE_URL');
        $username = getenv('WORDPRESS_TEST_USERNAME');
        $password = getenv('WORDPRESS_TEST_PASSWORD');

        if ($baseUrl === false || $username === false || $password === false) {
            self::markTestSkipped('Set WORDPRESS_TEST_BASE_URL, WORDPRESS_TEST_USERNAME, and WORDPRESS_TEST_PASSWORD to run real WordPress integration tests.');
        }

        self::assertTrue(Http::get($baseUrl.'/wp-json')->successful());

        $site = WordPress::sites()->create(new SiteCreateData('Docker WordPress', $baseUrl));
        WordPress::credentials()->createForSite($site, new CredentialCreateData(
            name: 'Docker',
            authType: AuthType::ApplicationPassword,
            username: $username,
            secret: $password,
        ));

        $users = WordPress::site($site)->users()->listRemote(['per_page' => 1]);

        self::assertNotEmpty($users);
    }
}
