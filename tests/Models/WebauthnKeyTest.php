<?php

use Rawilk\Webauthn\Models\WebauthnKey;
use Rawilk\Webauthn\Tests\Support\Enums\SampleUuid;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;
use Webauthn\PublicKeyCredentialSource;

it('fills data from a public key credential source', function () {
    $sampleData = samplePublicKeyCredentialSourceData();

    $webauthnKey = new WebauthnKey;
    $webauthnKey->user_id = 0;
    $webauthnKey->public_key_credential_source = new PublicKeyCredentialSource(
        $sampleData['public_key_credential_id'],
        $sampleData['type'],
        $sampleData['transports'],
        $sampleData['attestation_type'],
        $sampleData['trust_path'],
        $sampleData['aaguid'],
        $sampleData['credential_public_key'],
        $sampleData['user_handle'],
        $sampleData['counter'],
    );

    expect($webauthnKey->user_id)->toBe(0);
    expect($webauthnKey->credential_id)->toBe($sampleData['public_key_credential_id']);
    expect($webauthnKey->type)->toBe($sampleData['type']);
    expect($webauthnKey->transports)->toBe($sampleData['transports']);
    expect($webauthnKey->aaguid)->toBe($sampleData['aaguid']);
    expect($webauthnKey->credential_public_key)->toBe($sampleData['credential_public_key']);
    expect($webauthnKey->counter)->toBe($sampleData['counter']);
    expect($webauthnKey->attestation_type)->toBe($sampleData['attestation_type']);
    expect($webauthnKey->trust_path)->toBeInstanceOf($sampleData['trust_path']::class);
});

it('can create a public key credential source from stored attributes', function () {
    $sampleData = samplePublicKeyCredentialSourceData();

    $webauthnKey = new WebauthnKey;
    $webauthnKey->user_id = 0;
    $webauthnKey->credential_id = $sampleData['public_key_credential_id'];
    $webauthnKey->type = $sampleData['type'];
    $webauthnKey->transports = $sampleData['transports'];
    $webauthnKey->aaguid = $sampleData['aaguid'];
    $webauthnKey->credential_public_key = $sampleData['credential_public_key'];
    $webauthnKey->counter = $sampleData['counter'];
    $webauthnKey->attestation_type = $sampleData['attestation_type'];
    $webauthnKey->trust_path = $sampleData['trust_path'];

    $publicKeyCredentialSource = $webauthnKey->public_key_credential_source;

    expect($publicKeyCredentialSource->getPublicKeyCredentialId())->toBe($sampleData['public_key_credential_id']);
    expect($publicKeyCredentialSource->getType())->toBe($sampleData['type']);
    expect($publicKeyCredentialSource->getTransports())->toBe($sampleData['transports']);
    expect($publicKeyCredentialSource->getAaguid())->toBe($sampleData['aaguid']);
    expect($publicKeyCredentialSource->getCredentialPublicKey())->toBe($sampleData['credential_public_key']);
    expect($publicKeyCredentialSource->getUserHandle())->toBe($sampleData['user_handle']);
    expect($publicKeyCredentialSource->getCounter())->toBe($sampleData['counter']);
    expect($publicKeyCredentialSource->getAttestationType())->toBe($sampleData['attestation_type']);
    expect($publicKeyCredentialSource->getTrustPath())->toBeInstanceOf($sampleData['trust_path']::class);
});

it('can handle a null aaguid value', function () {
    $webauthnKey = new WebauthnKey;
    $webauthnKey->aaguid = null;

    expect($webauthnKey->getAttributeValue('aaguid'))->toBeNull();
    expect($webauthnKey->aaguid)->toBeNull();
});

it('can handle an empty string for aaguid value', function () {
    $webauthnKey = new WebauthnKey;
    $webauthnKey->aaguid = '';

    expect($webauthnKey->getAttributeValue('aaguid'))->toBeNull();
    expect($webauthnKey->aaguid)->toBeNull();
});

it('casts a uuid for aaguid', function () {
    $webauthnKey = new WebauthnKey;
    $webauthnKey->aaguid = SampleUuid::AAGUID->value;

    expect($webauthnKey->getAttributeValue('aaguid'))->toEqual(SampleUuid::AAGUID->value);
    expect($webauthnKey->aaguid)->toBeInstanceOf(AbstractUid::class);
    expect($webauthnKey->aaguid)->toEqual(Uuid::fromString(SampleUuid::AAGUID->value));
});
