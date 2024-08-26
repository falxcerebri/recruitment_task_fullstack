<?php

declare(strict_types=1);

namespace App\Util;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
    private $supportedCurrencies;
    private $basicCurrencies;

    public function __construct(ParameterBagInterface $params)
    {
        $this->supportedCurrencies = $params->get('currencies.supported');
        $this->basicCurrencies = $params->get('currencies.basic');
    }

    /**
     * isSupported Checks if the provided currency is supported in this configuration
     *
     * @param  string $currency 3 letter currency code, would be best if used from Currencies constants, e.g. Currencies::EUR
     * @return bool if supplied currency is supported
     */
    public function isSupported(string $currency): bool
    {
        return in_array($currency, $this->supportedCurrencies, true);
    }

    /**
     * isBasic Checks how the currency should be treated and exchange rate calculated for it
     *
     * @param  string $currency 3 letter currency code, would be best if used from Currencies constants, e.g. Currencies::EUR
     * @return bool if supplied currency is supported and is within BASIC catalog
     */
    public function isBasic(string $currency): bool
    {
        return $this->isSupported($currency) && in_array($currency, $this->basicCurrencies, true);
    }

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    public function getBasicCurrencies(): array
    {
        return $this->basicCurrencies;
    }
}
