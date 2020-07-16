<?php
class Aitoc_Aitcg_Model_Rewrite_Catalog_Product extends Mage_Catalog_Model_Product
{
    public function getAitcgMediaGalleryImages()
    {
        if(!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($this->getMediaGallery('images') as $image) {
                if (!$image['cgimage']) {
                    continue;
                }
                $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
            if(!$images->count()){
                foreach ($this->getMediaGallery('images') as $image) {
                    $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                    $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                    $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                    $images->addItem(new Varien_Object($image));
                }
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }
}
