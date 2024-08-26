<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Util\Currencies;
use DateTime;

class ExchangeRatesController extends AbstractController
{
    private const API_URL = 'https://api.nbp.pl/api/exchangerates';

    public const ERR_MSGS = [
        'NO_DATA' => 'No data available for the requested date',
        'FETCHING_ERROR' => 'Error fetching exchange rate',
        'UNSUPPORTED_CURRENCY' => 'Unsupported currency',
    ];

    private $httpClient;
    private $params;
    private $currencies;

    // Constructor to inject HttpClientInterface, ParameterBagInterface, and Currencies
    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params, Currencies $currencies)
    {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->currencies = $currencies;
    }

    public function showAll(string $date = null): JsonResponse
    {
        if ($date === null || $date === 'today') {
            $date = (new DateTime())->format('Y-m-d');
        }

        $apiUrl = sprintf('%s/tables/A/%s/?format=json', self::API_URL, $date);
        $response = $this->callAPI($apiUrl);

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);

        $processedRates = array_map(function ($rate) {
            $currencyCode = $rate['code'];
            $mid = $rate['mid'];

            if ($this->currencies->isBasic($currencyCode)) {
                $buy = $mid - $this->params->get('exchange_rates.std_buy_margin');
                $sell = $mid + $this->params->get('exchange_rates.std_sell_margin');
            } else {
                $buy = null;
                $sell = $mid + $this->params->get('exchange_rates.ext_sell_margin');
            }

            return [
                'currency' => $rate['currency'],
                'code' => $currencyCode,
                'mid' => $mid,
                'buy' => $buy,
                'sell' => $sell,
            ];
        }, array_filter($data[0]['rates'], function ($rate) {
            return $this->currencies->isSupported($rate['code']);
        }));

        $filteredData = [
            'table' => $data[0]['table'],
            'no' => $data[0]['no'],
            'effectiveDate' => $data[0]['effectiveDate'],
            'rates' => array_values($processedRates),
        ];

        return new JsonResponse($filteredData);
    }

    public function showOne(string $currency, string $date = null): JsonResponse
    {
        if (!$this->currencies->isSupported(strtoupper($currency))) {
            return new JsonResponse(['error' => self::ERR_MSGS['UNSUPPORTED_CURRENCY']], 400);
        }

        $response = $this->showAll($date);

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);
        $filteredRate = array_filter($data['rates'], function ($rate) use ($currency) {
            return $rate['code'] === strtoupper($currency);
        });

        if (empty($filteredRate)) {
            return new JsonResponse(['error' => self::ERR_MSGS['NO_DATA']], 404);
        }

        $filteredRate = array_values($filteredRate)[0];

        return new JsonResponse($filteredRate);
    }

    private function callAPI(string $apiUrl): JsonResponse
    {
        try {
            $response = $this->httpClient->request('GET', $apiUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $errorContent = $response->getContent(false);

                if ($statusCode === 404 && strpos($errorContent, 'Brak danych') !== false) {
                    return new JsonResponse(['error' => self::ERR_MSGS['NO_DATA']], $statusCode);
                }

                return new JsonResponse(['error' => self::ERR_MSGS['FETCHING_ERROR']], $statusCode);
            }

            $data = $response->toArray();
            return new JsonResponse($data);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => self::ERR_MSGS['FETCHING_ERROR'] . '\n' . $e->getMessage()], 500);
        }
    }
}
