<?php
/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 * @author    seven.io
 * @copyright 2019-present seven communications GmbH & Co. KG
 * @license   LICENSE
 */

use Sms77\Api\Constant\SmsOptions;

class SevenAdminController extends ModuleAdminController {
    public function __construct() {
        $this->table = 'seven_message';
        $this->className = 'SevenMessage';

        parent::__construct();
    }

    public function postProcess() {
        if (!Tools::isSubmit('submitAdd' . $this->table)) return;

        if (!Util::hasApiKey()) {
            $this->errors[] = Tools::displayError('No API key given.');
            return;
        }

        $res = SmsUtil::sendBulk();

        if (null === $res || (is_array($res) && !count($res)))
            $this->errors[] = Tools::displayError('An error has occurred: ' . $res);
        else
            Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
    }

    public function renderForm() {
        $this->redirectIfMissingApiKey();

        $context = Context::getContext();

        $this->fields_value = [
            SmsOptions::From =>
                Configuration::get(Constants::FROM),
            Constants::SIGNATURE =>
                Configuration::get(Constants::SIGNATURE),
            Constants::SIGNATURE_POSITION =>
                Configuration::get(Constants::SIGNATURE_POSITION),
        ];

        $this->fields_form = [
            'input' => [
                [
                    'label' => $this->module->l('Text'),
                    'name' => SmsOptions::Text,
                    'rows' => 5,
                    'type' => 'textarea',
                ],
                [
                    'hint' => $this->module->l('Limits sending only to customers from the selected countries.'),
                    'label' => $this->module->l('Countries'),
                    'multiple' => true,
                    'name' => Constants::BULK_COUNTRIES . '[]',
                    'options' => [
                        'id' => 'id_country',
                        'name' => 'name',
                        'query' => Country::getCountries($context->language->id),
                    ],
                    'type' => 'select',
                ],
                [
                    'hint' => $this->module->l('Limits sending only to customers from the selected groups.'),
                    'label' => $this->module->l('Groups'),
                    'multiple' => true,
                    'name' => Constants::BULK_GROUPS . '[]',
                    'options' => [
                        'id' => 'id_group',
                        'name' => 'name',
                        'query' => Group::getGroups($context->language->id),
                    ],
                    'type' => 'select',
                ],
                [
                    'label' => $this->module->l('Signature'),
                    'name' => Constants::SIGNATURE,
                    'rows' => 3,
                    'type' => 'textarea',
                ],
                FormUtil::signaturePosition(
                    $this->l('Signature position'),
                    $this->l('Decides at which position the signature gets inserted.'),
                    null,
                    false
                ),
                FormUtil::makeSwitch(
                    'ignore_signature',
                    $this->module->l('Ignore Signature')
                ),
                [
                    'label' => $this->module->l('From'),
                    'name' => SmsOptions::From,
                    'type' => 'text',
                ],
                [
                    'label' => $this->module->l('Label'),
                    'name' => SmsOptions::Label,
                    'type' => 'text',
                ],
                [
                    'label' => $this->module->l('Delay'),
                    'name' => SmsOptions::Delay,
                    'type' => 'text',
                ],
                [
                    'label' => $this->module->l('User Defined Header'),
                    'name' => SmsOptions::Udh,
                    'type' => 'text',
                ],
                [
                    'label' => $this->module->l('Time To Live'),
                    'name' => SmsOptions::Ttl,
                    'type' => 'text',
                ],
                [
                    'label' => $this->module->l('Foreign ID'),
                    'name' => SmsOptions::ForeignId,
                    'type' => 'text',
                ],
                FormUtil::makeSwitch(
                    SmsOptions::Flash,
                    $this->module->l('Flash'),
                    'flash'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::Debug,
                    $this->module->l('Debug'),
                    'debug'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::NoReload,
                    $this->module->l('No Reload'),
                    'no_reload'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::Unicode,
                    $this->module->l('Unicode'),
                    'unicode'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::Details,
                    $this->module->l('Details'),
                    'details'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::ReturnMsgId,
                    $this->module->l('Return Message ID'),
                    'return_msg_id'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::Json,
                    $this->module->l('JSON'),
                    'json'
                ),
                FormUtil::makeSwitch(
                    SmsOptions::PerformanceTracking,
                    $this->module->l('Performance Tracking'),
                    'performance_tracking'
                ),
            ],
            'legend' => [
                'title' => $this->module->l('New Bulk Message'),
            ],
            'submit' => [
                'class' => 'button',
                'title' => $this->module->l('Save'),
            ],
            'tinymce' => true,
        ];

        if (!$this->loadObject(true)) {
            return;
        }

        return parent::renderForm();
    }

    private function redirectIfMissingApiKey() {
        if (!Util::hasApiKey()) {
            Tools::redirectAdmin(Util::pluginConfigLink());
        }
    }

    public function renderList() {
        $this->redirectIfMissingApiKey();

        $this->fields_list = [
            'id_seven_message' => [
                'title' => $this->module->l('ID'),
            ],
            'timestamp' => [
                'title' => $this->module->l('Timestamp'),
            ],
            'response' => [
                'title' => $this->module->l('Response'),
            ],
            'type' => [
                'title' => $this->module->l('Type'),
            ],
            'config' => [
                'title' => $this->module->l('Config'),
            ],
            'groups' => [
                'title' => $this->module->l('Groups'),
            ],
            'countries' => [
                'title' => $this->module->l('Countries'),
            ],
        ];

        $this->initToolbar();

        return parent::renderList();
    }
}
