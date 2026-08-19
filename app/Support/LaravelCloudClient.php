<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class LaravelCloudClient
{
    public function enabled(): bool
    {
        return (bool) config('services.laravel_cloud.domain_sync', true)
            && $this->token() !== null
            && $this->environmentId() !== null;
    }

    public function syncRequested(): bool
    {
        return (bool) config('services.laravel_cloud.domain_sync', true);
    }

    public function environmentId(): ?string
    {
        $environmentId = trim((string) config('services.laravel_cloud.environment_id', ''));

        return $environmentId === '' ? null : $environmentId;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function createDomain(string $name, array $options = []): array
    {
        $environmentId = $this->environmentId();

        if ($environmentId === null) {
            throw new LaravelCloudApiException('Laravel Cloud environment is not configured.');
        }

        return $this->send(fn (PendingRequest $request) => $request->post(
            '/environments/'.rawurlencode($environmentId).'/domains',
            $this->domainPayload($name, $options),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function getDomain(string $domainId, bool $verify = false): array
    {
        return $this->send(fn (PendingRequest $request) => $request->get(
            '/domains/'.rawurlencode($domainId),
            $verify ? ['verify' => true] : [],
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyDomain(string $domainId): array
    {
        return $this->send(fn (PendingRequest $request) => $request->post(
            '/domains/'.rawurlencode($domainId).'/verify',
        ));
    }

    public function deleteDomain(string $domainId): void
    {
        $this->send(fn (PendingRequest $request) => $request->delete(
            '/domains/'.rawurlencode($domainId),
        ));
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function domainPayload(string $name, array $options): array
    {
        $defaults = config('services.laravel_cloud.domain_defaults', []);

        return array_filter([
            'name' => $name,
            'www_redirect' => $options['www_redirect'] ?? $defaults['www_redirect'] ?? null,
            'wildcard_enabled' => $options['wildcard_enabled'] ?? $defaults['wildcard_enabled'] ?? false,
            'allow_downtime' => $options['allow_downtime'] ?? $defaults['allow_downtime'] ?? true,
            'cloudflare_strategy' => $options['cloudflare_strategy'] ?? $defaults['cloudflare_strategy'] ?? 'none',
            'verification_method' => $options['verification_method'] ?? $defaults['verification_method'] ?? 'real_time',
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  callable(PendingRequest): mixed  $callback
     * @return array<string, mixed>
     */
    private function send(callable $callback): array
    {
        $token = $this->token();

        if ($token === null) {
            throw new LaravelCloudApiException('Laravel Cloud API token is not configured.');
        }

        try {
            $response = $callback($this->request($token))->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $message = $response ? $this->errorMessage($response->json()) : null;
            $message ??= $exception->getMessage();

            throw new LaravelCloudApiException((string) $message, $exception->getCode(), $exception);
        } catch (Throwable $exception) {
            throw new LaravelCloudApiException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    private function errorMessage(mixed $json): ?string
    {
        if (! is_array($json)) {
            return null;
        }

        $message = $json['message'] ?? null;

        if (is_string($message) && $message !== '') {
            return $message;
        }

        $errors = $json['errors'] ?? null;

        if (! is_array($errors)) {
            return null;
        }

        return collect($errors)
            ->map(function (mixed $error): ?string {
                if (! is_array($error)) {
                    return null;
                }

                return $error['detail'] ?? $error['title'] ?? null;
            })
            ->filter(fn (mixed $error): bool => is_string($error) && $error !== '')
            ->implode(' ');
    }

    private function request(string $token): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.laravel_cloud.base_url', 'https://cloud.laravel.com/api'), '/'))
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function token(): ?string
    {
        $token = trim((string) config('services.laravel_cloud.token', ''));

        return $token === '' ? null : $token;
    }
}
