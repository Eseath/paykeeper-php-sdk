<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Services;

use DateTime;
use Eseath\PayKeeper\Entities\Invoice;
use Eseath\PayKeeper\Entities\ListedPayment;
use Eseath\PayKeeper\Enums\InvoiceStatuses;
use Eseath\PayKeeper\Exceptions\InvoiceNotFoundException;
use Eseath\PayKeeper\PayKeeperClient;
use GuzzleHttp\Psr7\Query;
use stdClass;

class Invoices
{
    public function __construct(public readonly PayKeeperClient $client)
    {}

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/scheta/#3.2
     *
     * @param InvoiceStatuses[] $statuses
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

        $queryParams = Query::build([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'limit' => $limit,
            'from' => $from,
            'status[]' => $statuses,
        ]);

        $items = $this->client->request('GET', '/info/invoice/list/', $queryParams);

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
}
