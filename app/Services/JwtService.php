<?php
namespace App\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\Clock\SystemClock;

class JwtService
{
    private $config;

    public function __construct()
    {
        // Use the secret key directly
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('5f2b68fbb917b9a82b8e8cf056aa0e3a1e94b9a9e31d1f04a1e97e6b80f08adf')
        );
    }

    public function createToken(array $claims): string
    {
        $builder = $this->config->builder()
            ->issuedAt(now()->toDateTimeImmutable())
            ->expiresAt(now()->addDay()->toDateTimeImmutable());

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

            if ($parsedToken instanceof \Lcobucci\JWT\UnencryptedToken) {
                $clock = new SystemClock(new \DateTimeZone('UTC'));

                $constraints = [
                    new \Lcobucci\JWT\Validation\Constraint\SignedWith(
                        $this->config->signer(),
                        $this->config->signingKey()
                    ),
                    new LooseValidAt($clock)
                ];

                if ($this->config->validator()->validate($parsedToken, ...$constraints)) {
                    return $parsedToken->claims()->all();
                }
            }
        } catch (\Exception $e) {
            return null; // Invalid token or failed validation
        }

        return null;
    }
}
