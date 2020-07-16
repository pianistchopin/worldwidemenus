<?php
class AdjustWare_Icon_Model_Mysql4_Attribute extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('adjicon/attribute', 'id');
    }

    public function getIconsByOptions($ids, $storeId)
    {    
        $db = $this->_getReadAdapter();
        $sql = $db->select()
            ->from(array('o' => $this->getTable('adjicon/icon')), array('o.filename'))
            ->joinRight(array('v' => $this->getTable('eav/attribute_option_value')), 'o.option_id = v.option_id', array('v.option_id', 'v.value','v.store_id'))
			->joinLeft(array('c' => $this->getTable('adjicon/color')), 'v.option_id = c.option_id', array('c.color'))
            ->where('v.option_id IN(?)', $ids)
            ->where('v.store_id IN(\'0\',?)', $storeId);
        $result = $db->fetchAll($sql);

        $icons = array();
        foreach ($result as $row){
            $id = $row['option_id'];
            if (empty($icons[$id]) || $row['store_id'])
                $icons[$id] = array($row['value'], $row['filename'], $row['color']);
        }
            
        return $icons;
    }
    
    public function getOptions($attributeId)
    {
        $db = $this->_getReadAdapter();
        $sql = $db->select()
            ->from(array('a'=>$this->getTable('eav/attribute_option')), array())
            ->joinInner(array('v' => $this->getTable('eav/attribute_option_value')), 'a.option_id = v.option_id', array('v.value', 'v.option_id'))
            ->joinLeft(array('i' => $this->getTable('adjicon/icon')), 'v.option_id = i.option_id', array('i.icon_id', 'i.filename'))
            ->joinLeft(array('c' => $this->getTable('adjicon/color')), 'v.option_id = c.option_id', array('c.color_id', 'c.color'))
            ->where('a.attribute_id = ?', $attributeId)
            ->where('v.store_id = 0')  //default values
            ->order('v.value');
        $result = $db->fetchAll($sql);
		foreach($result as &$row) {
			$row['color'.$row['option_id']] = $row['color'];
			unset($row['color']);
		}
        return $result;
    }

    public function getAttributeOptions($attributeId)
    {
        $db = $this->_getReadAdapter();
        $sql = $db->select()
            ->from(array('a'=>$this->getTable('eav/attribute_option')), array())
            ->joinInner(array('v' => $this->getTable('eav/attribute_option_value')), 'a.option_id = v.option_id', array('v.value', 'v.option_id'))
            ->joinLeft(array('i' => $this->getTable('adjicon/icon')), 'v.option_id = i.option_id', array('i.icon_id', 'i.filename'))
            ->joinLeft(array('c' => $this->getTable('adjicon/color')), 'v.option_id = c.option_id', array('c.color_id', 'c.color'))
            ->where('a.attribute_id = ?', $attributeId)
            ->where('v.store_id = 0')
            ->order('cast(\'v.value\' as unsigned)');
        $result = $db->fetchAll($sql);
        $icons = array();
        foreach ($result as $row){
            $id = $row['option_id'];
            if (empty($icons[$id]))
                $icons[$id] = array('label' => $row['value'], 'icon' => $row['filename'], 'color' => $row['color']);
        }
        return $icons;
    }

	public function getOptionById($optionId, $attributeId)
	{
		$db = $this->_getReadAdapter();
		$sql = $db->select()
			->from(array('a'=>$this->getTable('eav/attribute_option')), array())
			->joinInner(array('v' => $this->getTable('eav/attribute_option_value')), 'a.option_id = v.option_id', array('v.value', 'v.option_id'))
			->joinLeft(array('i' => $this->getTable('adjicon/icon')), 'v.option_id = i.option_id', array('i.icon_id', 'i.filename'))
			->where('a.attribute_id = ?', $attributeId)
			->where('a.option_id = ?', $optionId)
			->where('v.store_id = 0')  //default values
			->order('v.value');
		$result = $db->fetchRow($sql);
		return $result;
	}

	public function getColorOptions($attributeId)
	{
		$db = $this->_getReadAdapter();
		$sql = $db->select()
			->from(array('a'=>$this->getTable('eav/attribute_option')), array())
			->joinInner(array('v' => $this->getTable('eav/attribute_option_value')), 'a.option_id = v.option_id', array('v.option_id'))
			->joinLeft(array('c' => $this->getTable('adjicon/color')), 'v.option_id = c.option_id', array('c.color'))
			->where('a.attribute_id = ?', $attributeId)
			->where('v.store_id = 0')  //default values
			->order('v.value');
		$result = $db->fetchAssoc($sql);

		$options = array();
		foreach($result as $key => $row) {
			$options['color'.$key] = $row['color'];
		}
		return $options;
	}
    
    public function getAvailableAttributes()
    {    
        $db = $this->_getReadAdapter();
        $sql = $db->select()
            ->from(array('a'=>$this->getTable('eav/attribute')), array('a.frontend_label', 'a.attribute_id'))
            ->joinLeft(array('i' => $this->getTable('adjicon/attribute')), 'a.attribute_id = i.attribute_id', array())
            ->where('a.frontend_input IN (?)', array('select','multiselect'))
            ->where('a.entity_type_id = ?', Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getEntityTypeId())
            ->where('i.id IS NULL');

        $result = $db->fetchAll($sql);  

        return $result;
    }
}