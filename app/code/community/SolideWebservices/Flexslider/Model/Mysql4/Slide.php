<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Mysql4_Slide extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {
		$this->_init('flexslider/slide', 'slide_id');
	}

	/**
	 * Logic performed before saving the model
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return SolideWebservices_Flexslider_Model_Mysql4_Slide
	 */
	protected function _beforeSave(Mage_Core_Model_Abstract $object) {
		if (!$object->getGroupId()) {
			$object->setGroupId(null);
		}

		return parent::_beforeSave($object);
	}

	/**
	 * Retrieve the group model associated with the slide
	 *
	 * @param SolideWebservices_Flexslider_Model_Slide $slide
	 * @return SolideWebservices_Flexslider_Model_Group
	 */
	public function getGroup(SolideWebservices_Flexslider_Model_Slide $slide) {
		if ($slide->getGroupId()) {
			$group = Mage::getModel('flexslider/group')->load($slide->getGroupId());

			if ($group->getId()) {
				return $group;
			}
		}

		return false;
	}

}