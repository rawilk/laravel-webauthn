<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Models;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rawilk\Webauthn\Casts\Base64;
use Rawilk\Webauthn\Casts\TrustPath;
use Rawilk\Webauthn\Casts\Uuid;
use Rawilk\Webauthn\Contracts\WebauthnKey as WebauthnKeyContract;
use Rawilk\Webauthn\Exceptions\WrongUserHandleException;
use Symfony\Component\Uid\NilUuid;
use Webauthn\PublicKeyCredentialSource;

/**
 * Rawilk\Webauthn\Models\WebauthnKey
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string|null $attachment_type
 * @property int $counter
 * @property array $transports
 * @property string $credential_id
 * @property string $credential_public_key
 * @property \Symfony\Component\Uid\AbstractUid|null $aaguid
 * @property string $attestation_type
 * @property \Webauthn\TrustPath\TrustPath $trust_path
 * @property \Webauthn\PublicKeyCredentialSource $public_key_credential_source
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rawilk\Webauthn\Models\WebauthnKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rawilk\Webauthn\Models\WebauthnKey query()
 * @mixin \Eloquent
 */
class WebauthnKey extends Model implements WebauthnKeyContract
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $visible = [
        'id',
        'name',
        'type',
        'attachment_type',
        'transports',
        'last_used_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'aaguid' => Uuid::class,
        'counter' => 'integer',
        'credential_id' => Base64::class,
        'credential_public_key' => Base64::class,
        'transports' => 'array',
        'trust_path' => TrustPath::class,
        'last_used_at' => 'immutable_datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('webauthn.database.table');
    }

    public function getPublicKeyCredentialSourceAttribute(): PublicKeyCredentialSource
    {
        return new PublicKeyCredentialSource(
            $this->credential_id,
            $this->type,
            $this->transports,
            $this->attestation_type,
            $this->trust_path,
            $this->aaguid ?? new NilUuid,
            $this->credential_public_key,
            (string) $this->user_id,
            $this->counter,
        );
    }

    public function setPublicKeyCredentialSourceAttribute(PublicKeyCredentialSource $source): void
    {
        if ((string) $this->user_id !== $source->getUserHandle()) {
            throw new WrongUserHandleException;
        }

        $this->credential_id = $source->getPublicKeyCredentialId();
        $this->type = $source->getType();
        $this->transports = $source->getTransports();
        $this->attestation_type = $source->getAttestationType();
        $this->trust_path = $source->getTrustPath();
        $this->aaguid = $source->getAaguid();
        $this->credential_public_key = $source->getCredentialPublicKey();
        $this->counter = $source->getCounter();
    }

    public static function fromPublicKeyCredentialSource(
        PublicKeyCredentialSource $publicKeyCredentialSource,
        User $user,
        string $keyName,
        ?string $attachmentType = null,
    ): self {
        return tap(new static([
            'user_id' => $user->getAuthIdentifier(),
            'name' => $keyName,
            'attachment_type' => $attachmentType,
        ]), function (self $webauthnKey) use ($publicKeyCredentialSource) {
            $webauthnKey->public_key_credential_source = $publicKeyCredentialSource;

            $webauthnKey->save();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function createdAtHtml(string $timezone = 'UTC'): string
    {
        $date = $this->created_at?->clone()->tz($timezone);

        return <<<HTML
        <time datetime="{$date?->toDateTimeString()}">{$date?->format('M d Y, g:i a')}</time>
        HTML;
    }

    public function lastUsedAtHtml(string $timezone = 'UTC'): string
    {
        $date = $this->last_used_at?->clone()->tz($timezone);

        if (! $date) {
            return __('webauthn::labels.webauthn_key_never_used');
        }

        return <<<HTML
        <time datetime="{$date->toDateTimeString()}">{$date->format('M d Y, g:i a')}</time>
        HTML;
    }
}
