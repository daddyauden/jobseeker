<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\JwtBearerInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\Jwt;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\EncryptionInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\RequestInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseInterface;

class JwtBearer implements GrantTypeInterface
{

    private $jwt;
    protected $storage;
    protected $audience;
    protected $jwtUtil;

    public function __construct(JwtBearerInterface $storage, $audience, EncryptionInterface $jwtUtil = null)
    {
        $this->storage = $storage;
        $this->audience = $audience;

        if (is_null($jwtUtil)) {
            $jwtUtil = new Jwt();
        }

        $this->jwtUtil = $jwtUtil;
    }

    public function getQuerystringIdentifier()
    {
        return 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        if (!$request->request("assertion")) {
            $response->setError(400, 'invalid_request', 'Missing parameters: "assertion" required');

            return null;
        }

        $undecodedJWT = $request->request('assertion');

        $jwt = $this->jwtUtil->decode($request->request('assertion'), null, false);

        if (!$jwt) {
            $response->setError(400, 'invalid_request', "JWT is malformed");

            return null;
        }

        $jwt = array_merge(array(
            'scope' => null,
            'iss' => null,
            'sub' => null,
            'aud' => null,
            'exp' => null,
            'nbf' => null,
            'iat' => null,
            'jti' => null,
            'typ' => null,
                ), $jwt);

        if (!isset($jwt['iss'])) {
            $response->setError(400, 'invalid_grant', "Invalid issuer (iss) provided");

            return null;
        }

        if (!isset($jwt['sub'])) {
            $response->setError(400, 'invalid_grant', "Invalid subject (sub) provided");

            return null;
        }

        if (!isset($jwt['exp'])) {
            $response->setError(400, 'invalid_grant', "Expiration (exp) time must be present");

            return null;
        }

        if (ctype_digit($jwt['exp'])) {
            if ($jwt['exp'] <= time()) {
                $response->setError(400, 'invalid_grant', "JWT has expired");

                return null;
            }
        } else {
            $response->setError(400, 'invalid_grant', "Expiration (exp) time must be a unix time stamp");

            return null;
        }

        if ($notBefore = $jwt['nbf']) {
            if (ctype_digit($notBefore)) {
                if ($notBefore > time()) {
                    $response->setError(400, 'invalid_grant', "JWT cannot be used before the Not Before (nbf) time");

                    return null;
                }
            } else {
                $response->setError(400, 'invalid_grant', "Not Before (nbf) time must be a unix time stamp");

                return null;
            }
        }

        if (!isset($jwt['aud']) || ($jwt['aud'] != $this->audience)) {
            $response->setError(400, 'invalid_grant', "Invalid audience (aud)");

            return null;
        }

        if (isset($jwt['jti'])) {
            $jti = $this->storage->getJti($jwt['iss'], $jwt['sub'], $jwt['aud'], $jwt['exp'], $jwt['jti']);

            if ($jti && $jti['expires'] > time()) {
                $response->setError(400, 'invalid_grant', "JSON Token Identifier (jti) has already been used");

                return null;
            } else {
                $this->storage->setJti($jwt['iss'], $jwt['sub'], $jwt['aud'], $jwt['exp'], $jwt['jti']);
            }
        }

        if (!$key = $this->storage->getClientKey($jwt['iss'], $jwt['sub'])) {
            $response->setError(400, 'invalid_grant', "Invalid issuer (iss) or subject (sub) provided");

            return null;
        }

        if (!$this->jwtUtil->decode($undecodedJWT, $key, true)) {
            $response->setError(400, 'invalid_grant', "JWT failed signature verification");

            return null;
        }

        $this->jwt = $jwt;

        return true;
    }

    public function getClientId()
    {
        return $this->jwt['iss'];
    }

    public function getUserId()
    {
        return $this->jwt['sub'];
    }

    public function getScope()
    {
        return null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        $includeRefreshToken = false;

        return $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
    }

}
