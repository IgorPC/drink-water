<?php

namespace App\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class TokenController
{
    public function generateToken($email)
    {
        $signer = new Sha256();
        $now   = time();
        $builder = new Builder();
        $key = new Key('process');
        $token = $builder
            ->identifiedBy('4f1g23a12aa')
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now + 60)
            ->expiresAt($now + 3600)
            ->withClaim('email', $email)
            ->getToken($signer, $key);

        return (string) $token;
    }

    public function verifyToken($token)
    {
        $parts = explode('.', $token);
        $header = base64_decode($parts[0]);
        $header = json_decode($header);
        $payload = base64_decode($parts[1]);
        $payload = json_decode($payload);
        $header = (array) $header;
        $payload = (array) $payload;
        if(array_key_exists("email", $payload) == false || array_key_exists("exp", $payload) == false){
            return false;
        }elseif (array_key_exists("typ", $header) == false || array_key_exists("alg", $header) == false){
            return false;
        }
        $signer = new Sha256();
        $key = new Key('process');
        $builder = new Builder();
        $newToken = $builder->identifiedBy($payload["jti"])
                ->issuedAt($payload["iat"])
                ->canOnlyBeUsedAfter($payload["nbf"])
                ->expiresAt($payload["exp"])
                ->withClaim('email', $payload["email"])
                ->getToken($signer, $key);

        if($newToken->isExpired() == true){
            return false;
        }else{
            return true;
        }
    }

    public function getClaim($token)
    {
        $parts = explode('.', $token);
        $payload = $parts[1];
        $payload = base64_decode($payload);
        $payload = json_decode($payload);
        $payload = (array) $payload;
        return $payload['email'];
    }

    public function verifyHeader($header)
    {
        $response = null;
        if(array_key_exists("HTTP_AUTHORIZATION", $header) == false){
            $data = ['data' => ['status_code' => 403, 'Message' => 'You do not have a token!']];
            $response = new JsonResponse($data, 403, ['Content-Type' => ['application/hal+json']]);
        }elseif ($header['HTTP_AUTHORIZATION'] == ''){
            $data = ['data' => ['status_code' => 403, 'Message' => 'Token not found!']];
            $response = new JsonResponse($data, 403, ['Content-Type' => ['application/hal+json']]);
        }else{
            $token = explode(' ', $header['HTTP_AUTHORIZATION']);
            if($token[0] != 'Bearer'){
                $data = ['data' => ['status_code' => 400, 'Message' => 'Directive Bearer not found!']];
                $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
            }elseif (array_key_exists(1, $token) == false){
                $data = ['data' => ['status_code' => 403, 'Message' => 'Token not found!']];
                $response = new JsonResponse($data, 403, ['Content-Type' => ['application/hal+json']]);
            }elseif($token[1] == ''){
                $data = ['data' => ['status_code' => 400, 'Message' => 'Token field is required!']];
                $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
            }else{
                $verified = $this->verifyToken($token[1]);
                if($verified == false){
                    $data = ['data' => ['status_code' => 401, 'Message' => 'Invalid Token!']];
                    $response = new JsonResponse($data, 401, ['Content-Type' => ['application/hal+json']]);
                }
            }
        }
        return $response;
    }
}