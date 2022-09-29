<?php

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Factory as CacheFactoryContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use function Pest\Laravel\actingAs;
use Rawilk\Webauthn\Actions\PrepareAssertionData;
use Rawilk\Webauthn\Actions\PrepareKeyCreationData;
use Rawilk\Webauthn\Models\WebauthnKey;
use Rawilk\Webauthn\Services\Webauthn\CredentialAttestationValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;

uses(RefreshDatabase::class);

it('can create a public key for registering a new key', function () {
    $user = user();

    /** @var \Webauthn\PublicKeyCredentialCreationOptions $publicKey */
    $publicKey = $this->app[PrepareKeyCreationData::class]($user);

    expect($publicKey)->toBeInstanceOf(PublicKeyCredentialCreationOptions::class);

    expect($publicKey->getChallenge())->not()->toBeNull();
    expect(strlen($publicKey->getChallenge()))->toBe(32);

    expect($publicKey->getUser())->toBeInstanceOf(PublicKeyCredentialUserEntity::class);
    expect($publicKey->getUser()->getId())->toEqual($user->getAuthIdentifier());
    expect($publicKey->getUser()->getDisplayName())->toBe($user->email);
});

it('can create a public key for asserting a security key is registered to a given user', function () {
    $user = user();

    $webauthnKey = WebauthnKey::factory()->for($user)->create();

    /** @var \Webauthn\PublicKeyCredentialRequestOptions $publicKey */
    $publicKey = $this->app[PrepareAssertionData::class]($user);

    expect($publicKey)->toBeInstanceOf(PublicKeyCredentialRequestOptions::class);

    expect($publicKey->getChallenge())->not()->toBeNull();
    expect(strlen($publicKey->getChallenge()))->toBe(32);

    expect($publicKey->getUserVerification())->toBe('preferred');
    expect($publicKey->getRpId())->toBe('localhost');
    expect($publicKey->getTimeout())->toBe(60000);
    expect($publicKey->getExtensions())->toHaveCount(0);

    $firstCredential = Arr::first($publicKey->getAllowCredentials());

    expect($firstCredential->getType())->toBe('public-key');
    expect($firstCredential->getId())->toBe($webauthnKey->credential_id);
});

it('always returns a new attestation challenge', function () {
    $user = user();
    actingAs($user);

    $first = $this->app[PrepareKeyCreationData::class]($user);
    $second = $this->app[PrepareKeyCreationData::class]($user);

    expect($first->getChallenge())->not()->toBe($second->getChallenge());
});

test('userless forces a preferred resident key', function () {
    config(['webauthn.userless' => 'preferred']);

    $user = user();
    actingAs($user);

    $attestation = $this->app[PrepareKeyCreationData::class]($user);

    expect($attestation->getAuthenticatorSelection()->getResidentKey())->toBe('preferred');
});

test('attestation validation fails if no attestation exists', function () {
    $cache = $this->mock(Repository::class);
    $cache->shouldReceive('pull')->andReturnNull();

    $this->mock(CacheFactoryContract::class)
        ->shouldReceive('store')
        ->with(null)
        ->andReturn($cache);

    $this->mock(PublicKeyCredentialLoader::class)
        ->shouldNotReceive('loadArray');

    $this->mock(AuthenticatorAttestationResponseValidator::class)
        ->shouldNotReceive('check');

    $credential = $this->app[CredentialAttestationValidator::class](user(), ['foo' => 'bar']);

    expect($credential)->toBeNull();
});
