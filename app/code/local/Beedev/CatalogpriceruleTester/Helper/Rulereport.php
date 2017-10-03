<?php

/**
 * @author Beedev Team
 * @copyright Copyright (c) 2017 Beedev Team
 * @package Beedev_CatalogpriceruleTester
 */
class Beedev_CatalogpriceruleTester_Helper_Rulereport extends Mage_Core_Helper_Abstract
{
    protected $csvLine = array();

    public function generateReportCsv($catalogPriceRule)
    {
        try {
            $fp = fopen(Mage::getBaseDir() . DS . 'dataexport' . DS . 'rulereport.csv', 'w');
            if ($fp === false) {
                Mage::throwException('Could not create report CSV');
            }
            fputcsv($fp, array('Catalog Price Rule: ', $catalogPriceRule));
            fputcsv($fp, array());
            fputcsv($fp, array_keys($this->csvLine[0]));

            fputcsv($fp, Array(
                'ID',
                'NAME',
                'SKU',
                'PRICE',
                'POST-PROMO PRICE'
            ));
            $theCoupon = Mage::getModel('catalogrule/rule')->load($catalogPriceRule, 'name');
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('catalogrule');
            $productIdListOriginal = $connection->fetchAll('SELECT * FROM ' . $tableName . ' WHERE rule_id = ' . $theCoupon->getId());

            $conditions = unserialize($productIdListOriginal[0]['conditions_serialized']);
            $productCollection = null;
            $productIdList = "";
            $productIdListClone = "";
            $myCount = 0;

            if ($conditions['conditions'] == null) {
                $productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('price')->addAttributeToSelect('name');
            } else {
                foreach ($conditions['conditions'] as $testCondition) {

                    if ($testCondition['attribute'] == 'category_ids') {
                        $categoryIdList = $testCondition['value'];
                        $categoryPieces = explode(", ", $categoryIdList);
                        if ($testCondition['operator'] == '!()') {
                            $category = Mage::getModel('catalog/category');
                            $catTree = $category->getTreeModel()->load();
                            $catIds = $catTree->getCollection()->getAllIds();
                            $categoryPieces = array_diff($catIds, $categoryPieces);
                        }

                        foreach ($categoryPieces as $categoryPiece) {
                            $products = Mage::getModel('catalog/category')->load($categoryPiece)
                                ->getProductCollection()
                                ->addAttributeToSelect('price')
                                ->addAttributeToSelect('name');

                            foreach ($products as $theProduct) {
                                $discounted = $this->getDiscounted($productIdListOriginal[0], $theProduct);
                                $this->writeToCsv($fp, $theProduct, $discounted);
                            }
                        }
                    } else {
                        $myCount++;
                        if ($testCondition['attribute'] != 'category_ids') {
                            $productIdList2 = $testCondition['value'];
                            $pieces = explode(", ", $productIdList2);
                            $filter = $this->getFilter($testCondition['operator']);
                            $productCollection = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToFilter($testCondition['attribute'], array($filter => $pieces))->addAttributeToSelect('price');

                            foreach ($productCollection as $product) {
                                if ($myCount == 1)
                                    $productIdList = $productIdList . ', ' . $product->getSku();
                                else
                                    $productIdListClone = $productIdListClone . ', ' . $product->getSku();
                            }
                        }
                        if ($myCount > 1) {
                            $pieces1 = explode(", ", $productIdList);
                            $pieces2 = explode(", ", $productIdListClone);
                            $result = array_intersect($pieces1, $pieces2);
                            foreach ($result as $miniresult) {
                                $productIdList = $miniresult . ', ';
                            }
                            $productIdListClone = '';

                        }
                    }
                }
            }

            $pieces = explode(", ", $productIdList);
            $arr_unique = array_unique($pieces);
            if ($conditions['aggregator'] == 'all') {
                $productpieces = array_diff_assoc($pieces, $arr_unique);
            } else {
                $productpieces = $arr_unique;
            }

            $productCollection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('sku', array('in' => $pieces))->addAttributeToSelect('price')->addAttributeToSelect('name');

            if ($productCollection) {
                foreach ($productCollection as $theProduct) {
                    $discounted = $this->getDiscounted($productIdListOriginal[0], $theProduct);
                    $this->writeToCsv($fp, $theProduct, $discounted);
                }
            }
            fclose($fp);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getDiscounted($productIdListOriginal, $theProduct)
    {
        $discounted = '';
        if ($productIdListOriginal['simple_action'] == 'by_percent') {
            $discounted = ($theProduct->getPrice() / 100) * (100 - $productIdListOriginal['discount_amount']);
        } elseif ($productIdListOriginal['simple_action'] == 'to_percent') {
            $discounted = ($theProduct->getPrice() / 100) * $productIdListOriginal['discount_amount'];
        } elseif ($productIdListOriginal['simple_action'] == 'by_fixed') {
            $discounted = $theProduct->getPrice() - $productIdListOriginal['discount_amount'];
        } elseif ($productIdListOriginal['simple_action'] == 'to_fixed') {
            $discounted = $productIdListOriginal['discount_amount'];
        }
        return $discounted;
    }

    public function writeToCsv($fp, $_product, $discounted)
    {
        fputcsv($fp, Array(
            $_product->getId(),
            $_product->getName(),
            $_product->getSku(),
            $_product->getPrice(),
            number_format((float)$discounted, 2, '.', ''),
        ));
    }

    public function getFilter($testConditional)
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