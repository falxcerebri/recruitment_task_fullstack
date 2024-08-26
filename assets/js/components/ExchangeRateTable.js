import React from 'react';
import { Link } from 'react-router-dom';

const ExchangeRateTable = ({ rates, selectedDate }) => (
    <div className="table-responsive">
        <table className="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Waluta</th>
                    <th>Kod</th>
                    <th>Kupno</th>
                    <th>Kurs średni</th>
                    <th>Sprzedaż</th>
                </tr>
            </thead>
            <tbody>
                {Array.isArray(rates)
                    ? rates.map((rate, index) => (
                        <tr key={index}>
                            <td>{rate.currency ?? 'N/A'}</td>
                            <td>
                                {rate.code ? (
                                    <Link to={`/exchange-rates/${rate.code}/${selectedDate || ''}`}>
                                        {rate.code}
                                    </Link>
                                ) : (
                                    'N/A'
                                )}
                            </td>
                            <td>{rate.buy ? rate.buy.toFixed(4) : 'N/A'}</td>
                            <td>{rate.mid ? rate.mid.toFixed(4) : 'N/A'}</td>
                            <td>{rate.sell ? rate.sell.toFixed(4) : 'N/A'}</td>
                        </tr>
                    ))
                    : (
                        <tr>
                            <td>{rates.currency ?? 'N/A'}</td>
                            <td>{rates.code ?? 'N/A'}</td>
                            <td>{rates.buy ? rates.buy.toFixed(4) : 'N/A'}</td>
                            <td>{rates.mid ? rates.mid.toFixed(4) : 'N/A'}</td>
                            <td>{rates.sell ? rates.sell.toFixed(4) : 'N/A'}</td>
                        </tr>
                    )
                }
            </tbody>
        </table>
    </div>
);

export default ExchangeRateTable;
