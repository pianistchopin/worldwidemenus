<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Mysql4_Group_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	/**
	 * Constructor method
	 */
	protected function _construct() {
		$this->_init('flexslider/group');
	}

	/**
	 * Add Filter by GroupCode
	 *
	 * @param string $position
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addGroupCodeFilter($code) {
		$this->getSelect()->where('main_table.code = ?', $code);
		return $this;
	}

	/**
	 * Add Filter by position
	 *
	 * @param string $position
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addPositionFilter($position) {
		$this->getSelect()->where('main_table.position = ?', $position);
		return $this;
	}

	/**
	 * Add Filter by sort order
	 *
	 * @param string $sort_order
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addSortFilter($sort_order = 'ASC') {
		$this->getSelect()->order('main_table.sort_order ' . $sort_order);
		return $this;
	}

	/**
	 * Add Filter by category
	 *
	 * @param int $category
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addCategoryFilter($category) {
		$this->getSelect()->join(
				array('category_table' => $this->getTable('flexslider/flexslider_category')),
				'main_table.group_id = category_table.group_id',
				array()
				)
				->where('category_table.category_id = ?', $category);
		return $this;
	}

	/**
	 * Add Filter by page
	 *
	 * @param int $page
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addPageFilter($page) {
		$this->getSelect()->join(
				array('page_table' => $this->getTable('flexslider/flexslider_page')),
				'main_table.group_id = page_table.group_id',
				array()
				)
				->where('page_table.page_id = ?', $page);
		return $this;
	}
	
	/**
	 * Add Filter by product
	 *
	 * @param int $product
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addProductFilter($product) {
		$this->getSelect()->join(
				array('product_table' => $this->getTable('flexslider/flexslider_product')),
				'main_table.group_id = product_table.group_id',
				array()
				)
				->where('product_table.product_sku = ?', $product);
		return $this;
	}

	/**
	 * Add Filter by store
	 *
	 * @param int|Mage_Core_Model_Store $store
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addStoreFilter($store) {
		if (!Mage::app()->isSingleStoreMode()) {
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}

			$this->getSelect()->join(
					array('store_table' => $this->getTable('flexslider/flexslider_store')),
					'main_table.group_id = store_table.group_id',
					array()
					)
					->where('store_table.store_id in (?)', array(0, $store));
			return $this;
		}
		return $this;
	}

	/**
	 * Add by date enabled slides
	 *
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addGroupDateFilter() {
		return $this->addFieldToFilter('slider_startdate',
											array(
												array('to' => Mage::getModel('core/date')->date('Y-m-d H:i:s')),
												array('slider_startdate', 'null'=>'')
											)
										)
					->addFieldToFilter('slider_enddate',
											array(
												array('gteq' => Mage::getModel('core/date')->date('Y-m-d H:i:s')),
												array('slider_enddate', 'null'=>'')
											)
										);
	}
	
	/**
	 * Add Filter by status
	 *
	 * @param int $status
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Group_Collection
	 */
	public function addEnableFilter($status = 1) {
		$this->getSelect()->where('main_table.is_active = ?', $status);
		return $this;
	}

}