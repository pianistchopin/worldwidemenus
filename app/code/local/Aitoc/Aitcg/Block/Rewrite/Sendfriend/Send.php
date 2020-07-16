<?php
/**
* @copyright  Copyright (c) 2012 AITOC, Inc. 
*/

class Aitoc_Aitcg_Block_Rewrite_Sendfriend_Send extends Mage_Sendfriend_Block_Send
{
    /**
     * overwrite parent
     * enables sending custom images to a friend
     */
    public function getSendUrl()
    {
        $aitcgSharedImgId = $this->getRequest()->getParam('aitcg_shared_img_id');
        if(isset($aitcgSharedImgId))
        {
            return Mage::getUrl('*/*/sendmail', array(
                'id'     => $this->getProductId(),
                'cat_id' => $this->getCategoryId(),
                'aitcg_shared_img_id' => $aitcgSharedImgId
                ));
        }

        return parent::getSendUrl();
    }
}
