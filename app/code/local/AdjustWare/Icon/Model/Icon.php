<?php
class AdjustWare_Icon_Model_Icon extends Mage_Core_Model_Abstract
{
    protected $_iconPath;

    protected $_resizeOptions = array();
	protected $_location = array('l' => 'layered_height', 'pl' => 'list_height', 'v' => 'view_height', 'o' => 'option_height');

    public function _construct()
    {
        parent::_construct();
        $this->_init('adjicon/icon');
        $this->_iconPath = Mage::getBaseDir('media') . DS . 'icons' . DS;

        $this->setResizeOptions($this->_getDafaultResizeOptions());
    }

	public function upload($attributeId, $attributeOptionInfo)
    {
        $fieldName = 'option_' . $attributeOptionInfo['option_id'];
		if (empty($_FILES[$fieldName]['name'])) {
            return $this;
        }
        
        // create a human readable name
        $iconName = $attributeId . '_' . $attributeOptionInfo['option_id'] . '_';  // for better debug/maintenance
        $iconName .= preg_replace('/[^a-z0-9]+/', '', strtolower($attributeOptionInfo['value']));
        $iconName .= '_' . rand(0, 99); // to prevent browser cache
        $iconName .= '.' . strtolower(substr(strrchr($_FILES[$fieldName]['name'], '.'), 1)); // keep original extension

        //upload an icon file
        $uploader = new Varien_File_Uploader($fieldName);
        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $uploader->save($this->_iconPath, $iconName);

        // create and save thumbnail
        $this->makeThumb($iconName);


        // store new values in DB
        $oldFile = $this->getFilename();
        if ($oldFile){
            @unlink($this->_iconPath . $oldFile);
            @unlink($this->_iconPath . 'l_' . $oldFile);
			@unlink($this->_iconPath . 'pl_' . $oldFile);
			@unlink($this->_iconPath . 'v_' . $oldFile);
            @unlink($this->_iconPath . 'o_' . $oldFile);
        }
        $this->setOptionId($attributeOptionInfo['option_id']);
        $this->setFilename($iconName);
        $this->save();
    }
    
    public function makeThumb($iconName = false)
    {
        if (!$iconName) {
            $iconName = $this->getFilename();
        }
        if (!$this->_resizeIcon($iconName)){
            $this->copyThumb($iconName);
        }
    }
    
    public function copyThumb($iconName)
    {
		foreach($this->_location as $key => $value) {
			@copy($this->_iconPath . $iconName, $this->_iconPath . $key . '_' .$iconName);
			@chmod($this->_iconPath . $key . '_' .$iconName, 0644);
		}
    }

    protected function _getDafaultResizeOptions()
    {                
        return array (
            'layered_height'=> intVal(Mage::getStoreConfig('design/adjicon/layered_size')) < 1 ? null : intVal(Mage::getStoreConfig('design/adjicon/layered_size')),
			'list_height'=> intVal(Mage::getStoreConfig('design/adjicon/list_size')) < 1 ? null : intVal(Mage::getStoreConfig('design/adjicon/list_size')),
			'view_height'=> intVal(Mage::getStoreConfig('design/adjicon/product_size')) < 1 ? null : intVal(Mage::getStoreConfig('design/adjicon/product_size')),
            'option_height'=> intVal(Mage::getStoreConfig('design/adjicon/option_size')) < 1 ? null : intVal(Mage::getStoreConfig('design/adjicon/option_size'))
        );
    }
    
    public function setResizeOptions($options)
    {
        $this->_resizeOptions = array_merge($this->_resizeOptions, $options);
        return $this;
    }
    
    protected function _getResizeParam($param)
    {
        if (isset ($this->_resizeOptions[$param])) {
            return $this->_resizeOptions[$param];
        }
        return null;
    }

    protected function _resizeIcon($iconName)
    {
        if (!($this->_getResizeParam('layered_height') && $this->_getResizeParam('list_height')
            && $this->_getResizeParam('option_height') && $this->_getResizeParam('view_height'))) {
            return false;
        }

		foreach($this->_location as $key => $value) {
			$image = new Varien_Image($this->_iconPath . $iconName);
			$image->keepFrame(false);
			$image->keepAspectRatio(true);
			$image->keepTransparency(true);
			$image->resize(null, $this->_getResizeParam($value));
			$image->save(null, $key. '_' .$iconName);
		}

        return true;
    }
}