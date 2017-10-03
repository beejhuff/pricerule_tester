<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */
class Beedev_CatalogpriceruleTester_Block_Adminhtml_Rulereport_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $catalogRules = Mage::getModel('catalogrule/rule')->getCollection();
        $arrayCatalogRules = array();
        foreach ($catalogRules as $catalogRule) {
            $arrayCatalogRules[] = array(
                'value' => $catalogRule->getName(),
                'label' => $catalogRule->getName()
            );
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'class' => 'rulereportEditForm',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('rulereportFieldset2', array(
            'legend' => Mage::helper('catalogpriceruletester')->__('Catalog Price Rule Report'),
            'class' => 'rulereportFieldset2'
        ));

        $fieldset->addField('catalog_price_rule', 'select', array(
            'name' => 'catalog_price_rule',
            'label' => 'Catalog Price Rule',
            'id' => 'catalog_price_rule',
            'title' => 'Catalog Price Rule',
            'required' => true,
            'values' => $arrayCatalogRules,
            'note' => Mage::helper('salesrule')->__('Select a Catalog Price Rule to generate a product report'),
        ));

        $fieldset->addField('from_x_to_y_run', 'submit', array(
            'label' => '',
            'name' => 'from_x_to_y_run',
            'value' => 'Run',
        ));

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/dataexport/rulereport.csv')) {
            $fieldset = $form->addFieldset('rulereportFieldset3', array(
                'legend' => Mage::helper('catalogpriceruletester')->__('File Download'),
                'class' => 'rulereportFieldset2'
            ));

            $fieldset->addField('filelink', 'link', array(
                'label' => Mage::helper('catalogpriceruletester')->__('Download File'),
                'href' => '/dataexport/rulereport.csv?a=' . uniqid(),
                'value' => 'rulereport.csv'
            ));
        }
        return parent::_prepareForm();
    }
}