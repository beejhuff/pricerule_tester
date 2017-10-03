<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */
class Beedev_CatalogpriceruleTester_Block_Adminhtml_Producttest_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
            'class' => 'producttestEditForm',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('producttestFieldset2', array(
            'legend' => Mage::helper('catalogpriceruletester')->__('Catalog Price Rule Product Test'),
            'class' => 'producttestFieldset2'
        ));

        $fieldset->addField('product_sku', 'text', array(
            'name' => 'product_sku',
            'label' => 'Product SKU',
            'id' => 'product_sku',
            'title' => 'Product SKU',
            'required' => true,
            'note' => Mage::helper('salesrule')->__('Product SKU'),
        ));

        $fieldset->addField('from_x_to_y_run', 'submit', array(
            'label' => '',
            'name' => 'from_x_to_y_run',
            'value' => 'Run',
        ));

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/dataexport/productreport.csv')) {
            $fieldset = $form->addFieldset('producttestFieldset3', array(
                'legend' => Mage::helper('catalogpriceruletester')->__('File Download'),
                'class' => 'producttestFieldset2'
            ));

            $fieldset->addField('filelink', 'link', array(
                'label' => Mage::helper('catalogpriceruletester')->__('Download File'),
                'href' => '/dataexport/productreport.csv?a=' . uniqid(),
                'value' => 'productreport.csv'
            ));
        }
        return parent::_prepareForm();
    }
}