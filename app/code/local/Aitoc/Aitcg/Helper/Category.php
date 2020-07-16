<?php
class Aitoc_Aitcg_Helper_Category extends Aitoc_Aitcg_Helper_Abstract
{

	public $catsOptionCount = 0;

    public function getPredefinedCatsOptionHtml($ids = null)
    {
        $model = Mage::getModel('aitcg/category');
        $collection = $model->getCollection();
        $return = '';
        if ($ids !== null) {
            $collection->addFieldToFilter('category_id', array('in' => explode(',' ,$ids)) );
        }

	    $this->catsOptionCount = 0;
        foreach ($collection->load() as $category) {
            $return .= '\'<option value="' . $category->getCategoryId() . '">' . htmlentities($category->getName(), ENT_QUOTES) . '</option>\'+' . "\r\n";
            $this->catsOptionCount++;
        }
        
        return $return;
    }

    public function getCatsOptionCount()
    {
    	return $this->catsOptionCount;
    }
    
    public function getCategoryImagesRadio($category_id, $rand)
    {
            $imageCollection = Mage::getModel('aitcg/category_image')->getCollection()
                    ->addFieldToFilter('category_id', $category_id)
                    ->addFieldToFilter('filename', array('neq' => ''));
            $return = '';
            foreach($imageCollection->load() as $image)
            {
                $return .= '<div style="position:relative;" id="swatch_'.$image->getCategoryImageId().'"><input type="radio" value="'.$image->getCategoryImageId().'" name="predefined-image'.$rand.'" data-catid="'.$image->getCategoryId().'"/>'.
                        '<img src="'.$image->getImagesUrl().'preview/'.$image->getFilename().'" /><span class="swatch_label">'.$image->getName().'</span></span></div>';
            }
    
            return $return;
    }
    public function copyEmbossPredefinedImage($id, $size = '')
    {
        $path = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
        $image = Mage::getModel('aitcg/category_image')->load($id);

        $fileName = $image->getEmbossfilename();
        if(empty($fileName) || !file_exists($image->getEmbossImagesPath().$image->getEmbossfilename()))
        {
            if(file_exists($image->getImagesPath() . 'preview' . DS . $image->getFilename())){
                    return $image->getImagesUrl() . 'preview' . DS . $image->getFilename();

            }

        }

        $fileNameExploded = explode('.',$fileName);
        $ext = '.'.array_pop($fileNameExploded);
        $filename = Mage::helper('aitcg')->uniqueFilename($ext);
        while(file_exists($path.$filename))
        {
            $filename = Mage::helper('aitcg')->uniqueFilename($ext);
        }

        @copy($image->getEmbossImagesPath(). 'preview' . DS.$image->getEmbossfilename(),$path.$filename);
        list($width, $height) = getimagesize($path.$filename);

        if (strpos($size,'A4') !== false) {

            $w = 595; $h = 842; $cropW = 0; $cropH = 0;
            if($width > $w) {
                $cropW = $width - $w;
            }
            if($height > $h) {
                $cropH = $height - $h;
            }

            $imageObj = new Varien_Image($path.$filename);
            $imageObj->crop(0,0, $cropW, $cropH);
            $imageObj->save($path.$filename);
        }

        if (strpos($size,'A5') !== false) {

            $w = 421; $h = 595; $cropW = 0; $cropH = 0;
            if($width > $w) {
                $cropW = $width - $w;
            }
            if($height > $h) {
                $cropH = $height - $h;
            }

            $imageObj = new Varien_Image($path.$filename);
            $imageObj->crop(0,0, $cropW, $cropH);
            $imageObj->save($path.$filename);
        }

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'custom_product_preview/quote/'
            . $filename;
    }

    public function copyPredefinedImage($id, $size = '')
    {
        $path = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
        $image = Mage::getModel('aitcg/category_image')->load($id);
        
        $fileName = $image->getFilename();
        $fileNameExploded = explode('.',$fileName);
        $ext = '.'.array_pop($fileNameExploded);
        
        $filename = Mage::helper('aitcg')->uniqueFilename($ext);
        while(file_exists($path.$filename))
        {
            $filename = Mage::helper('aitcg')->uniqueFilename($ext);
        }

	    @copy($image->getImagesPath().$image->getFilename(),$path.$filename);
	    list($width, $height) = getimagesize($path.$filename);

	    if (strpos($size,'A4') !== false) {

	    	$w = 595; $h = 842; $cropW = 0; $cropH = 0;
	    	if($width > $w) {
			    $cropW = $width - $w;
		    }
		    if($height > $h) {
			    $cropH = $height - $h;
		    }

		    $imageObj = new Varien_Image($path.$filename);
		    $imageObj->crop(0,0, $cropW, $cropH);
		    $imageObj->save($path.$filename);
	    }

	    if (strpos($size,'A5') !== false) {

		    $w = 421; $h = 595; $cropW = 0; $cropH = 0;
		    if($width > $w) {
			    $cropW = $width - $w;
		    }
		    if($height > $h) {
			    $cropH = $height - $h;
		    }

		    $imageObj = new Varien_Image($path.$filename);
		    $imageObj->crop(0,0, $cropW, $cropH);
		    $imageObj->save($path.$filename);
	    }

	    return $filename;
    }
}