<?php
class Aitoc_Aitcg_Helper_Accessory extends Aitoc_Aitcg_Helper_Abstract
{
	public function getAccessoryHtml($ids = null)
	{
		$model = Mage::getModel('aitcg/mask_category');
		$collection = $model->getCollection();
		$return = '';
		if ($ids !== null) {
			$collection->addFieldToFilter('id', array('in' => explode(',', $ids)) );
		}

		$return .= '<div class="accessories-options">Corner Protectors <input type="number" name="accessory-corners" class="accessory-category" /><span>Quantity</span></div>';
		$return .= '<div class="accessories-options">Pockets <input type="number" name="accessory-pockets" class="accessory-category" /><span>Quantity</span></div>';
		$return .= '<div class="accessories-options">Screw Fixings <input type="number" name="accessory-screw" class="accessory-category" /><span>Quantity</span></div>';
		$return .= '<div class="accessories-options">Floating Frame <input type="number" name="accessory-frame" class="accessory-category" /><span>Quantity</span></div>';

		return $return;
	}

	public function getSpineHtml($ids = null)
	{
		$return = '';

		$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'color');
		$options = Mage::getModel('eav/entity_attribute_source_table')
		               ->setAttribute($attribute)
		               ->getAllOptions(false);

		foreach ($options as $option) {
			$return .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
		}
		
		return $return;
	}

	public function getSpineId()
	{
		$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'color');
		return $attribute->getId();
	}
}