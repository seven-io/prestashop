<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    sms77.io
 * @copyright 2019-present sms77 e.K.
 * @license   LICENSE
 */

class BackendHelperForm extends HelperForm
{
    public function __construct($name)
    {
        parent::__construct($name);

        $defaultLang = Configuration::get('PS_LANG_DEFAULT');

        $this->allow_employee_form_lang = $defaultLang;

        $this->currentIndex = AdminController::$currentIndex . "&configure=$name";

        $this->default_form_language = $defaultLang;

        $this->fields_value = [
            'config[SMS77_API_KEY]' => Configuration::get('SMS77_API_KEY'),
            'config[SMS77_ON_INVOICE]' => Configuration::get('SMS77_ON_INVOICE'),
            'config[SMS77_ON_DELIVERY]' => Configuration::get('SMS77_ON_DELIVERY'),
            'config[SMS77_ON_SHIPMENT]' => Configuration::get('SMS77_ON_SHIPMENT'),
            'config[SMS77_ON_PAYMENT]' => Configuration::get('SMS77_ON_PAYMENT'),
            'config[SMS77_MSG_ON_DELIVERY]' => Configuration::get('SMS77_MSG_ON_DELIVERY'),
            'config[SMS77_MSG_ON_INVOICE]' => Configuration::get('SMS77_MSG_ON_INVOICE'),
            'config[SMS77_MSG_ON_SHIPMENT]' => Configuration::get('SMS77_MSG_ON_SHIPMENT'),
            'config[SMS77_MSG_ON_PAYMENT]' => Configuration::get('SMS77_MSG_ON_PAYMENT'),
            'config[SMS77_FROM]' => Configuration::get('SMS77_FROM'),
            'config[SMS77_ON_GENERIC]' => Configuration::get('SMS77_ON_GENERIC'),
        ];

        $this->fields_form = [
            [
                'form' => [
                    'tabs' => [
                        'settings' => $this->l('Configuration'),
                        'bulk' => $this->l('Bulk SMS'),
                    ],
                    'legend' => [
                        'title' => $this->l('Settings'),
                    ],
                    'input' => [
                        [
                            'tab' => 'settings',
                            'type' => 'text',
                            'name' => 'config[SMS77_API_KEY]',
                            'label' => $this->l('API-Key'),
                            'hint' => $this->l('Your sms77.io API-Key.'),
                            'desc' => $this->l('An API-Key is needed for sending. Get yours now at sms77.io'),
                            'required' => true,
                        ],

                        $this->makeSwitch(
                            'INVOICE',
                            'Text on invoice generation?',
                            'Send a text message after an invoice has been created?'
                        ),
                        $this->makeSwitch(
                            'PAYMENT',
                            'Text on payment?',
                            'Send a text message after payment has been received?'
                        ),
                        $this->makeSwitch(
                            'SHIPMENT',
                            'Text on shipment?',
                            'Send a text message after shipment?'
                        ),
                        $this->makeSwitch(
                            'DELIVERY',
                            'Text on delivery?',
                            'Send a text message after delivery?'
                        ),
                        [
                            'tab' => 'settings',
                            'type' => 'text',
                            'name' => 'config[SMS77_FROM]',
                            'label' => $this->l('From'),
                            'hint' => $this->l('Set a custom sender number or name.'),
                            'desc' => $this->l('Max 11 alphanumeric or 16 numeric characters.'),
                            'size' => 16,
                        ],
                        $this->makeTextarea(
                            'INVOICE',
                            'Sets the text message sent to the customer after invoice generation.'
                        ),
                        $this->makeTextarea(
                            'PAYMENT',
                            'Sets the text message sent to the customer after payment.'
                        ),
                        $this->makeTextarea(
                            'SHIPMENT',
                            'Sets the text message sent to the customer after shipment.'
                        ),
                        $this->makeTextarea(
                            'DELIVERY',
                            'Sets the text message sent to the customer after delivery.'
                        ),
                        $this->makeTextarea(
                            'GENERIC',
                            'Send out any message to all of your customers.',
                            'bulk'
                        ),
                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                        'class' => 'btn btn-default pull-right',
                    ],
                ],
            ],
        ];

        $this->module = $this;

        $this->name = $name;

        $this->name_controller = $name;

        $this->title = $name;

        $this->token = Tools::getAdminTokenLite('AdminModules');

        $this->show_toolbar = true;

        $this->submit_action = 'submit' . $name;

        $this->toolbar_btn = [
            'save' =>
                [
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex . "&configure=$name&save$name&token="
                        . Tools::getAdminTokenLite('AdminModules'),
                ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];

        $this->toolbar_scroll = true;
    }

    private function makeTextarea($action, $trans, $tab = 'settings')
    {
        $trans = $this->l($trans);

        return [
            'tab' => $tab,
            'type' => 'textarea',
            'name' => "config[SMS77_ON_$action]",
            'label' => $trans,
            'hint' => $trans,
            'desc' => $trans,
        ];
    }

    private function makeSwitch($action, $label, $desc, $tab = 'settings')
    {
        $descHit = $this->l($desc);

        return [
            'tab' => $tab,
            'type' => 'switch',
            'name' => "config[SMS77_MSG_ON_$action]",
            'label' => $this->l($label),
            'desc' => $descHit,
            'hint' => $descHit,
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'on_' . Tools::strtolower($action) . '_on',
                    'value' => 1,
                    'label' => $this->l('Yes'),
                ],
                [
                    'id' => 'on_' . Tools::strtolower($action) . '_off',
                    'value' => 0,
                    'label' => $this->l('No'),
                ],
            ],
        ];
    }
}
