<?php

use Cose\Algorithm\Manager as CoseAlgorithmManager;
use Cose\Algorithm\ManagerFactory as CoseAlgorithmManagerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Rawilk\Webauthn\Models\WebauthnKey;
use Rawilk\Webauthn\Services\Webauthn;
use Symfony\Component\Uid\NilUlid;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\Counter\CounterChecker;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

uses(RefreshDatabase::class);

it('can determine if webauthn is enabled for the application', function () {
    config(['webauthn.enabled' => true]);

    expect($this->app[Webauthn::class]->webauthnIsEnabled())->toBeTrue();

    config(['webauthn.enabled' => false]);

    expect($this->app[Webauthn::class]->webauthnIsEnabled())->toBeFalse();
});

it('can determine if a user has webauthn enabled for their account', function () {
    $user = user();

    expect($this->app[Webauthn::class]->enabledFor($user))->toBeFalse();

    WebauthnKey::factory()->for($user)->create();

    expect($this->app[Webauthn::class]->enabledFor($user))->toBeTrue();
});

it('can determine if a user is allowed to register a new key to their account', function () {
    $user = user();
    config(['webauthn.enabled' => true]);

    expect($this->app[Webauthn::class]->canRegister($user))->toBeTrue();

    config(['webauthn.enabled' => false]);

    expect($this->app[Webauthn::class]->canRegister($user))->toBeFalse();
});

it('creates a new WebauthnKey model', function () {
    $user = user();

    config(['webauthn.database.model' => WebauthnKey::class]);

    $source = new PublicKeyCredentialSource(
        'test',
        'type',
        [],
        'attestationType',
        new EmptyTrustPath,
        new NilUlid,
        'credentialPublicKey',
        $user->getAuthIdentifier(),
        0,
    );

    $webauthnKey = $this->app[Webauthn::class]->create($user, 'name', $source);

    expect($webauthnKey)->toBeInstanceOf(WebauthnKey::class);
});

it('registers container bindings via closure', function (string $expectedBinding) {
    expect($this->app[$expectedBinding])->not->toBeNull($expectedBinding);
})->with([
    PackedAttestationStatementSupport::class,
    AttestationStatementSupportManager::class,
    AttestationObjectLoader::class,
    CounterChecker::class,
    AuthenticatorAttestationResponseValidator::class,
    AuthenticatorAssertionResponseValidator::class,
    AuthenticatorSelectionCriteria::class,
    PublicKeyCredentialRpEntity::class,
    PublicKeyCredentialLoader::class,
    CoseAlgorithmManager::class,
    CoseAlgorithmManagerFactory::class,
]);
