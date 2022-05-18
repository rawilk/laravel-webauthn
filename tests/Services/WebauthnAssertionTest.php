<?php

use Illuminate\Contracts\Cache\Factory as CacheFactoryContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use function Pest\Laravel\actingAs;
use Psr\Http\Message\ServerRequestInterface;
use Rawilk\Webauthn\Actions\PrepareAssertionData;
use Rawilk\Webauthn\Exceptions\ResponseMismatchException;
use Rawilk\Webauthn\Services\Webauthn;
use Rawilk\Webauthn\Tests\Support\Enums\IdEnum;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

uses(RefreshDatabase::class);

it('never generates the same assertion', function () {
    $user = user();
    actingAs($user);

    $first = $this->app[PrepareAssertionData::class]($user);
    $second = $this->app[PrepareAssertionData::class]($user);

    expect($first->getChallenge())->not()->toBe($second->getChallenge());
});

// Let's come back to this...
it('validates and asserts credentials', function () {
    $user = user();

    $request = $this->mock(Request::class);
    $request->shouldReceive('getHost')->andReturn('test_host');
    $request->shouldReceive('ip')->andReturn('127.0.0.1');

    $cache = $this->mock(Repository::class);
    $options = (new PublicKeyCredentialRequestOptions(\random_bytes(16)))
        ->setTimeout(60000)
        ->setRpId('test_id')
        ->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED);
    $cache->shouldReceive('pull')->andReturn($options);

    $this->mock(CacheFactoryContract::class)
        ->shouldReceive('store')
        ->with(null)
        ->andReturn($cache);

    /** @var \Webauthn\AuthenticatorAssertionResponse $response */
    $response = Mockery::mock(AuthenticatorAssertionResponse::class);

    $credential = new PublicKeyCredential(
        'test_credential_id',
        'public-key',
        IdEnum::ASSERTION_RAW_ID->value,
        $response,
    );

    $this->mock(PublicKeyCredentialLoader::class)
        ->shouldReceive('loadArray')
        ->with(['foo' => 'bar'])
        ->andReturn($credential);

    $this->mock(AuthenticatorAssertionResponseValidator::class)
        ->shouldReceive('check')
        ->with(
            IdEnum::ASSERTION_RAW_ID->value,
            $response,
            $options,
            Mockery::type(ServerRequestInterface::class),
            $user->getAuthIdentifier(),
        )
        ->once();

    $result = $this->app[Webauthn::class]->validateAssertion($user, ['foo' => 'bar']);

    expect($result)->toBeInstanceOf(PublicKeyCredentialSource::class);
});

test('assertion fails when the check fails', function () {
    $user = user();

    $request = $this->mock(Request::class);
    $request->shouldReceive('getHost')->andReturn('test_host');
    $request->shouldReceive('ip')->andReturn('127.0.0.1');

    $cache = $this->mock(Repository::class);
    $options = (new PublicKeyCredentialRequestOptions(random_bytes(16)))
        ->setTimeout(60000)
        ->setRpId('test_id')
        ->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED);
    $cache->shouldReceive('pull')->andReturn($options);

    $this->mock(CacheFactoryContract::class)
        ->shouldReceive('store')
        ->with(null)
        ->andReturn($cache);

    /** @var \Webauthn\AuthenticatorAssertionResponse $response */
    $response = Mockery::mock(AuthenticatorAssertionResponse::class);

    $credential = new PublicKeyCredential(
        'test_credential_id',
        'public-key',
        IdEnum::ASSERTION_RAW_ID->value,
        $response,
    );

    $this->mock(PublicKeyCredentialLoader::class)
        ->shouldReceive('loadArray')
        ->with(['foo' => 'bar'])
        ->andReturn($credential);

    $this->mock(AuthenticatorAssertionResponseValidator::class)
        ->shouldReceive('check')
        ->with(
            IdEnum::ASSERTION_RAW_ID->value,
            $response,
            $options,
            Mockery::type(ServerRequestInterface::class),
            $user->getAuthIdentifier(),
        )
        ->once()
        ->andThrow(new InvalidArgumentException);

    $result = $this->app[Webauthn::class]->validateAssertion($user, ['foo' => 'bar']);

    expect($result)->toBeFalse();
});

test('assertion fails if no previous assertion was generated', function () {
    $request = $this->mock(Request::class);
    $request->shouldReceive('getHost')->andReturn('test_host');
    $request->shouldReceive('ip')->andReturn('127.0.0.1');

    $cache = $this->mock(Repository::class);
    $cache->shouldReceive('pull')->andReturn(null);

    $this->mock(CacheFactoryContract::class)
        ->shouldReceive('store')
        ->with(null)
        ->andReturn($cache);

    $this->mock(PublicKeyCredentialLoader::class)
        ->shouldNotReceive('loadArray');

    $this->mock(AuthenticatorAssertionResponseValidator::class)
        ->shouldNotReceive('check');

    $result = $this->app[Webauthn::class]->validateAssertion(user(), ['foo' => 'bar']);

    expect($result)->toBeFalse();
});

test('assertion fails if response is incorrect', function () {
    $request = $this->mock(Request::class);
    $request->shouldReceive('getHost')->andReturn('test_host');
    $request->shouldReceive('ip')->andReturn('127.0.0.1');

    $cache = $this->mock(Repository::class);
    $options = (new PublicKeyCredentialRequestOptions(random_bytes(16)))
        ->setTimeout(60000)
        ->setRpId('test_id')
        ->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED);
    $cache->shouldReceive('pull')->andReturn($options);

    $this->mock(CacheFactoryContract::class)
        ->shouldReceive('store')
        ->with(null)
        ->andReturn($cache);

    $credential = Mockery::mock(PublicKeyCredential::class);
    $credential->shouldReceive('getRawId')->andReturn('test_credential_id');
    $credential->shouldReceive('getResponse')
        ->andReturn(new class extends \Webauthn\AuthenticatorResponse {
            public function __construct()
            {
            }
        });

    $this->mock(PublicKeyCredentialLoader::class)
        ->shouldReceive('loadArray')
        ->with(['foo' => 'bar'])
        ->andReturn($credential);

    $this->mock(AuthenticatorAssertionResponseValidator::class)
        ->shouldNotReceive('check');

    $this->assertThrows(
        fn () => $this->app[Webauthn::class]->validateAssertion(user(), ['foo' => 'bar']),
        ResponseMismatchException::class,
        ResponseMismatchException::assertionMismatched()->getMessage(),
    );
});

test('assertion fails if an exception is thrown', function () {
    $user = user();

    $request = $this->mock(Request::class);
    $request->shouldReceive('getHost')->andReturn('test_host');
    $request->shouldReceive('ip')->andReturn('127.0.0.1');

    $cache = $this->mock(Repository::class);
    $options = (new PublicKeyCredentialRequestOptions(random_bytes(16)))
        ->setTimeout(60000)
        ->setRpId('test_id')
        ->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED);
    $cache->shouldReceive('pull')->andReturn($options);

    $this->mock(CacheFactoryContract::class)
        ->shouldReceive('store')
        ->with(null)
        ->andReturn($cache);

    $response = Mockery::mock(AuthenticatorAssertionResponse::class);

    $credential = new PublicKeyCredential(
        'test_credential_id',
        'public-key',
        IdEnum::ASSERTION_RAW_ID->value,
        $response,
    );

    $this->mock(PublicKeyCredentialLoader::class)
        ->shouldReceive('loadArray')
        ->with(['foo' => 'bar'])
        ->andReturn($credential);

    $this->mock(AuthenticatorAssertionResponseValidator::class)
        ->shouldReceive('check')
        ->with(
            IdEnum::ASSERTION_RAW_ID->value,
            $response,
            $options,
            Mockery::type(ServerRequestInterface::class),
            $user->getAuthIdentifier(),
        )
        ->once()
        ->andThrow(new Exception);

    $this->assertThrows(
        fn () => $this->app[Webauthn::class]->validateAssertion($user, ['foo' => 'bar']),
        Exception::class,
    );
});
