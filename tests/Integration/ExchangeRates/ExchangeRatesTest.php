<?php

namespace Integration\ExchangeRates;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use App\Controller\ExchangeRatesController;
use App\Controller\Currencies;

class ExchangeRagesTest extends WebTestCase
{
    public function testShowOneWithValidCurrencyAndDate(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/exchange-rates/EUR/2024-08-02');
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        // Check if the necessary keys exist in the response data
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('mid', $responseData);
        $this->assertArrayHasKey('buy', $responseData);
        $this->assertArrayHasKey('sell', $responseData);
    }

    public function testShowOneWithUnsupportedCurrency(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/exchange-rates/XYZ/2024-08-02');
        $this->assertResponseStatusCodeSame(400);
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        // Verify the error message
        $this->assertEquals(ExchangeRatesController::ERR_MSGS['UNSUPPORTED_CURRENCY'], $responseData['error']);
    }

    public function testShowAllWithValidDate(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/exchange-rates/2024-08-02');
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        // Assuming the API returns an array of rates, we check if it's present
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
    }

    public function testShowOneWithWeekendDate(): void
    {
        $client = static::createClient();

        // Choose a Saturday or Sunday date (e.g., 2024-08-03 is a Saturday)
        $client->request('GET', '/api/exchange-rates/EUR/2024-08-03');

        // Check that the response indicates that rates are not available
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(404);
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        // Assuming the API returns a specific message for weekends
        $this->assertEquals(ExchangeRatesController::ERR_MSGS['NO_DATA'], $responseData['error']);
    }

    public function testShowAllWithWeekendDate(): void
    {
        $client = static::createClient();

        // Test a request for a Saturday or Sunday date
        $client->request('GET', '/api/exchange-rates/2024-08-03');

        // Check that the response indicates that rates are not available
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(404);
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        // Verify the error message or appropriate response
        $this->assertEquals(ExchangeRatesController::ERR_MSGS['NO_DATA'], $responseData['error']);
    }

    public function testShowAllOnlyReturnsSupportedCurrencies(): void
    {
        $client = static::createClient();

        // Perform the request to a known date where we have control over the response
        $client->request('GET', '/api/exchange-rates/2024-08-01');

        // Assert response status code is successful
        $this->assertResponseIsSuccessful();

        // Get the response content and decode it to an array
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        // Define the expected supported currencies
        $expectedCurrencies = Currencies::SUPPORTED;

        // Extract the returned currencies from the response
        $returnedCurrencies = array_column($responseData['rates'], 'code');

        // Check that all returned currencies are supported
        foreach ($returnedCurrencies as $currency) {
            $this->assertContains($currency, $expectedCurrencies, "Currency {$currency} is not supported.");
        }

        // Check that all supported currencies are in the response
        foreach ($expectedCurrencies as $currency) {
            $this->assertContains($currency, $returnedCurrencies, "Supported currency {$currency} is missing in the response.");
        }

        // Check that the number of returned currencies matches the number of supported currencies
        $this->assertCount(count($expectedCurrencies), $returnedCurrencies);
    }
}
