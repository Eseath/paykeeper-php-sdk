<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Services;

use DateTime;
use Eseath\PayKeeper\Entities\Invoice;
use Eseath\PayKeeper\Entities\InvoiceStatusCounter;
use Eseath\PayKeeper\Enums\InvoiceStatuses;
use Eseath\PayKeeper\Exceptions\InvoiceNotFoundException;
use Eseath\PayKeeper\PayKeeperClient;
use Eseath\PayKeeper\Responses\InvoiceListcountResponse;
use stdClass;

class Invoices
{
    public function __construct(public readonly PayKeeperClient $client)
    {}

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/scheta/#3.2
     *
     * @param  InvoiceStatuses[]  $statuses
     * @return Invoice[]
     */
    public function getList(
        DateTime $startDate,
        DateTime $endDate,
        ?array $statuses,
        int $limit = 100,
        int $from = 0
    ): array
    {
        $statuses = array_map(static fn (InvoiceStatuses $case) => $case->name, $statuses);

        $items = $this->client->request('GET', '/info/invoice/list/', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'limit' => $limit,
            'from' => $from,
            'status[]' => $statuses,
        ]);

        return array_map(static fn (stdClass $item) => Invoice::createFrom((array) $item), $items);
    }

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/scheta/#3.4
     */
    public function getById(string $id): ?Invoice
    {
        try {
            $item = $this->client->request('GET', '/info/invoice/byid/', ['id' => $id]);
        } catch (InvoiceNotFoundException) {
            return null;
        }

        return Invoice::createFrom((array) $item);
    }

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/scheta/#3.3
     *
     * @param InvoiceStatuses[] $statuses
     */
    public function getQuantity(DateTime $startDate, DateTime $endDate, array $statuses): InvoiceListcountResponse
    {
        $statuses = array_map(static fn (InvoiceStatuses $case) => $case->name, $statuses);

        $data = $this->client->request('GET', '/info/invoice/listcount/', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status[]' => $statuses,
        ]);

        return new InvoiceListcountResponse(
            statuses: array_map(static fn ($status) => new InvoiceStatusCounter(
                name: $status->status,
                quantity: (int) $status->count,
            ), $data->statuses),
            total: (int) $data->fullcount[0]->count,
        );
    }
}
