<?php

declare(strict_types=1);

namespace Eseath\PayKeeper\Services;

use DateTime;
use Eseath\PayKeeper\Entities\ListedPayment;
use Eseath\PayKeeper\Entities\Payment;
use Eseath\PayKeeper\Enums\PaymentStatuses;
use Eseath\PayKeeper\PayKeeperClient;
use GuzzleHttp\Psr7\Query;
use stdClass;

class Payments
{
    public function __construct(public readonly PayKeeperClient $client)
    {}

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/platezhi/#2.3
     */
    public function getById(string $id)
    {
        $data = $this->client->request('GET', '/info/payments/byid/', [
            'id' => $id,
            'advanced' => 'true',
        ]);

        return Payment::createFrom((array) $data[0]);
    }

    /**
     * @link https://docs.paykeeper.ru/dokumentatsiya-json-api/platezhi/#2.3
     *
     * @param  int[]  $paymentSystemIds
     * @param  PaymentStatuses[]  $statuses
     * @return ListedPayment[]
     */
    public function getList(
        DateTime $start,
        DateTime $end,
        array $statuses = [],
        array $paymentSystemIds = [],
        int $limit = 100,
        int $from = 0,
        ?string $query = null,
    ): array
    {
        $statuses = array_map(static fn (PaymentStatuses $case) => $case->name, $statuses);

        $queryParams = Query::build([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'limit' => $limit,
            'from' => $from,
            'payment_system_id[]' => $paymentSystemIds,
            'status[]' => $statuses,
            'query' => $query,
        ]);

        $items = $this->client->request('GET', '/info/payments/bydate/', $queryParams);

        return array_map(static fn (stdClass $item) => ListedPayment::createFrom((array) $item), $items);
    }
}
