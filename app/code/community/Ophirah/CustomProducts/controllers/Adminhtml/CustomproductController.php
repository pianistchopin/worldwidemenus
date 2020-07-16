<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Cart2Quote
 * @package     CustomProducts
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 * @version     1.0.5
 */

/**
 * Controller class for the custom product.
 * Class Ophirah_CustomProducts_Adminhtml_CustomProductsController
 * @since 1.0.5
 */
class Ophirah_CustomProducts_Adminhtml_CustomproductController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Error created in the controller action.
     * @var bool | string
     * @since 1.0.5
     */
    protected $error = false;

    /**
     * Add product to quote action.
     * @return void
     * @internal param $c2qid
     * @since 1.0.5
     */
    public function addAction() {
        $c2qId = (int)$this->getRequest()->getParam('quoteadv_id');
        Mage::dispatchEvent('customproduct_adminhtml_before_add', array('quote_id' => $c2qId));

        $allowed = Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct();
        if(!$allowed){
            $errorMsg = $this->__("This function is only available in the Cart2Quote enterprise edition.
<a href='https://www.cart2quote.com/cart2quote-update-upgrade.html'>Upgrade</a>");
            $this->_setError($errorMsg);
            $this->_return($c2qId);
            return;
        }

        if(!isset($c2qId) || $c2qId == false){
            $this->_setError('FP01 - Quote Id not valid or set');
            $this->_return();
            return;
        }

        $params = $this->getRequest()->getParams();
        if(!array_key_exists('options', $params)){
            $this->_setError('FP02 - Params not valid');
            $this->_return($c2qId);
            return;
        }

        $model = Mage::getModel('customproducts/customproduct');
        $product = $model->getCustomProduct();
        if(!$product instanceof Mage_Catalog_Model_Product){
            $this->_setError('FP03 - Custom product is invalid.');
            $this->_return($c2qId);
            return;
        }

        $params = $model->prepareParams($params);
        if($params == false){
            $this->_setError('FP04 - Cannot prepare product params');
            $this->_return($c2qId);
            return;
        }

        $error = $model->addProductToQuote($product, $c2qId, $params);
        $this->_setError($error);
        $this->_return($c2qId);

        Mage::dispatchEvent('customproducts_adminhtml_after_add', array('quote_id' => $c2qId));
        return;
    }

    /**
     * Go back to the quote page or quote overview.
     * @return void
     * @param null $c2qId
     * @since 1.0.5
     */
    protected function _return($c2qId = null){
        $this->_displayMessage();
        if (!empty($c2qId)) {
            $this->_redirect('adminhtml/qquoteadv/edit', array('id' => $c2qId));
        } else {
            $this->_redirect('adminhtml/qquoteadv/index');
        }
    }

    /**
     * Displays a success or error.
     * @return void
     * @since 1.0.5
     */
    protected function _displayMessage(){
        if($this->hasError()){
            Mage::getSingleton('adminhtml/session')->addError($this->__('Can not save the custom quote product: ')
                .$this->getError());
        }else{
            Mage::getSingleton('adminhtml/session')->addSuccess($this->
            __('Custom quote product is successfully created.'));
        }
    }

    /**
     * Gets the controller error.
     * @return bool|string
     * @since 1.0.5
     */
    public function getError(){
        return $this->error;
    }

    /**
     * Sets an error.
     * @param $error
     * @return $this
     * @since 1.0.5
     */
    protected function _setError($error){
        if(is_string($error)){
            $this->error = $error;
        }
        return $this;
    }

    /**
     * Checks if the controller has an error.
     * @return bool
     * @since 1.0.5
     */
    protected function hasError(){
        if(!empty($this->error)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $aclResource = 'sales/qquoteadv/actions';
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }
}
