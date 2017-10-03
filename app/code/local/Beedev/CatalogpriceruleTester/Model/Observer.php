<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */
class Beedev_CatalogpriceruleTester_Model_Observer
{

    public function adminhtmlWidgetContainerHtmlBefore($event)
    {
        $block = $event->getBlock();
        $id = Mage::app()->getRequest()->getParam('id');
        if ($block instanceof Mage_Adminhtml_Block_Promo_Catalog_Edit) {
            $block->addButton('duplicate_rule', array(
                'onclick' => 'setLocation(\' ' . Mage::helper('adminhtml')->getUrl('adminhtml/catalogpriceruletester_rulereport/duplicate', array('rule_id' => $id)) . '\')',
                'label' => Mage::helper('catalogpriceruletester')->__('Duplicate'),
                'class' => 'go'
            ));
        }
    }
}