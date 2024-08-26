import React from 'react';
import ExchangeRateTable from './ExchangeRateTable';
import DatePicker from './DatePicker';

const CurrentRateTable = ({ currentRates, todayDate }) => (
    <div className="current-rates-section mt-5">
        <h4 className="text-center">Aktualnie</h4>
        <DatePicker initialDate={todayDate} disabled /> {/* Disabled DatePicker */}
        <ExchangeRateTable rates={currentRates} selectedDate={todayDate} />
    </div>
);

export default CurrentRateTable;
