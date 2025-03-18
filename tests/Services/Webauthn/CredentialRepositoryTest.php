<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rawilk\Webauthn\Models\WebauthnKey;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

use function Pest\Laravel\actingAs;

uses(DatabaseTransactions::class);

it('gets a public key credential source from a credential id', function () {
    $user = user();
    actingAs($user);
    $webauthnKey = WebauthnKey::factory()->for($user)->create();

    $publicKey = $this->app[PublicKeyCredentialSourceRepository::class]
        ->findOneByCredentialId($webauthnKey->credential_id);

    expect($publicKey)->not()->toBeNull();
});

it('can handle non existing credentials', function () {
    $user = user();
    actingAs($user);

    $publicKey = $this->app[PublicKeyCredentialSourceRepository::class]
        ->findOneByCredentialId('does-not-exist');

    expect($publicKey)->toBeNull();
});

it('does not return a public key credential source for the wrong user', function () {
    $user = user();
    $user2 = user();
    actingAs($user);

    $webauthnKey = WebauthnKey::factory()->for($user2)->create();

    $publicKey = $this->app[PublicKeyCredentialSourceRepository::class]
        ->findOneByCredentialId($webauthnKey->credential_id);

    expect($publicKey)->toBeNull();
});

it('can find every credential registered to a user', function () {
    $user = user();
    actingAs($user);

    WebauthnKey::factory()->for($user)->count(2)->create();
    WebauthnKey::factory()->for(user())->create();

    $publicKeys = $this->app[PublicKeyCredentialSourceRepository::class]
        ->findAllForUserEntity(new PublicKeyCredentialUserEntity('name', $user->getAuthIdentifier(), 'name'));

    expect($publicKeys)->not()->toBeNull();
    expect($publicKeys)->toHaveCount(2);
});

it('can save a credential source', function () {
    $user = user();
    actingAs($user);

    $webauthnKey = WebauthnKey::factory()->for($user)->create();

    $source = $webauthnKey->public_key_credential_source;
    $source->setCounter(154);

    $this->app[PublicKeyCredentialSourceRepository::class]
        ->saveCredentialSource($source);

    $this->assertDatabaseHas('webauthn_keys', [
        'user_id' => $user->getAuthIdentifier(),
        'counter' => '154',
    ]);
});
