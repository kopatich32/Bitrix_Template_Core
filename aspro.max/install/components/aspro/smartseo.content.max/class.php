<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc as Loc;;

Loc::loadMessages(__FILE__);

class SmartseoContentComponent extends \CBitrixComponent
{

    const PAGE_DEFAULT = 'template';
    const PAGE_ERRORS = 'error';
    const DEFAULT_CODE_VIEW_CONTENT = 'aspro_smartseo_content';

    private $_errors = [];
    private $_summary = [];

    public function getUnique()
    {
        return md5($this->GetName() . '.' . $this->GetTemplateName());
    }

    /**
     * @inheritdoc
     */
    public function executeComponent()
    {
        global $APPLICATION;

        if (!$this->validate()) {
            $this->arResult = [
                'ERROR_MESSAGE' => $this->getErrors(),
            ];

            $this->includeComponentTemplate(self::PAGE_ERRORS);

            return;
        }
        $this->setSefDefaultParams();
        $this->setResult();
        $this->includeComponentTemplate(self::PAGE_DEFAULT);
    }

    public function hasErrors()
    {
        if ($this->_errors) {
            return true;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getSummary()
    {
        return $this->_summary;
    }

    protected function validate()
    {
        if(!class_exists('\Aspro\Max\Smartseo\General\Smartseo') || !Smartseo\General\Smartseo::validateModules()) {
            $this->_errors[] = Loc::getMessage('ASPRO_SMARTSEO_CONTENT_ERROR_MODULE_NOT_INSTALLED');

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function setSefDefaultParams()
    {
        if(!$this->arParams['CODE_VIEW_CONTENT']) {
            $this->arParams['CODE_VIEW_CONTENT'] = self::DEFAULT_CODE_VIEW_CONTENT;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function setResult()
    {
        if($this->arParams['FIELDS'] && Smartseo\General\Smartseo::getCurrentSeoProperty()) {
            foreach ($this->arParams['FIELDS'] as $field) {

            }
        }

        $this->arResult = [
            'UNIQUE' => $this->getUnique(),
            'VALUES' => Smartseo\General\Smartseo::getCurrentSeoProperty(),
        ];
    }

}

?>
