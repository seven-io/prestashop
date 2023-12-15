<img src="https://www.seven.io/wp-content/uploads/Logo.svg" width="250" />


# Official module for PrestaShop 1.6 & 1.7

## Installation

**Via Composer**

1. Open a shell and navigate to the PrestaShop installation
2. Run `composer require sms77/prestashop-module`
3. Administration: Go to `Modules->Module Manager` and activate `sms77`

**Via GitHub**

1. [Download](https://github.com/seven-io/prestashop/releases/download/2.0.0/sms77-prestashop-2.0.0.zip)
   the latest release as *.zip
2. Extract archive `unzip -d /path/to/prestashop/modules <archive_name>.zip`
3. Administration: Go to `Modules->Module Manager`, search for `sms77` and click `install`

### Usage

Go to the module manager and search for `sms77`. Click on the `settings` button and look
through the available options. Remember to set your API key in order to be able to send
messages.

**Available message placeholders:**

- {address.&lt;property>} => Use a property from the *Address* object
    - {address.firstname} resolves to the customers first name
    - {address.lastname} resolves to the customers last name
- {invoice.&lt;property>} => Use a property from the *OrderInvoice* object (available only
  on invoice creation)
    - {invoice.number} resolves to the invoice number
    - {invoice.total_paid_tax_incl} resolves to the invoices total amount tax included
- {order.&lt;property>} => Use a property from the *Order* object (if available)
    - {order.id} resolves to the order ID
    - {order.reference} resolves to the order reference

**Addresses**: The delivery address takes precedence over the billing address. So if the
delivery address differs to the billing address, the delivery address will be taken for
message placeholders.

#### Implemented Events

- Delivery - the order status has been set to delivered
- Invoice Creation - an order invoice has been created
- Payment - the order has been marked as being fully paid
- Refund - the order status has been set to refunded
- Shipping - the order status has been set to shipped

###### Support

Need help? Feel free to [contact us](https://www.seven.io/en/company/contact/).

[![MIT](https://img.shields.io/badge/License-MIT-teal.svg)](./LICENSE)

**Screenshots**

![Screenshot of plugin configuration](https://tettra-production.s3.us-west-2.amazonaws.com/0d6efb4f154041e899af17bdcd19c1b5/bcac36a50716f4f73cd84020c4bf091d/d822b155a4112474fdb7aea5ee22465e/cb30d8dd64d0e83fcc7822a40f1703d9/mLBF1Q0g4SCVCXQSEfzElQAJBvxDiaqqTTSqY2lS.png 'PrestaShop.Sms77: Plugin Configuration')
![Screenshot of bulk SMS creation](https://tettra-production.s3.us-west-2.amazonaws.com/0d6efb4f154041e899af17bdcd19c1b5/bcac36a50716f4f73cd84020c4bf091d/d822b155a4112474fdb7aea5ee22465e/cb30d8dd64d0e83fcc7822a40f1703d9/8hpOqOKmtJkPkuEPHtw1nQJksLbhWZgsFbXDuCV2.png 'PrestaShop.Sms77: Compose bulk SMS')
![Screenshot of sent SMS](https://tettra-production.s3.us-west-2.amazonaws.com/0d6efb4f154041e899af17bdcd19c1b5/bcac36a50716f4f73cd84020c4bf091d/d822b155a4112474fdb7aea5ee22465e/cb30d8dd64d0e83fcc7822a40f1703d9/Ir18yYjK7ZtbkwWagNUIjmkCIKCbxeaGkO62Fbmz.png 'PrestaShop.Sms77: Sent SMS')
