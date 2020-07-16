<?php
class Aitoc_Aitcg_Helper_Mask_Category extends Aitoc_Aitcg_Helper_Abstract
{
    public function getMaskCatsOptionHtml($ids = null)
    {
        $model = Mage::getModel('aitcg/mask_category');
        $collection = $model->getCollection();
        $return = '';
        if ($ids !== null) {
            $collection->addFieldToFilter('id', array('in' => explode(',', $ids)) );
        }

        foreach ($collection->load() as $category) {
			$return .= '\'<option value="' . $category->getId() . '">' . htmlentities($category->getName(), ENT_QUOTES) . '</option>\'+' . "\r\n";
        }

        return $return;
    }

	public function getMaskCatsRadioHtml($ids = null)
	{
		$model = Mage::getModel('aitcg/mask_category');
		$collection = $model->getCollection();
		$return = '';
		if ($ids !== null) {
			$collection->addFieldToFilter('id', array('in' => explode(',', $ids)) );
		}

		foreach ($collection->load() as $category) {
			$return .= '<div class="maskradio">' . htmlentities($category->getName(), ENT_QUOTES) . ' <input type="radio" value="'.$category->getId().'" name="masks-category-selector" class="masks-category" /></div>';
		}

		return $return;
	}
    
    public function getCategoryMaskRadio($category_id, $rand)
    {
            $maskCollection = Mage::getModel('aitcg/mask')->getCollection()
                    ->addFieldToFilter('category_id',$category_id)
                    ->addFieldToFilter('filename', array('neq' => ''))->setOrder('name', 'ASC');
            $return = '';
            foreach($maskCollection->load() as $mask)
            {
                $imagepath=$mask->getImagesUrl().'preview/'.$mask->getFilename();
                if(!empty($mask->getName()))
                {
                    $ext = pathinfo($mask->getFilename(), PATHINFO_EXTENSION);
                    $filename=Mage::helper('aitcg')->getSlug($mask->getName()).'.'.$ext;
                    if(file_exists($mask->getImagesPath().'preview/'.$filename))
                    {
                        $imagepath=$mask->getImagesUrl().'preview/'.$filename;
                    }

                }
                $return .= '<div><input type="radio" value="'.$mask->getId().'" name="mask'.$rand.'" />'.
                        '<img src="'.$imagepath.'" /></div>';
            }
    
            return $return;
    }
    
    public function copyPredefinedImage($id)
    {
        $path = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'mask' . DS . 'alpha' . DS;
        $image = Mage::getModel('aitcg/mask')->load($id);
        
        $fileName = $image->getFilename();
        $fileNameExploded = explode('.',$fileName);
        $ext = '.'.array_pop($fileNameExploded);
        
        $filename = Mage::helper('aitcg')->uniqueFilename($ext);
        while(file_exists($path.$filename))
        {
            $filename = Mage::helper('aitcg')->uniqueFilename($ext);
        }
        
        @copy($image->getImagesPath().$image->getFilename(),$path.$filename);
        return $filename;
    }
}