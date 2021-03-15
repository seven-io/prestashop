![Sms77.io Logo](https://www.sms77.io/wp-content/uploads/2019/07/sms77-Logo-400x79.png "sms77io")
# Official Module for PrestaShop 1.6+

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
- SMS77_MSG_ON_DELIVERY (orderStateId is 5)
- SMS77_MSG_ON_INVOICE (orderStateId is 1, 10 or 13)
- SMS77_MSG_ON_PAYMENT (orderStateId is 2 or 11)
- SMS77_MSG_ON_REFUND (orderStateId is 7)
- SMS77_MSG_ON_SHIPMENT (orderStateId is 4)

##### Screenshots
![Screenshot of plugin configuration](https://tettra-production.s3.us-west-2.amazonaws.com/0d6efb4f154041e899af17bdcd19c1b5/bcac36a50716f4f73cd84020c4bf091d/d822b155a4112474fdb7aea5ee22465e/cb30d8dd64d0e83fcc7822a40f1703d9/mLBF1Q0g4SCVCXQSEfzElQAJBvxDiaqqTTSqY2lS.png "PrestaShop.Sms77: Plugin Configuration")
![Screenshot of bulk SMS creation](https://tettra-production.s3.us-west-2.amazonaws.com/0d6efb4f154041e899af17bdcd19c1b5/bcac36a50716f4f73cd84020c4bf091d/d822b155a4112474fdb7aea5ee22465e/cb30d8dd64d0e83fcc7822a40f1703d9/8hpOqOKmtJkPkuEPHtw1nQJksLbhWZgsFbXDuCV2.png "PrestaShop.Sms77: Compose bulk SMS")
![Screenshot of sent SMS](https://tettra-production.s3.us-west-2.amazonaws.com/0d6efb4f154041e899af17bdcd19c1b5/bcac36a50716f4f73cd84020c4bf091d/d822b155a4112474fdb7aea5ee22465e/cb30d8dd64d0e83fcc7822a40f1703d9/Ir18yYjK7ZtbkwWagNUIjmkCIKCbxeaGkO62Fbmz.png "PrestaShop.Sms77: Sent SMS")
