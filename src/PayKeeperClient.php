<?php

declare(strict_types=1);

namespace Eseath\PayKeeper;

use Eseath\PayKeeper\Entities\PaymentSystem;
use Eseath\PayKeeper\Exceptions\InvoiceNotFoundException;
use Eseath\PayKeeper\Services\Invoices;
use Eseath\PayKeeper\Services\Payments;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\RequestOptions;
use RuntimeException;
use stdClass;

class PayKeeperClient
{
    protected string $hostname = 'demo.paykeeper.ru';

    protected string $username = 'demo';

    protected string $password = 'demo';

    protected Client $transport;

    public readonly Invoices $invoices;

    public readonly Payments $payments;

    public function __construct(
        string $username,
        string $password,
        string $hostname,
        bool $testMode = false
    )
    {
        if (! $testMode) {
            $this->username = $username;
            $this->password = $password;
            $this->hostname = $hostname;
        }

        $this->transport = new Client([
            'base_uri' => "https://$this->hostname",
        ]);

        $this->invoices = new Invoices($this);
        $this->payments = new Payments($this);
    }

    /**
     * @param array<string,string|int|float|array> $queryParams
     * @param array<string,string|int|float|array> $data
     */
    public function request(
        string $method,
        string $uri,
        array $queryParams = [],
        array $data = [],
    ): stdClass | array
    {
        $options = [
            RequestOptions::HEADERS => [
                'Authorization' => 'Basic ' . base64_encode("$this->username:$this->password"),
            ],
            RequestOptions::QUERY => Query::build($queryParams, treatBoolsAsInts: false),
        ];

        if ($method === 'POST' || $method === 'PUT') {
            $options[RequestOptions::FORM_PARAMS] = [
                'token' => $this->getAccessToken(),
                ...$data,
            ];
        }

        $response = $this->transport->request($method, $uri, $options);
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode === 200 && empty($body)) {
            throw new RuntimeException("Empty response body (http status: $statusCode)");
        }

        $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

        if (isset($data->result) && $data->result === 'fail') {
            match ($data->msg) {
                'Счёт не найден.' => throw new InvoiceNotFoundException($data->msg),
                default => throw new RuntimeException($data->msg),
            };
        }

        return $data;
    }

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/token-bezopasnosti/
     */
    public function getAccessToken(): string
    {
        $data = $this->request('GET', '/info/settings/token/');

        return $data->token;
    }

    public function getPaymentLink(
        float $amount,
        ?string $serviceName = null,
        ?string $orderId = null,
        ?string $clientId = null,
        ?string $clientEmail = null,
        ?string $clientPhone = null,
        ?string $expiry = null,
        ?string $userResultCallback = null,
    ): InvoicePreviewResponse
    {
        if ($userResultCallback) {
            $serviceName = json_encode([
                'service_name' => $serviceName,
                'user_result_callback' => $userResultCallback,
            ], JSON_THROW_ON_ERROR);
        }

        $data = [
            'token' => $this->getAccessToken(),
            'pay_amount' => $amount,
            'clientid' => $clientId,
            'orderid' => $orderId,
            'service_name' => $serviceName,
            'client_email' => $clientEmail,
            'client_phone' => $clientPhone,
            'expiry' => $expiry,
        ];

        $data = $this->request('POST', '/change/invoice/preview/', data: $data);

        return new InvoicePreviewResponse(
            invoice_id: $data->invoice_id,
            invoice_url: $data->invoice_url,
            invoice: $data->invoice,
        );
    }

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/statistika/#1.1
     */
    public function getPaymentSystems(): array
    {
        $items = $this->request('GET', '/info/systems/list/');

        return array_map(static fn (stdClass $item) => new PaymentSystem(
            id: $item->id,
            system_description: $item->system_description,
            site_description: $item->site_description,
        ), $items);
    }

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/statistika/#1.2
     */
    public function getPaymentsAcceptedAmount(string $startDate, string $endDate): array
    {
        $items = $this->request('GET', '/info/systems/sums/', [
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return array_map(static fn (stdClass $item) => (object) [
            'paymentSystem' => new PaymentSystem(
                id: $item->id,
                system_description: $item->system_description,
                site_description: $item->site_description,
            ),
            'success' => (float) $item->success,
            'account' => (float) $item->account,
            'stuck' => (float) $item->stuck,
            'currency' => $item->currency,
        ], $items);
    }
}
