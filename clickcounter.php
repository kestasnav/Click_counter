<?php

//require_once _PS_MODULE_DIR_ . 'clickcounter/models/save-link-click.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class ClickCounter extends Module
{

    /** Modulio savybės / Module attributes */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'clickcounter';
        $this->tab = 'others';
        $this->version = '0.0.1';
        $this->author = 'kestasnav';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
        $this->displayName = $this->l('Click Counter');
        $this->description = $this->l('Counts clicks on your website');
    }

    public function install()
    {
        return parent::install()

            && $this->registerHook('displayHeader')
            && Configuration::updateValue('click_counter', 0);

    }

    public function uninstall()
    {
        return parent::uninstall() &&
            Configuration::deleteByName('click_counter');
    }

    /** Turinio valdymo dalyje atvaizdavimas / Rendering in the content management section */
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $counter = (int)Tools::getValue('click_counter');
            Configuration::updateValue('click_counter', $counter);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    /** Paspaudimų skaitiklio forma / Clicks counter form */
    public function displayForm()
    {
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'html',
                    'label' => $this->l('Click Counter:') . ' '. $this->l((int) Configuration::get('click_counter')),
                    'required' => true
                ]
            ],
            'submit' => [
                'title' => $this->l('Reset'),
                'icon' => 'icon-refresh'
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->fields_value['click_counter'] = Configuration::get('click_counter');

        return $helper->generateForm($fields_form);
    }

    /** Skaičiuoja ir atvaizduoja paspaudimų skaičių / Counts and displays the number of clicks */
    public function hookDisplayHeader()
    {

        $click_counter = (int)Configuration::get('click_counter');
        $click_counter++;

        Configuration::updateValue('click_counter', $click_counter);

        $this->context->smarty->assign([
            'click_counter' => $click_counter
        ]);

        return $this->display(__FILE__, 'views/templates/hook/clickcounter.tpl');
    }

}