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

class Form extends HelperForm
{
    public function __construct($name)
    {
        parent::__construct();

        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = $defaultLang;
        $this->currentIndex = AdminController::$currentIndex . "&configure=$name";
        $this->default_form_language = $defaultLang;

        $configuration = Configuration::getMultiple(array_keys(Constants::$configuration));

        foreach ($configuration as $k => $v) {
            $configuration["config[$k]"] = $v;

            unset($configuration[$k]);
        }

        $this->fields_value = $configuration;

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

                        $this->makeBool(
                            'INVOICE',
                            'Text on invoice generation?',
                            'Send a text message after an invoice has been created?'
                        ),
                        $this->makeBool(
                            'PAYMENT',
                            'Text on payment?',
                            'Send a text message after payment has been received?'
                        ),
                        $this->makeBool(
                            'SHIPMENT',
                            'Text on shipment?',
                            'Send a text message after shipment?'
                        ),
                        $this->makeBool(
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
                            'ON_INVOICE',
                            'Sets the text message sent to the customer after invoice generation.'
                        ),
                        $this->makeTextarea(
                            'ON_PAYMENT',
                            'Sets the text message sent to the customer after payment.'
                        ),
                        $this->makeTextarea(
                            'ON_SHIPMENT',
                            'Sets the text message sent to the customer after shipment.'
                        ),
                        $this->makeTextarea(
                            'ON_DELIVERY',
                            'Sets the text message sent to the customer after delivery.'
                        ),
                        $this->makeTextarea(
                            'SIGNATURE',
                            'Sets a signature to add to all messages.'
                        ),
                        [
                            'tab' => 'settings',
                            'type' => 'radio',
                            'name' => 'config[SMS77_SIGNATURE_POSITION]',
                            'label' => $this->l('Signature position'),
                            'hint' => $this->l('Decides at which position the signature gets inserted.'),
                            'desc' => $this->l('Decides at which position the signature gets inserted.'),
                            'values' => array_map(function ($pos) {
                                return [
                                    'id' => "sms77_config_signature_position_$pos",
                                    'label' => $pos,
                                    'value' => $pos,
                                ];
                            }, Constants::$signature_positions),
                        ],
                        $this->makeTextarea(
                            'BULK',
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
        $this->submit_action = "submit$name";

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
            'name' => "config[SMS77_$action]",
            'label' => $trans,
            'hint' => $trans,
            'desc' => $trans,
        ];
    }

    private function makeSwitch($action, $label, $desc, $values, $isBool)
    {
        $descHit = $this->l($desc);

        return [
            'tab' => 'settings',
            'type' => 'switch',
            'name' => "config[SMS77_$action]",
            'label' => $this->l($label),
            'desc' => $descHit,
            'hint' => $descHit,
            'is_bool' => $isBool,
            'values' => $values,
        ];
    }

    private function makeBool($action, $label, $desc)
    {
        $sAction = Tools::strtolower($action);

        return $this->makeSwitch("MSG_ON_$action", $label, $desc, [
            [
                'id' => 'on_' . $sAction . '_on',
                'value' => 1
            ],
            [
                'id' => 'on_' . $sAction . '_off',
                'value' => 0
            ],
        ], true);
    }
}
