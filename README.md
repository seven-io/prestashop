# sms77.io PrestaShop module

## Installation
- Via GitHub
    1. Head to /releases and download the latest *.ZIP.
    2. Extract the files inside /modules/sms77_api.
    3. Head to the module manager and search for sms77.
    4. Click on the install button.

- Via Composer
    1. Open a shell and navigate to the PrestaShop installation.
    2. Run composer require sms77/prestashop-module.
    3. Head to the module manager and activate sms77 API.

### Usage
Go to the module manager and search for sms77. 
Click on the settings button and look through the available options.
Remember to set your API-Key in order to be able to send messages.

Available message placeholders:
- {0} => First name
- {1} => Last name
- {2} => Order-ID (where available)

#### Implemented Events
- SMS77_MSG_ON_DELIVERY (orderState->id = 5)
- SMS77_MSG_ON_INVOICE (orderState->id = [1, 10, 13])
- SMS77_MSG_ON_PAYMENT (orderState->id = [2, 11])
- SMS77_MSG_ON_REFUND (orderState->id = 7)
- SMS77_MSG_ON_SHIPMENT (orderState->id = 4)