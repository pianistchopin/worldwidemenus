<?php
class Aitoc_Aitcg_Block_Checkout_Cart_Item_Option_Cgfile extends Mage_Core_Block_Template 
{
    protected $_isSecureConnection = false;
    
    public function _construct()
    {
        parent::_construct();
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $this->_isSecureConnection = true;
        }
        $this->setTemplate('aitcg/checkout/cart/item/option/cgfile.phtml');
    }
    
    public function getImgData()
    {
        $sImgData = parent::getImgData();

        $sImgData = str_replace("'", "&#039;", $sImgData);
        $sImgData = Mage::helper('aitcg')->getSecureUnsecureUrl($sImgData);
        return $sImgData;
    }
    
    public function getSaveSvgUrl()
    {
        if($this->_isSecureConnection) {
            return Mage::getUrl('aitcg/index/svg', array('_secure' => true));
        }    
        return Mage::getUrl('aitcg/index/svg');
    }   

    public function getReservedForSocialWidgetsImgId()
    {
        $reservedId = 0;
        $imgData = Mage::helper('core')->jsonDecode($this->getImgData());
        $imgData = $imgData['data'];

        foreach ($imgData as $key => $img)  {
            foreach ($img as $k => $val) {
                if (($k == 'social_widgets_reserved_img_id') && $val != 0) {
                    $reservedId = $val;
                    return $reservedId;
                }
            }
        }

        return false;
    }

    public function getSharedImgId($rand)
    {
        if ($id = $this->getReservedForSocialWidgetsImgId()) {
            return $id;
        }

        return Mage::helper('aitcg')->getSharedImgId($rand);
    }

    public function canEmailToFriend()
    {
        return Mage::helper('sendfriend')->isEnabled();
    }

    public function getSavePdfUrl($front = 'aitcg')
    {
        if (Mage::app()->getStore()->getConfig('catalog/aitcg/aitcg_enable_svg_to_pdf') == 1) {
            return Mage::getUrl($front.'/index/pdf');
        }
        return false;
    }
}
