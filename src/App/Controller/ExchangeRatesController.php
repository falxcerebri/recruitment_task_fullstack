<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTime;

/**
 * Currencies
 *
 * Class of currencies codes utility. This class should be used to configure which currencies are supported in the application.
 *
 * To add new currencies, please add new constants:
 *
 *   const EGP = 'EGP';
 *
 * Next to add this currency add it to SUPPORTED array.
 *
 * TODO: SUPPORTED and BASIC should be loaded from configuration yaml files.
 */
class Currencies
{
    const EUR = 'EUR';
    const USD = 'USD';
    const CZK = 'CZK';
    const IDR = 'IDR';
    const BRL = 'BRL';

    const SUPPORTED = [
        self::EUR,
        self::USD,
        self::CZK,
        self::IDR,
        self::BRL,
    ];

    const BASIC = [
        self::EUR,
        self::USD,
    ];

    /**
     * isSupported Checks if the provided currency is supported in this configuration
     *
     * @param  string $currency 3 letter currency code, would be best if used from Currencies constants, e.g. Currencies::EUR
     * @return bool if supplied currency is supported
     */
    public static function isSupported(string $currency): bool
    {
        return in_array($currency, self::SUPPORTED, true);
    }

    /**
     * isBasic Checks how the currency should be treated and exchange rate calculated for it
     *
     * @param  string $currency 3 letter currency code, would be best if used from Currencies constants, e.g. Currencies::EUR
     * @return bool if supplied currency is supported and is within BASIC catalog
     */
    public static function isBasic(string $currency): bool
    {
        return in_array($currency, self::SUPPORTED, true) && in_array($currency, self::BASIC, true);
    }
}

class ExchangeRatesController extends AbstractController
{
    private const API_URL = 'https://api.nbp.pl/api/exchangerates';

    public const ERR_MSGS = [
        'NO_DATA' => 'No data available for the requested date',
        'FETCHING_ERROR' => 'Error fetching exchange rate',
        'UNSUPPORTED_CURRENCY' => 'Unsupported currency',
    ];

    //TODO: these margins should be loaded from configuration files
    private const STD_SELL_MARGIN = 0.07;
    private const STD_BUY_MARGIN = 0.05;

    private const EXT_SELL_MARGIN = 0.15;

    // Declare the HttpClientInterface property
    private $httpClient;

    // Constructor to inject HttpClientInterface
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function showAll(string $date = null): JsonResponse
    {
        // If date is not provided, use the current date as the default
        if ($date === null || $date === 'today') {
            $date = (new DateTime())->format('Y-m-d');
        }

        // Call the external API to get the exchange rate
        $apiUrl = sprintf('%s/tables/A/%s/?format=json', self::API_URL, $date);
        $response = $this->callAPI($apiUrl);

        // If the response has an error, return it as is
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);

        // Filter the rates to include only supported currencies and calculate buy/sell rates
        $processedRates = array_map(function ($rate) {
            $currencyCode = $rate['code'];
            $mid = $rate['mid'];

            // Use the isBasic method to determine if the currency is BASIC
            if (Currencies::isBasic($currencyCode)) {
                $buy = $mid - self::STD_BUY_MARGIN;
                $sell = $mid + self::STD_SELL_MARGIN;
            } else {
                $buy = null;
                $sell = $mid + self::EXT_SELL_MARGIN;
            }

            return [
                'currency' => $rate['currency'],
                'code' => $currencyCode,
                'mid' => $mid,
                'buy' => $buy,
                'sell' => $sell,
            ];
        }, array_filter($data[0]['rates'], function ($rate) {
            return Currencies::isSupported(($rate['code']));
            //NOTE: couldn't change this to `array_filter($data[0]['rates'], ['Currencies', 'isSupported'])`
        }));

        // Prepare the final response structure
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
        if (!Currencies::isSupported(strtoupper($currency))) {
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
                $errorContent = $response->getContent(false); // Get the raw response content without throwing an exception

                // Handle specific case of "404 NotFound - Brak danych"
                if ($statusCode === 404 && strpos($errorContent, 'Brak danych') !== false) {
                    return new JsonResponse(['error' => self::ERR_MSGS['NO_DATA']], $statusCode);
                }

                // Handle generic 404 NotFound or other errors
                return new JsonResponse(['error' => self::ERR_MSGS['FETCHING_ERROR']], $statusCode);
            }

            $data = $response->toArray();  // Convert the response to an array

            // Forward the JSON data received from the external API
            return new JsonResponse($data);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => self::ERR_MSGS['FETCHING_ERROR'] . '\n' . $e->getMessage()], 500);
        }
    }
}
