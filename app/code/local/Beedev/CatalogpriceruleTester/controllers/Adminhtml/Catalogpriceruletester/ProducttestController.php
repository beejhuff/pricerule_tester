<?php
/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */

class Beedev_CatalogpriceruleTester_Adminhtml_Catalogpriceruletester_ProducttestController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalogpriceruletester_producttest/edit", array('id' => '1')));
    }

    public function editAction()
    {
        $this->loadLayout()
            ->_addContent($this->getLayout()->createBlock('catalogpriceruletester/adminhtml_producttest_edit'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $productSKU = $data['product_sku'];
            $isPriceRuleProduct = Mage::helper('catalogpriceruletester/producttest')->generateReportCsv($productSKU);
            if ($isPriceRuleProduct) {
                Mage::getSingleton('core/session')->addSuccess('File created Successfully.');
            } else {
                Mage::getSingleton('core/session')->addError('This product is not affected by any rules.');
            }
        }
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalogpriceruletester_producttest/edit"));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('beedev/catalogpriceruletester/producttest');
    }
}