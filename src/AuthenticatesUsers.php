<?php

namespace MrTimofey\LaravelSimpleTokens;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait AuthenticatesUsers
{
    protected function guard(): TokenGuard
    {
        return auth($this->guard ?? 'api');
    }

    protected function provider(): SimpleProvider
    {
        return $this->guard()->getProvider();
    }

    protected function generateToken(): string
    {
        return str_random(60);
    }

    public function user(): JsonResponse
    {
        return new JsonResponse($this->guard()->user());
    }

    public function authenticate(Request $req): JsonResponse
    {
        $provider = $this->provider();
        /** @var Authenticatable $model */
        $model = $provider->createModel();
        $user = $provider->retrieveByCredentials($req->only([
            'login', 'email', 'password', $model->getRememberTokenName()
        ]));
        if (!$user) {
            throw new BadRequestHttpException('Bad credentials');
        }
        $token = $this->generateToken();
        cache()->set(config('simple_tokens.cache_prefix') . $token, $user->getAuthIdentifier(), config('simple_tokens.token_ttl'));
        $provider->updateRememberToken($user, str_random(100));
        return new JsonResponse([
            'user' => $user,
            'api_token' => $token,
            $model->getRememberTokenName() => $req->get('remember', false) ? $user->getRememberToken() : null
        ]);
    }

    public function logout(): void
    {
        if ($token = $this->guard()->getTokenForRequest()) {
            cache()->delete(config('simple_tokens.cache_prefix') . $token);
        }
    }
}