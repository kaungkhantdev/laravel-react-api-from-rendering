<?php
namespace App\Services;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;

class JwtService
{
    private $config;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(config('app.jwt_secret'))
        );
    }

    public function createToken(array $claims): string
    {
        $builder = $this->config->builder()
            ->issuedAt(now()->toDateTimeImmutable()) // Time token was issued
            ->expiresAt(now()->addHour()->toDateTimeImmutable());

        foreach ($claims as $key => $value) {
            $builder->withClaim($key, $value);
        }

        $token = $builder->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    public function validateToken(string $token): ?array
    {
        try {
            $parsedToken = $this->config->parser()->parse($token);

            assert($parsedToken instanceof UnencryptedToken);

            if ($this->config->validator()->validate($parsedToken, ...[
                new \Lcobucci\JWT\Validation\Constraint\SignedWith(
                    $this->config->signer(),
                    $this->config->signingKey()
                ),
                new \Lcobucci\JWT\Validation\Constraint\LooseValidAt(new \DateTimeImmutable())
            ])) {
                return $parsedToken->claims()->all();
            }
        } catch (\Exception $e) {
            return null; // Invalid token or failed validation
        }

        return null;
    }
}
