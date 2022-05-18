<?php

use CBOR\ListObject;
use CBOR\MapItem;
use CBOR\MapObject;
use CBOR\TextStringObject;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Rawilk\Webauthn\Tests\Support\Enums\SampleUuid;
use Rawilk\Webauthn\Tests\TestCase;
use Symfony\Component\Uid\Uuid;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\TrustPath\EmptyTrustPath;

uses(TestCase::class)->in(
    __DIR__ . '/Models',
    __DIR__ . '/Services',
);

// Helpers
function user(): \Rawilk\Webauthn\Tests\Support\Models\User
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

function attestationData(PublicKeyCredentialCreationOptions $publicKey): array
{
    return [
        'id' => Base64UrlSafe::encode('0'),
        'rawId' => Base64UrlSafe::encode('0'),
        'type' => 'public-key',
        'response' => [
            'clientDataJSON' => Base64UrlSafe::encode(json_encode([
                'type' => 'webauthn.create',
                'challenge' => Base64UrlSafe::encode($publicKey->getChallenge()),
                'origin' => 'https://localhost',
                'tokenBinding' => [
                    'status' => 'supported',
                    'id' => 'id',
                ],
            ])),
            'attestationObject' => Base64UrlSafe::encode((string) (new MapObject([
                new MapItem(
                    new TextStringObject('authData'),
                    new TextStringObject(
                        hash('sha256', 'localhost', true) . // rp_id_hash
                        pack('C', 65) . // flags
                        pack('N', 1) . // signCount
                        '0000000000000000' . // aaguid
                        pack('n', 1) . '0' .  // credentialLength
                        ((string) new MapObject([
                            new MapItem(
                                new TextStringObject('key'),
                                new TextStringObject('value')
                            ),
                        ])) // credentialPublicKey
                    )
                ),
                new MapItem(new TextStringObject('fmt'), new TextStringObject('none')),
                new MapItem(new TextStringObject('attStmt'), new ListObject([])),
            ]))),
        ],
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
