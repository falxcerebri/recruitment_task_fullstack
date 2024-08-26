import React, { useEffect, useState } from 'react';
import { useParams, useHistory } from 'react-router-dom';
import DatePicker from './DatePicker';
import Loading from './Loading';
import ExchangeRateTable from './ExchangeRateTable';
import CurrentRateTable from './CurrentRateTable';

const ExchangeRates = () => {
    const { currency, currencyOrDate, date } = useParams();
    const history = useHistory();
    const [rates, setRates] = useState(null);
    const [currentRates, setCurrentRates] = useState(null);
    const [loading, setLoading] = useState(true);
    const [currentRatesLoading, setCurrentRatesLoading] = useState(true);
    const [selectedDate, setSelectedDate] = useState("");
    const todayDate = new Date().toISOString().split("T")[0]

    useEffect(() => {
        let apiUrl;

        // Determine the initial date
        let initialDate = date || currencyOrDate;
        if (!initialDate) {
            initialDate = todayDate;
            setSelectedDate(todayDate);
        } else {
            setSelectedDate(initialDate);
        }

        // Determine the API URL based on the parameters
        if (!currency && !currencyOrDate && !date) {
            // No params: Fetch all exchange rates for the current day
            apiUrl = `/api/exchange-rates`;
        } else if (currency && date) {
            // Both currency and date provided: Fetch exchange rate for the particular currency on the particular date
            apiUrl = `/api/exchange-rates/${currency}/${date}`;
        } else if (currencyOrDate) {
            // One parameter provided: it could be either currency or date
            apiUrl = `/api/exchange-rates/${currencyOrDate}`;
        }

        // Fetch data for selected date
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                setRates(data.rates || data); // Adjust based on how your API returns data
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching the rates:', error);
                setLoading(false);
            });

        // Fetch data for current date if a specific currency and date are selected
        if (currency && date) {
            setCurrentRatesLoading(true); // Set current rates loading to true before fetching
            fetch(`/api/exchange-rates/${currency}/${todayDate}`)
                .then(response => response.json())
                .then(data => {
                    setCurrentRates(data.rates || data);
                    setCurrentRatesLoading(false); // Set to false once data is loaded
                })
                .catch(error => {
                    console.error('Error fetching the current rates:', error);
                    setCurrentRatesLoading(false); // Ensure we stop loading even if there's an error
                });
        }
    }, [currency, currencyOrDate, date, todayDate]); // Re-run effect when any of these params change

    const handleDateChange = (newDate) => {
        // Update the URL with the new date
        if (currency) {
            history.push(`/exchange-rates/${currency}/${newDate}`);
        } else {
            history.push(`/exchange-rates/${newDate}`);
        }
    };

    return (
        <div>
            <section className="row-section">
                <div className="container">
                    <div className="row mt-5">
                        <div className="col-md-8 offset-md-2">
                            <h2 className="text-center"><span>Kursy Wymiany Walut</span></h2>
                            {loading ? (
                                <Loading />
                            ) : (
                                <div className="container">
                                    <DatePicker initialDate={selectedDate} onDateChange={handleDateChange} />
                                    <ExchangeRateTable rates={rates} selectedDate={selectedDate} />
                                    {currency && date && !currentRatesLoading && currentRates && (
                                        <CurrentRateTable currentRates={currentRates} todayDate={todayDate} />
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
}

export default ExchangeRates;
