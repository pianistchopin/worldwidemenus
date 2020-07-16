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
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Block_Qquoteadv_History
 */
class Ophirah_Qquoteadv_Block_Qquoteadv_History extends Mage_Core_Block_Template
{
    /**
     * @var
     */
    private $_requestData;

    /**
     * @var
     */
    private $_filteredQuotes;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('qquoteadv/qquoteadv/history.phtml');

        $this->getQuotesWithUserFilter();
        $this->getFilteredQuotes()->setOrder('created_at', 'desc');

        $this->setQquotesadv($this->getFilteredQuotes());
        Mage::app()->getFrontController()
            ->getAction()
            ->getLayout()
            ->getBlock('root')
            ->setHeaderTitle(Mage::helper('qquoteadv')->__('My Quotes'));
    }

    /**
     * Prepare the quote history layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'qquoteadv.history.pager')
            ->setCollection($this->getQquotesadv());
        $this->setChild('pager', $pager);
        $this->getQquotesadv()->load();

        return $this;
    }

    /**
     * This function filters the collection of Qqadvcustomer objects.
     * @return object
     */
    public function getQuotesWithUserFilter()
    {
        if ($this->isUserFilterSet()) {
            $this->filterLike('increment_id', 'id');
            $this->filterLike('firstname', 'firstname');
            $this->filterLike('lastname', 'lastname');
            $this->filterDate();
            $this->filterStatus();
        }

        return $this;
    }

    /**
     * If possible the collect of Qqadvcustomer objects are filtered by date.
     * @return object
     */
    private function filterDate()
    {
        $from = $this->getRequestData('from');
        $to = $this->getRequestData('to');

        if (!empty($from) && !empty($to)) {
            $this->filterDateFromTo();
        } elseif (!empty($from)) {
            $this->filterLike('created_at', 'from');
        } elseif (!empty($to)) {
            $this->filterLike('created_at', 'to');
        }

        return $this;
    }

    /**
     * Filter the collect of Qqadvcustomer objects by form key from and to.
     * @return object
     */
    private function filterDateFromTo()
    {
        $this->getFilteredQuotes()->addFieldToFilter('created_at', array(
            'from' => $this->getRequestData('from'),
            'to'   => $this->getRequestData('to'),
            'date' => true
        ));

        return $this;
    }

    /**
     * Filter to find quotes with comparable request data
     *
     * @param $filterField
     * @param $requestDataParam
     */
    private function filterLike($filterField, $requestDataParam)
    {
        $this->getFilteredQuotes()->addFieldToFilter($filterField, array(
                'like' => '%' . $this->getRequestData($requestDataParam) . '%'
            )
        );
    }

    /**
     * If possible this function will filter the status of a collection of Qqadvcustomer objects.
     * If the status is 'saved' or 'begin' then we query Qqadvcustomer objects with both status.
     *
     * @return $this
     */
    private function filterStatus()
    {
        $status = $this->getRequestData('status');

        if (!empty($status)) {
            if ($status != Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED
                && $status != Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN
            ) {
                $this->getFilteredQuotes()->addFieldToFilter('status', $status);
            } else {
                $this->getFilteredQuotes()->addFieldToFilter(
                    array(
                        'status',
                        'status'
                    ),
                    array(
                        array('like' => '%' . Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED . '%'),
                        array('like' => '%' . Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN . '%')
                    )
                );
            }
        }

        return $this;
    }

    /**
     * This function builds the sort list for the available status.
     * @return string
     */
    public function getSortListForStatus()
    {
        $options = Mage::getSingleton('qquoteadv/status')->getOptionArray();
        $html = '<option value="" >' . Mage::helper('qquoteadv')->__('-- Quote Status --') . '</option>';

        foreach ($options as $key => $option) {
            if ($key != Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED
                && $key != Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN
            ) {
                $html .= '<option value="' . $key . '" >' . $this->__($option) . '</option>';
            } else {
                if (strpos($html, Mage::helper('qquoteadv')->__('In Process')) !== false) {
                    $html .= '<option value="' . $key . '" >' . $this->__('In Process') . '</option>';
                }
            }
        }
        return $html;
    }

    /**
     * Returns the pager block html
     *
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Returns the view quote url
     *
     * @param $qquote
     * @return mixed
     */
    public function getViewUrl($qquote)
    {
        return $this->getUrl('*/view/view', array('id' => $qquote->getId()));
        //return $this->getUrl('*/proposal/view', array('id' => $qquote->getId()));
    }

    /**
     * Returns the track url
     *
     * @param $qquote
     * @return mixed
     */
    public function getTrackUrl($qquote)
    {
        return $this->getUrl('*/*/track', array('order_id' => $qquote->getId()));
    }

    /**
     * Returns the reorder url
     *
     * @param $qquote
     * @return mixed
     */
    public function getReorderUrl($qquote)
    {
        return $this->getUrl('*/*/reorder', array('order_id' => $qquote->getId()));
    }

    /**
     * Returns the back url
     *
     * @return mixed
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Get status label based on id
     *
     * @param $id
     * @return mixed
     */
    public function getStatusLabel($id)
    {
        return Mage::helper('qquoteadv')->getStatus($id);
    }

    /**
     * Checks if there is a filter setting active.
     * @return bool
     */
    public function isUserFilterSet(){
        $id = $this->getRequestData('id');
        $form = $this->getRequestData('from');
        $to = $this->getRequestData('to');
        $status = $this->getRequestData('status');

        if(
            !empty($id)
            || !empty($form)
            || !empty($to)
            || !empty($status)
        ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the request data and sets it to the local var
     *
     * @param $param
     * @return mixed
     */
    public function getRequestData($param){
        if(empty($this->_requestData)){
            $this->_requestData = Mage::app()->getRequest();
        }
        return $this->_requestData->getParam($param);
    }

    /**
     * Get all quotes with a higher status than the begin status
     *
     * @return mixed
     */
    public function getFilteredQuotes(){
        if(empty($this->_filteredQuotes)){
            $this->_filteredQuotes =  Mage::getResourceModel('qquoteadv/qqadvcustomer_collection')
                ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId())
                ->addFieldToFilter('is_quote', 1)
                ->addFieldToFilter('status', array('gt' => Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN));
        }
        return $this->_filteredQuotes;
    }

    /**
     * Get the css of the filter by page load.
     *
     * @return string
     */
    public function getShowFilter(){
        $showFilter = '';
        if(!$this->isUserFilterSet()){
            $showFilter = 'display: none';
        }
        return $showFilter;
    }

    /**
     * Get no result text.
     *
     * @return string
     */
    public function getUserFilterSet(){
        if($this->isUserFilterSet()){
            $userFilterSet = Mage::helper('catalogsearch')->__('Your search returns no results.');
        }else{
            $userFilterSet = Mage::helper('qquoteadv')->__('You have placed no quotes.');
        }
        return $userFilterSet;
    }
}
