<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Rawilk\Webauthn\Models\WebauthnKey;
use Symfony\Component\Uid\Uuid;
use Webauthn\TrustPath\EmptyTrustPath;

class WebauthnKeyFactory extends Factory
{
    protected $model = WebauthnKey::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'counter' => 0,
            'type' => 'public-key',
            'transports' => [],
            'attestation_type' => 'none',
            'trust_path' => new EmptyTrustPath,
            'aaguid' => Uuid::fromString($this->faker->uuid()),
            'credential_public_key' => 'oWNrZXlldmFsdWU=',
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (WebauthnKey $webauthnKey) {
            $webauthnKey->credential_id = Base64UrlSafe::encode((string) $webauthnKey->user_id);
        });
    }
}
