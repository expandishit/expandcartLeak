import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import App from './App';

import {IntlProvider} from "react-intl";
import messages_ar from "./translations/ar.json";
import messages_en from "./translations/en.json";

const messages = {
  'ar': messages_ar,
  'en': messages_en
};


const adminLanguage = document.getElementsByTagName('html')[0].getAttribute('lang') == 'en' ? 'en' : 'ar';

ReactDOM.render(
  <BrowserRouter>
  <IntlProvider locale={adminLanguage} messages={messages[adminLanguage]}>
    <App />
  </IntlProvider>
  </BrowserRouter>
  , document.getElementById('rootApp'));