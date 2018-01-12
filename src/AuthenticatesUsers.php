<?php

namespace MrTimofey\LaravelSimpleTokens;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;
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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->guard()->getProvider();
    }

    public function user(): JsonResponse
    {
        return new JsonResponse($this->guard()->user());
    }

    /**
     * @param Request $req
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function authenticate(Request $req): JsonResponse
    {
        $provider = $this->provider();
        /** @var Authenticatable $model */
        $model = $provider->createModel();
        $rememberName = $model->getRememberTokenName();
        if ($req->has($rememberName)) {
            $user = $provider->retrieveByCredentials($req->only([$rememberName]));
        } else {
            $credentials = $req->only(['login', 'email', 'password']);
            $user = $provider->retrieveByCredentials($credentials);
            if ($user && !$provider->validateCredentials($user, $credentials)) {
                $user = null;
            }
        }
        if (!$user) {
            throw new BadRequestHttpException('Bad credentials');
        }
        $provider->updateRememberToken($user, str_random(100));
        return new JsonResponse([
            'user' => $user,
            'api_token' => $provider->issueToken($user),
            $rememberName => $req->get('remember', false) ? $user->getRememberToken() : null
        ]);
    }

    public function logout(): void
    {
        if ($token = $this->guard()->getTokenForRequest()) {
            $this->provider()->forget($token);
        }
    }
}