<?php
class AdjustWare_Icon_Model_Cpp extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjicon/cpp');
    }

    /*
     * @return bool
     */
    public function validateCPP()
    {
        $vyaImage = $this->_getVYAImage();
        $cppImage = $this->_getCPPImage();

        if(!$vyaImage || !$cppImage){
            return true;
        }

        return $this->_validate($vyaImage, $cppImage);
    }

    /*
     * @param string $img1
     * @param string $img2
     * @return bool
     */
    protected function _validate($img1, $img2)
    {
        list($width1, $height1) = getimagesize($img1);
        list($width2, $height2) = getimagesize($img2);

        if($width1 != $width2 || $height1 != $height2){
            return false;
        }

        return true;
    }

    /*
     * @return string
     */
    protected function _getVYAImage()
    {
        $vyaModel = Mage::getModel('adjicon/image');
        $vyaModel->load($this->getVyaImageId());
        $vyaImage = $vyaModel->getImagePath() . $vyaModel->getFile();
        if(empty($vyaImage)){
            return false;
        }

        return $vyaImage;
    }

    /*
     * @return string
     */
    protected function _getCPPImage()
    {
        $cppOptionId = $this->getCppOptionId();
        $optionModel = Mage::getModel('catalog/product_option')->load($cppOptionId);
        $productId = $optionModel->getProductId();

        $cppTemplateId = $this->_getResource()->getCPPTemplateId($cppOptionId);
        if(!$cppTemplateId){
            return false;
        }

        $cppImage = Mage::getSingleton('aitcg/resource_template')->getFullImageUrlForVYA($productId, $cppTemplateId);
        if(empty($cppImage)){
            return false;
        }

        return $cppImage;
    }

    /*
     * @return mixed
     */
    public function getCPPOptionTitle()
    {
        return $this->_getResource()->getCPPOptionTitle($this->getCppOptionId());
    }

    /*
     * @param AdjustWare_Icon_Model_Image $adjImageModel
     * @param integer $cppOptionId
     * @return AdjustWare_Icon_Model_Cpp
     */
    public function setAdjImagesCPPOptions(AdjustWare_Icon_Model_Image $adjImageModel, $cppOptionId)
    {
        $this->load($adjImageModel->getImageId(), 'vya_image_id');
        if(!$this->getId()){
            $this->setVyaImageId($adjImageModel->getImageId());
        }

        if($cppOptionId == ''){
            $this->delete();
        }
        else{
            $this->setCppOptionId($cppOptionId);
        }

        return $this;
    }
}