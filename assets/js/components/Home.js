import React from 'react';
import { Route, Switch, Link } from 'react-router-dom';
import SetupCheck from './SetupCheck';
import ExchangeRates from './ExchangeRates';

const Home = () => {
    return (
        <div>
            <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
                <Link className={"navbar-brand"} to={"#"}> Telemedi Zadanko </Link>
                <div id="navbarText">
                    <ul className="navbar-nav mr-auto">
                        <li className="nav-item">
                            <Link className={"nav-link"} to={"/setup-check"}> React Setup Check </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={"nav-link"} to={"/exchange-rates"}> Exchange Rates </Link>
                        </li>
                    </ul>
                </div>
            </nav>
            <Switch>
                <Route path="/setup-check" component={SetupCheck} />
                <Route path="/exchange-rates/:currency/:date" component={ExchangeRates} />
                <Route path="/exchange-rates/:currencyOrDate" component={ExchangeRates} />
                <Route path="/exchange-rates" component={ExchangeRates} />
            </Switch>
        </div>
    );
}

export default Home;
