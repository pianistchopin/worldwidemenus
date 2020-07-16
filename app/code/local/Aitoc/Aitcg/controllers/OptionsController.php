<?php

class Aitoc_Aitcg_OptionsController extends Mage_Adminhtml_Controller_Action
{
    public function massCopyAction()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);
        $copyFromProductId = (int)$this->getRequest()->getParam('copy_from_product_id');

        try {
            $copyFromProduct = Mage::getModel('catalog/product')
                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                ->load($copyFromProductId);

            if (!$copyFromProduct->getId()) {
                throw new Mage_Core_Exception($this->__('Product ID does not exist.'));
            }

            if (!$copyFromProduct->getHasOptions()) {
                throw new Mage_Core_Exception($this->__('Product has no options.'));
            }

            $this->_writeToLog($this->__('Start log: %s', Mage::getSingleton('core/date')->gmtDate()), true);

            $success = 0;
            $errors  = 0;
            foreach ($productIds as $productId) {
                if ($this->_copyOptions($copyFromProduct, $productId)) {
                    $success++;
                } else {
                    $errors++;
                }
            }

            if ($success) {
                $this->_getSession()->addSuccess(
                    $this->__('The custom options have been copied successfully for %d products. '
                        . '<a href="'. Mage::getBaseUrl('media') .'copy_cpp_options.txt" target="_blank">See log.</a>', $success)
                );
            }

            if ($errors) {
                $this->_getSession()->addError(
                    $this->__('An error has occurred for %d products. '
                        . '<a href="'. Mage::getBaseUrl('media') .'copy_cpp_options.txt" target="_blank">See log.</a>', $errors)
                );
            }
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }

        $this->_redirect('adminhtml/catalog_product/index', array('store'=> $storeId));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }

    protected function _copyOptions($copyFromProduct, $productId)
    {
        try {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                ->load($productId);
            if ($product->getId()) {

                // delete options
                $options = $product->getProductOptionsCollection();
                foreach ($options as $option) {
                    $option->getValueInstance()->deleteValue($option->getId());
                    $option->deletePrices($option->getId());
                    $option->deleteTitles($option->getId());
                    $option->delete();
                }
                $product->setHasOptions(0)
                    ->setRequiredOptions(0)
                    ->save();

                // copy options
                $copyFromProduct->getOptionInstance()->duplicate($copyFromProduct->getId(), $productId);

                // copy cgimage flag
                $copyFromImages = $copyFromProduct->getMediaGallery('images');
                $mediaGallery = $product->getMediaGallery();
                $images = $mediaGallery['images'];
                $imageTemplateIdBunch = array();
                foreach ($images as $key => &$image) {
                    if (isset($copyFromImages[$key]) && $copyFromImages[$key]['cgimage']) {
                        $image['cgimage'] = 1;
                    } else {
                        $image['cgimage'] = 0;
                    }
                    if (isset($copyFromImages[$key]['value_id'])) {
                        $imageTemplateIdBunch[$copyFromImages[$key]['value_id']] = $image['value_id'];
                    }
                }
                $mediaGallery['images'] = $images;
                $product->setData('media_gallery', $mediaGallery);

                // copy options flag
                $product->setHasOptions($copyFromProduct->getHasOptions())
                    ->setRequiredOptions($copyFromProduct->getRequiredOptions())
                    ->save();

                // copy catalog_product_option_aitimage information
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                    ->load($productId);
                $optionsCollection = $product->getProductOptionsCollection();
                $options = array();
                foreach ($optionsCollection as $option) {
                    $options[] = $option;
                }

                $copyFromOptionsCollection = $copyFromProduct->getProductOptionsCollection();
                $copyFromOptions = array();
                foreach ($copyFromOptionsCollection as $option) {
                    $copyFromOptions[] = $option;
                }

                foreach ($copyFromOptions as $index => $option) {
                    if ($option->getType() == Aitoc_Aitcg_Helper_Options::OPTION_TYPE_AITCUSTOMER_IMAGE
                        && isset($options[$index])
                        && isset($imageTemplateIdBunch[$option->getData('image_template_id')])
                    ) {
                        $data = array(
                            'option_id' => $options[$index]->getId(),
                            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID,
                            'image_template_id' => $imageTemplateIdBunch[$option->getData('image_template_id')],
                            'area_size_x' => $option->getData('area_size_x'),
                            'area_size_y' => $option->getData('area_size_y'),
                            'area_offset_x' => $option->getData('area_offset_x'),
                            'area_offset_y' => $option->getData('area_offset_y'),
                            'use_text' => $option->getData('use_text'),
                            'use_user_image' => $option->getData('use_user_image'),
                            'use_digital_image' => $option->getData('use_digital_image'),
                            'spread_type' => $option->getData('spread_type'),
                            'use_predefined_image' => $option->getData('use_predefined_image'),
                            'predefined_cats' => $option->getData('predefined_cats'),
                            'text_length' => $option->getData('text_length'),
                            'allow_colorpick' => $option->getData('allow_colorpick'),
                            'allow_text_distortion' => $option->getData('allow_text_distortion'),
                            'allow_predefined_colors' => $option->getData('allow_predefined_colors'),
                            'color_set_id' => $option->getData('color_set_id'),
                            'use_masks' => $option->getData('use_masks'),
                            'masks_cat_id' => $option->getData('masks_cat_id'),
                            'mask_location' => $option->getData('mask_location'),
                            'def_img_behind_text' => $option->getData('def_img_behind_text'),
                            'def_img_behind_image' => $option->getData('def_img_behind_image'),
                            'def_img_behind_clip' => $option->getData('def_img_behind_clip'),
                            'allow_save_graphics' => $option->getData('allow_save_graphics'),
                            'scale_image' => $option->getData('scale_image')
                        );

                        $resource = Mage::getSingleton('core/resource');
                        $connection = $resource->getConnection('core_write');
                        $connection->insertOnDuplicate($resource->getTableName('catalog_product_option_aitimage'), $data);
                    }
                }

                $this->_writeToLog($this->__('ID: %d, Name: %s - Success', $product->getId(), $product->getName()));
                return true;
            }
        } catch (Exception $e) {
            $this->_writeToLog($this->__('ID: %d, Name: %s - Error', $productId, $product->getName()));
            $this->_getSession()->addError($e->getMessage());
        }

        return false;
    }

    protected function _writeToLog($text, $overwrite = false)
    {
        $io = new Varien_Io_File();
        $path = Mage::getBaseDir('media') . DS;
        $fileName = 'copy_cpp_options.txt';
        $file = $path . $fileName;

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, ($overwrite ? 'w+' : 'a+'));
        $io->streamLock(true);
        $io->streamWrite($text . "\r\n");
        $io->streamClose();
    }
}
