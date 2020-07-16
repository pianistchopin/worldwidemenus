<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	protected function _construct() {
		$this->_init('flexslider/slide');
	}

	/**
	 * Init collection select
	 *
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection
	*/
	protected function _initSelect() {
		$this->getSelect()->from(array('main_table' => $this->getMainTable()));
		
		return $this;
	}

	/**
	 * Filter the collection by a group ID
	 *
	 * @param int $groupId
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection
	 */
	public function addGroupIdFilter($groupId) {
		return $this->addFieldToFilter('group_id', $groupId);
	}

	/**
	 * Filter the collection by enabled slides
	 *
	 * @param int $isEnabled = true
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection
	 */
	public function addIsEnabledFilter($isEnabled = true) {
		return $this->addFieldToFilter('is_enabled', $isEnabled ? 1 : 0);
	}
	
	/**
	 * Filter the collection by date enabled slides
	 *
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection
	 */
	public function addDateFilter() {
		return $this->addFieldToFilter('slide_startdate',
											array(
												array('to' => Mage::getModel('core/date')->date('Y-m-d H:i:s')),
												array('slide_startdate', 'null'=>'')
											)
										)
					->addFieldToFilter('slide_enddate',
											array(
												array('gteq' => Mage::getModel('core/date')->date('Y-m-d H:i:s')),
												array('slide_enddate', 'null'=>'')
											)
										);
	}

	/**
	 * Add a random order to the slides
	 *
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection
	*/
	public function addOrderByRandom($dir = 'ASC') {
		$this->getSelect()->order('RAND() ' . $dir);
		return $this;
	}

	/**
	 * Add order by sort order
	 *
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide_Collection
	*/
	public function addOrderBySortOrder($dir = 'ASC') {
		$this->getSelect()->order('sort_order ' . $dir);
		return $this;
	}

}