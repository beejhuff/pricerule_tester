<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */
class Beedev_CatalogpriceruleTester_Block_Adminhtml_Producttest_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'catalogpriceruletester';
        $this->_controller = 'adminhtml_producttest';
        $this->_mode = 'edit';

        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('save');
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        return Mage::helper('catalogpriceruletester')->__('Catalog Price Rule Product Test');
    }
}