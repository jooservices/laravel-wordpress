<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Feature;

use Jooservices\LaravelWordPress\DTOs\Credentials\CredentialCreateData;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\AuthType;
use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Models\User;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class LocalCrudTest extends TestCase
{
    public function test_site_credentials_and_local_users_can_be_managed(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Local', 'https://example.com'));
        $credential = WordPress::credentials()->createForSite($site, new CredentialCreateData(
            name: 'Default',
            authType: AuthType::ApplicationPassword,
            username: 'admin',
            secret: 'secret',
        ));

        $user = WordPress::site($site)->users()->createLocal([
            'username' => 'ada',
            'email' => 'ada@example.com',
            'name' => 'Ada Lovelace',
        ]);

        self::assertSame('wp_sites', $site->getTable());
        self::assertSame('secret', $credential->secret);
        self::assertInstanceOf(User::class, $user);
        self::assertSame('ada@example.com', $user->email);
    }
}
