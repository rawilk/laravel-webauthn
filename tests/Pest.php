<?php

use CBOR\MapItem;
use CBOR\MapObject;
use CBOR\TextStringObject;
use Rawilk\Webauthn\Tests\Support\Enums\SampleUuid;
use Rawilk\Webauthn\Tests\TestCase;
use Symfony\Component\Uid\Uuid;
use Webauthn\TrustPath\EmptyTrustPath;

uses(TestCase::class)->in(
    __DIR__ . '/Models',
    __DIR__ . '/Services',
    __DIR__ . '/Unit',
);

// Helpers
function user(): Rawilk\Webauthn\Tests\Support\Models\User
{
    return \Rawilk\Webauthn\Tests\Support\Models\User::factory()->create();
}

function samplePublicKeyCredentialSourceData(): array
{
    return [
        'public_key_credential_id' => 'a',
        'type' => 'b',
        'transports' => [],
        'attestation_type' => 'c',
        'trust_path' => new EmptyTrustPath,
        'aaguid' => Uuid::fromString(SampleUuid::AAGUID->value),
        'credential_public_key' => 'e',
        'user_handle' => '0',
        'counter' => 1,
    ];
}

function webauthnKeyCredentialPublicKey(): string
{
    return (string) new MapObject([
        new MapItem(
            new TextStringObject('1'),
            new TextStringObject('0')
        ),
        new MapItem(
            new TextStringObject('3'),
            new TextStringObject('-7')
        ),
    ]);
}
