<?php

declare(strict_types=1);

namespace Rawilk\Webauthn;

use Cose\Algorithm\Manager as CoseAlgorithmManager;
use Cose\Algorithm\ManagerFactory as CoseAlgorithmManagerFactory;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Rawilk\Webauthn\Contracts\WebauthnKey as WebauthnKeyContract;
use Rawilk\Webauthn\Facades\Webauthn as WebauthnFacade;
use Rawilk\Webauthn\Http\Controllers\AssetsController;
use Rawilk\Webauthn\Models\WebauthnKey;
use Rawilk\Webauthn\Services\Webauthn;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AndroidSafetyNetAttestationStatementSupport;
use Webauthn\AttestationStatement\AppleAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\Counter\CounterChecker;
use Webauthn\Counter\ThrowExceptionIfInvalid;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;
use Webauthn\TokenBinding\TokenBindingHandler;

class WebauthnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-webauthn')
            ->hasConfigFile()
            ->hasMigration('create_webauthn_table')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WebauthnFacade::class, Webauthn::class);

        $this->registerBindings();
    }

    public function packageBooted(): void
    {
        Blade::directive('webauthnScripts', function (string $expression) {
            return "<?php echo (new \Rawilk\Webauthn\Support\WebauthnAssets)->javaScript({$expression}) ?>";
        });

        Route::get('/webauthn/assets/{asset}', [AssetsController::class, 'source']);
    }

    protected function registerBindings(): void
    {
        $this->app->bind(WebauthnKeyContract::class, config('webauthn.database.model', WebauthnKey::class));

        $this->registerWebauthnBindings();
    }

    protected function registerWebauthnBindings(): void
    {
        $this->app->bind(PublicKeyCredentialSourceRepository::class, Webauthn\CredentialRepository::class);
        $this->app->bind(TokenBindingHandler::class, IgnoreTokenBindingHandler::class);

        $this->app->bind(
            PackedAttestationStatementSupport::class,
            fn ($app) => new PackedAttestationStatementSupport(
                $app[CoseAlgorithmManager::class]
            )
        );

        if ($this->app['config']->get('webauthn.google_safetynet_api_key')) {
            $this->app->bind(
                AndroidKeyAttestationStatementSupport::class,
                fn ($app) => (new AndroidSafetyNetAttestationStatementSupport)
                    ->enableApiVerification(
                        $app[ClientInterface::class],
                        $app['config']->get('webauthn.google_safetynet_api_key'),
                        $app[RequestFactoryInterface::class],
                    )
            );
        }

        $this->app->bind(
            AttestationStatementSupportManager::class,
            fn ($app) => tap(new AttestationStatementSupportManager, function ($manager) use ($app) {
                // https://www.w3.org/TR/webauthn/#sctn-none-attestation
                $manager->add($app[NoneAttestationStatementSupport::class]);

                // https://www.w3.org/TR/webauthn/#sctn-fido-u2f-attestation
                $manager->add($app[FidoU2FAttestationStatementSupport::class]);

                // https://www.w3.org/TR/webauthn/#sctn-android-key-attestation
                $manager->add($app[AndroidKeyAttestationStatementSupport::class]);

                // https://www.w3.org/TR/webauthn/#sctn-tpm-attestation
                $manager->add($app[TPMAttestationStatementSupport::class]);

                // https://www.w3.org/TR/webauthn/#sctn-packed-attestation
                $manager->add($app[PackedAttestationStatementSupport::class]);

                // https://www.w3.org/TR/webauthn/#sctn-android-safetynet-attestation
                if ($app['config']->get('webauthn.google_safetynet_api_key') !== null) {
                    $manager->add($app[AndroidSafetyNetAttestationStatementSupport::class]);
                }

                // https://www.w3.org/TR/webauthn/#sctn-apple-anonymous-attestation
                // Note: for some reason, this never makes it through the validation and prevents us from adding
                // a hardware key from an Apple device when attestation conveyance is enabled.
                $manager->add($app[AppleAttestationStatementSupport::class]);
            })
        );

        $this->app->bind(
            AttestationObjectLoader::class,
            function ($app) {
                $attestationObjectLoader = new AttestationObjectLoader($app[AttestationStatementSupportManager::class]);
                $attestationObjectLoader->setLogger($app['log']);

                return $attestationObjectLoader;
            }
        );

        $this->app->bind(
            CounterChecker::class,
            fn ($app) => new ThrowExceptionIfInvalid($app['log'])
        );

        $this->app->bind(
            AuthenticatorAttestationResponseValidator::class,
            function ($app) {
                $authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
                    $app[AttestationStatementSupportManager::class],
                    $app[PublicKeyCredentialSourceRepository::class],
                    $app[TokenBindingHandler::class],
                    $app[ExtensionOutputCheckerHandler::class],
                );
                $authenticatorAttestationResponseValidator->setLogger($app['log']);

                return $authenticatorAttestationResponseValidator;
            }
        );

        $this->app->bind(AuthenticatorAssertionResponseValidator::class, function ($app) {
            $authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator(
                $app[PublicKeyCredentialSourceRepository::class],
                $app[TokenBindingHandler::class],
                $app[ExtensionOutputCheckerHandler::class],
                $app[CoseAlgorithmManager::class],
            );
            $authenticatorAssertionResponseValidator->setCounterChecker($app[CounterChecker::class]);
            $authenticatorAssertionResponseValidator->setLogger($app['log']);

            return $authenticatorAssertionResponseValidator;
        });

        $this->app->bind(
            AuthenticatorSelectionCriteria::class,
            fn ($app) => tap(new AuthenticatorSelectionCriteria, function ($authenticatorSelectionCriteria) use ($app) {
                $authenticatorSelectionCriteria->setAuthenticatorAttachment($app['config']->get('webauthn.attachment_mode', 'null'))
                    ->setUserVerification($app['config']->get('webauthn.user_verification', 'preferred'));

                if (($userless = $app['config']->get('webauthn.userless')) !== null) {
                    $authenticatorSelectionCriteria->setResidentKey($userless);
                }
            })
        );

        $this->app->bind(
            PublicKeyCredentialRpEntity::class,
            fn ($app) => new PublicKeyCredentialRpEntity(
                $app['config']->get('webauthn.relying_party.name') ?? 'Laravel',
                $app['config']->get('webauthn.relying_party.id') ?? $app->make('request')->getHost(),
                $app['config']->get('webauthn.relying_party.icon'),
            )
        );

        $this->app->bind(PublicKeyCredentialLoader::class, function ($app) {
            $publicKeyCredentialLoader = new PublicKeyCredentialLoader($app[AttestationObjectLoader::class]);
            $publicKeyCredentialLoader->setLogger($app['log']);

            return $publicKeyCredentialLoader;
        });

        $this->app->bind(
            CoseAlgorithmManager::class,
            fn ($app) => $app[CoseAlgorithmManagerFactory::class]
                ->generate(...$app['config']->get('webauthn.public_key_credential_parameters'))
        );

        $this->app->bind(
            CoseAlgorithmManagerFactory::class,
            fn () => tap(new CoseAlgorithmManagerFactory, function ($factory) {
                // list of existing algorithms
                $algorithms = [
                    RSA\RS1::class,
                    RSA\RS256::class,
                    RSA\RS384::class,
                    RSA\RS512::class,
                    RSA\PS256::class,
                    RSA\PS384::class,
                    RSA\PS512::class,
                    ECDSA\ES256::class,
                    ECDSA\ES256K::class,
                    ECDSA\ES384::class,
                    ECDSA\ES512::class,
                    EdDSA\Ed256::class,
                    EdDSA\Ed512::class,
                    EdDSA\Ed25519::class,
                    EdDSA\EdDSA::class,
                ];

                foreach ($algorithms as $algorithm) {
                    $factory->add((string) $algorithm::identifier(), new $algorithm);
                }
            })
        );
    }
}
