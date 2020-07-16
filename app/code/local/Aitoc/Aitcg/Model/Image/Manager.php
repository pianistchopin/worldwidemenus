<?php

class Aitoc_Aitcg_Model_Image_Manager extends Mage_Core_Model_Abstract
{

    protected function _getAllowedExtensions()
    {
        // File extension
        $ait_extensions = trim(Mage::getStoreConfig('catalog/aitcg/aitcg_image_extensions'));

        if ($ait_extensions == '') {
            $ait_extensions = 'jpg, jpeg, png, gif';
        }

        preg_match_all('/[a-z0-9]+/si', strtolower($ait_extensions), $matches);
        if (isset($matches[0]) && is_array($matches[0]) && count($matches[0]) > 0) {
            return $matches[0];
        }

        return null;
    }


    public function addImage($img_data, $product_id, $option_id, $option = null)
    {
        $model   = Mage::getModel('aitcg/image');
        $product = Mage::getModel('catalog/product');
        $product->load($product_id);
        /** @var $option Aitoc_Aitcg_Model_Rewrite_Catalog_Product */

        if ($option_id == 0 || $product_id == 0 || $product->isEmpty()) {
            if ($option == null) {
                throw new Exception(Mage::helper('aitcg')->__('Invalid data recieved'));
            }
        } else {
            $option = $product->getOptionById(intval($option_id));
        }

        $model->setData('create_time', now())
            ->setData('image_template_id', $option->getData('image_template_id'))
            ->setData('area_size_x', $option->getData('area_size_x'))
            ->setData('area_size_y', $option->getData('area_size_y'))
            ->setData('area_offset_x', $option->getData('area_offset_x'))
            ->setData('area_offset_y', $option->getData('area_offset_y'))
            ->setData('use_text', $option->getData('use_text'))
            ->setData('use_user_image', $option->getData('use_user_image'))
            ->setData('spread_type', $option->getData('spread_type'))
            ->setData('use_digital_image', $option->getData('use_digital_image'))
            ->setData('use_predefined_image', $option->getData('use_predefined_image'))
            ->setData('predefined_cats', $option->getData('predefined_cats'))
            ->setData('use_masks', $option->getData('use_masks'))
            ->setData('use_instagram', $option->getData('use_instagram'))
            ->setData('use_pinterest', $option->getData('use_pinterest'))
            ->setData('use_black_white', $option->getData('use_black_white'))
            ->setData('masks_cat_id', $option->getData('masks_cat_id'))
            ->setData('mask_location', $option->getData('mask_location'))
            ->setData('allow_colorpick', $option->getData('allow_colorpick'))
            ->setData('text_length', $option->getData('text_length'))
            ->setData('allow_text_distortion', $option->getData('allow_text_distortion'))
            ->setData('allow_predefined_colors', $option->getData('allow_predefined_colors'))
            ->setData('color_set_id', $option->getData('color_set_id'))
            ->setData('def_img_behind_text', $option->getData('def_img_behind_text'))
            ->setData('def_img_behind_image', $option->getData('def_img_behind_image'))
            ->setData('def_img_behind_clip', $option->getData('def_img_behind_clip'))
            ->setData('allow_save_graphics', $option->getData('allow_save_graphics'))
            ->setData('img_data', $img_data)
            ->setData('scale_image', $option->getData('scale_image'));

        $model->save();

        return $model->getId();
    }

}