<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */
class Beedev_CatalogpriceruleTester_Adminhtml_Catalogpriceruletester_RulereportController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalogpriceruletester_rulereport/edit", array('id' => '1')));
    }

    public function editAction()
    {
        $this->loadLayout()
            ->_addContent($this->getLayout()->createBlock('catalogpriceruletester/adminhtml_rulereport_edit'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $catalogPriceRule = $data['catalog_price_rule'];
            if (Mage::helper('catalogpriceruletester/rulereport')->generateReportCsv($catalogPriceRule)) {
                Mage::getSingleton('core/session')->addSuccess('File created Successfully.');
            } else {
                Mage::getSingleton('core/session')->addError('This rule does not apply to any products.');
            }
        }

        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalogpriceruletester_rulereport/edit"));
    }

    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('rule_id');
        if (!$id) {
            return $this->_fault($this->__('Please select a rule to duplicate.'));
        }

        try {
            $mainRule = Mage::getSingleton('catalogrule/rule')->load($id);
            if (!$mainRule->getId()) {
                return $this->_fault($this->__('Please select a rule to duplicate.'));
            }

            $mainRule->getStoreLabels();
            $rule = clone $mainRule;
            $rule->setId(null);
            $rule->save();

            $this->_getSession()->addSuccess(
                $this->__('The rule has been duplicated. Set a new coupon and activate it if needed.')
            );
            return $this->_redirect('adminhtml/promo_catalog/edit', array('id' => $rule->getId()));
        } catch (Exception $e) {
            return $this->_fault($e->getMessage());
        }
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('beedev/catalogpriceruletester/rulereport');
    }
}