<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_Catalogpricerulepromo
 */
class Beedev_CatalogpriceruleTester_Helper_Producttest extends Mage_Core_Helper_Abstract
{
    protected $csvLine = array();

    public function generateReportCsv($productSKU)
    {
        $fp = fopen(Mage::getBaseDir() . DS . 'dataexport' . DS . 'productreport.csv', 'w');
        if ($fp === false) {
            Mage::throwException('Could not create report CSV');
        }

        fputcsv($fp, array('Product: ', $productSKU));
        fputcsv($fp, array());
        fputcsv($fp, array_keys($this->csvLine[0]));

        fputcsv($fp, Array(
            'ID',
            'NAME',
            'PRICE',
            'POST-PROMO PRICE'
        ));

        try {
            $product_id = Mage::getModel("catalog/product")->getIdBySku($productSKU);
            $theProduct = Mage::getModel('catalog/product')->load($product_id);
            if (!$product_id)
                return false;
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('catalogrule');
            $promoList = $connection->fetchAll('SELECT * FROM ' . $tableName);

            $canAddToCsv = false;
            $productCanAddToCsv = true;

            foreach ($promoList as $promo) {
                $conditions = unserialize($promo['conditions_serialized']);
                if ($conditions['conditions'] == null) {
                    $canAddToCsv = true;
                    $productCanAddToCsv = true;
                } else {
                    foreach ($conditions['conditions'] as $ruleCondition) {
                        if ($ruleCondition['attribute'] == 'category_ids') {
                            $categoryIdList = $ruleCondition['value'];
                            $categoryPieces = explode(",", $categoryIdList);
                            foreach ($categoryPieces as $categoryPiece) {
                                $products = Mage::getModel('catalog/category')->load($categoryPiece)
                                    ->getProductCollection()
                                    ->addAttributeToSelect('sku');
                                foreach ($products as $secondProduct) {
                                    $isApplicable = null;
                                    $isApplicable = $this->getApplicable($secondProduct->getSku(), $productSKU, $ruleCondition['operator']);
                                    if ($isApplicable && $canAddToCsv == false) {
                                        $canAddToCsv = true;
                                        break;
                                    }
                                }
                            }
                        } else {
                            $productCanAddToCsv = false;
                            $allAttributes = (string)$ruleCondition['value'];
                            $explodedAttributes = explode(",", $allAttributes);
                            $filter = $this->getRealFilter($ruleCondition['operator']);
                            $productCollection = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToFilter($ruleCondition['attribute'], array($filter => $explodedAttributes))->addAttributeToSelect('price')
                                ->addAttributeToSelect('name')->addAttributeToSelect('color');

                            foreach ($productCollection as $miniProduct) {
                                if ($miniProduct->getId() == $theProduct->getId()) {
                                    $productCanAddToCsv = true;
                                }

                            }
                        }
					}
                    if ($canAddToCsv && $productCanAddToCsv) {
                        if ($promo['simple_action'] == 'by_percent') {
                            $discounted = ($theProduct->getPrice() / 100) * (100 - $promo['discount_amount']);
                        } elseif ($promo['simple_action'] == 'to_percent') {
                            $discounted = ($theProduct->getPrice() / 100) * $promo['discount_amount'];
                        } elseif ($promo['simple_action'] == 'by_fixed') {
                            $discounted = $theProduct->getPrice() - $promo['discount_amount'];
                        } elseif ($promo['simple_action'] == 'to_fixed') {
                            $discounted = $promo['discount_amount'];
                        }

                        $promotionName = $promo['name'];
                        fputcsv($fp, Array(
                            $promo['rule_id'],
                            $promotionName,
                            $theProduct->getPrice(),
                            $discounted
                        ));
                    }
                }
            }
            $returnString = 'Done';
            fclose($fp);
            return $returnString;
        } catch (Exception $e) {
            die($e);
            return false;
        }
    }

    public function getApplicable($promoRuleAttributeValue, $productAttribute, $testConditional)
    {
        $filter = true;
        if ($testConditional == '!=' || $testConditional == '!{}' || $testConditional == '!()') {
            if ($promoRuleAttributeValue == $productAttribute) {
                $filter = false;
            }
        } elseif ($testConditional == '==' || $testConditional == '{}' || $testConditional == '()') {
            if ($promoRuleAttributeValue != $productAttribute) {
                $filter = false;
            }
        }
        return $filter;
    }

    public function getRealFilter($testConditional)
    {
        $filter = null;
        if ($testConditional == '!=') {
            //is not
            $filter = 'neq';
        } elseif ($testConditional == '==') {
            //is
            $filter = 'eq';
        } elseif ($testConditional == '>') {
            //greaterthan
            $filter = 'gt';
        } elseif ($testConditional == '<') {
            //less than
            $filter = 'lt';
        } elseif ($testConditional == '>=') {
            //greater than or equals
            $filter = 'gteq';
        } elseif ($testConditional == '<=') {
            //equals or less than
            $filter = 'lteq';
        } elseif ($testConditional == '{}') {
            //contains
            $filter = 'like';
        } elseif ($testConditional == '!{}') {
            //doesnotcontain
            $filter = 'nlike';
        } elseif ($testConditional == '!()') {
            //isnotoneof
            $filter = 'nin';
        } else {
            //is one of
            $filter = 'in';
        }
        return $filter;
    }
}