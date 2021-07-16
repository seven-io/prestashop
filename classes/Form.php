<?php
/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 * @author    sms77.io
 * @copyright 2019-present sms77 e.K.
 * @license   LICENSE
 */

class Form extends HelperForm {
    private function getOptionName($k, $v) {
        $optionName = 'config[' . $k . ']';

        if (is_array($v)) $optionName .= '[]';

        return $optionName;
    }

    private function setFieldValues() {
        $config = Configuration::getMultiple(array_keys(Constants::CONFIGURATION));

        foreach ($config as $k => $v)
            $this->fields_value[$this->getOptionName($k, $v)] = $v;
    }

    public function __construct($name) {
        parent::__construct();

        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = $defaultLang;
        $this->currentIndex = Sms77AdminController::$currentIndex . '&configure=' . $name;
        $this->default_form_language = $defaultLang;

        $this->setFieldValues();

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
                            'desc' => $this->l('An API-Key is needed for sending. Get yours now at sms77.io'),
                            'hint' => $this->l('Your sms77.io API-Key.'),
                            'label' => $this->l('API-Key'),
                            'name' => FormUtil::toName(Constants::API_KEY),
                            'required' => true,
                            'tab' => 'settings',
                            'type' => 'text',
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
                        $this->makeBool(
                            'REFUND',
                            'Text on refund?',
                            'Send a text message after refund initiation?'
                        ),
                        [
                            'desc' => $this->l('Max 11 alphanumeric or 16 numeric characters.'),
                            'hint' => $this->l('Set a custom sender number or name.'),
                            'label' => $this->l('From'),
                            'name' => FormUtil::toName(Constants::FROM),
                            'size' => 16,
                            'tab' => 'settings',
                            'type' => 'text',
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
                            'ON_REFUND',
                            'Sets the text message sent to the customer after refund.'
                        ),
                        $this->makeTextarea(
                            'SIGNATURE',
                            'Sets a signature to add to all messages.'
                        ),
                        FormUtil::signaturePosition(
                            $this->l('Signature position'),
                            $this->l('Decides at which position the signature gets inserted.'),
                            'settings',
                            true
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
        $this->show_toolbar = true;
        $this->submit_action = 'submit' . $name;
        $this->title = $name;
        $this->token = Tools::getAdminTokenLite('AdminModules');
        $this->toolbar_btn = [
            'back' => [
                'href' => Sms77AdminController::$currentIndex
                    . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
            'save' => [
                'desc' => $this->l('Save'),
                'href' => Sms77AdminController::$currentIndex
                    . "&configure=$name&save$name&token="
                    . Tools::getAdminTokenLite('AdminModules'),
            ],
        ];
        $this->toolbar_scroll = true;
    }

    /**
     * @param string $action
     * @param string $label
     * @param string $desc
     * @return array
     */
    private function makeBool($action, $label, $desc) {
        return FormUtil::makeSwitch(
            'config[SMS77_MSG_ON_' . $action . ']',
            $label, Tools::strtolower($action), true, 'settings', $desc
        );
    }

    /**
     * @param string $action
     * @param string $trans
     * @return array
     */
    private function makeTextarea($action, $trans) {
        return FormUtil::makeTextarea(
            'config[SMS77_' . $action . ']', $trans, 'settings');
    }
}
