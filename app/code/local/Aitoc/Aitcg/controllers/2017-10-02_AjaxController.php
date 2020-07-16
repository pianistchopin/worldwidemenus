<?php

class Aitoc_Aitcg_AjaxController extends Mage_Core_Controller_Front_Action
{

    public function fontPreviewAction()
    {
        if ($this->getRequest()->isPost()) {
            $font_id          = Mage::app()->getRequest()->get('font_id');
            $rand             = Mage::app()->getRequest()->get('rand');
            $model            = Mage::getModel('aitcg/font')->load($font_id);
            $response         = array();
            $response['src']  = Mage::helper('aitcg/font')->getFontPreview(
                $model->getFontsPath() . $model->getFilename()
            );
            $response['rand'] = $rand;

            $this->_setBodyJson($response);
        }
    }

    public function masksCategoryAction()
    {
        if ($this->getRequest()->isPost()) {
            $category_id        = Mage::app()->getRequest()->get('category_id');
            $rand               = Mage::app()->getRequest()->get('rand');
            $response           = array();
            $response['images'] = Mage::helper('aitcg/mask_category')->getCategoryMaskRadio($category_id, $rand);
            $response['rand']   = $rand;

            $this->_setBodyJson($response);
        }
    }

    public function categoryPreviewAction()
    {
        if ($this->getRequest()->isPost()) {
            $category_id        = Mage::app()->getRequest()->get('category_id');
            $rand               = Mage::app()->getRequest()->get('rand');
            $response           = array();
            $response['images'] = Mage::helper('aitcg/category')->getCategoryImagesRadio($category_id, $rand);
            $response['rand']   = $rand;

            $this->_setBodyJson($response);
        }
    }

    public function addTextAction()
    {
        if ($this->getRequest()->isPost()) {
            $font          = Mage::app()->getRequest()->get('font');
            $color         = Mage::app()->getRequest()->get('color');
            $size        = Mage::app()->getRequest()->get('size');
            $text          = Mage::app()->getRequest()->get('text');
            $outline       = Mage::app()->getRequest()->get('outline');
            $curveText     = Mage::app()->getRequest()->get('curveText');
            $blackAndWhite = Mage::app()->getRequest()->get('useblackAndWhite');

            if (!empty($outline)) {
                $outline          = array();
                $outline['color'] = Mage::app()->getRequest()->get('coloroutline');
                $outline['wight'] = (Mage::app()->getRequest()->get('widthoutline') > 1
                    && is_numeric(
                        Mage::app()
                            ->getRequest()
                            ->get('widthoutline')
                    )) ? Mage::app()->getRequest()->get('widthoutline') : 1;
            }
            $shadow = Mage::app()->getRequest()->get('shadow');
            if (!empty($shadow)) {
                $shadow          = array();
                $shadow['alpha'] = Mage::app()->getRequest()->get('shadowalpha');
                $shadow['alpha'] = (is_numeric($shadow['alpha'])) ? ($shadow['alpha'] < 0 ? 0
                    : ($shadow['alpha'] > 126 ? 126 : $shadow['alpha'])) : 50;
                $shadow['x']     = Mage::app()->getRequest()->get('shadowoffsetx');
                $shadow['x']     = (is_numeric($shadow['x'])) ? $shadow['x'] : 20;
                $shadow['y']     = Mage::app()->getRequest()->get('shadowoffsety');
                $shadow['y']     = (is_numeric($shadow['y'])) ? $shadow['y'] : 20;
                $shadow['color'] = Mage::app()->getRequest()->get('colorshadow');
            }
            ($align = Mage::app()->getRequest()->get('alignFont')) ? '' : ($align = 'right');
            $model = Mage::getModel('aitcg/font')->load($font);
            if ($curveText) {
                $radius   = Mage::app()->getRequest()->get('radiusText'); //400 - curved text off
                $downText = Mage::app()->getRequest()->get('downText');
                if ($radius != 400) {
                    $radius   = 3500 - $radius;
                    $filename = Mage::helper('aitcg/font')->getCurveTextImage(
                        $model->getFontsPath() . $model->getFilename(), $text, $color, $radius, $downText, $align
                    );
                } else {
                    $filename = Mage::helper('aitcg/font')->getTextImage(
                        $model->getFontsPath() . $model->getFilename(), $text, $color, $outline, $shadow, $align,$size
                    );
                }
            } else {
                $filename = Mage::helper('aitcg/font')->getTextImage(
                    $model->getFontsPath() . $model->getFilename(), $text, $color, $outline, $shadow, $align,$size

                );
            }

            $response = '\'' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'custom_product_preview/quote/'
                . $filename . '\'';

            if ($blackAndWhite) {
                Mage::helper('aitcg/image')->setBlackWhiteImage($response, $filename);
            }

            $this->getResponse()->setBody($response);
        }
    }

    public function addPredefinedAction()
    {
        $blackAndWhite = Mage::app()->getRequest()->get('useblackAndWhite');
        $imgSize = Mage::app()->getRequest()->get('img_size');

        if ($this->getRequest()->isPost()) {
            $imgId    = Mage::app()->getRequest()->get('img_id');
            $filename = Mage::helper('aitcg/category')->copyPredefinedImage($imgId, $imgSize);
            $embossfilename = Mage::helper('aitcg/category')->copyEmbossPredefinedImage($imgId, $imgSize);

            $response        = array();
            $response['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'custom_product_preview/quote/'
                . $filename;
            if(!empty($embossfilename))
            {
                $response['embossurl'] = $embossfilename;
            }


            if ($blackAndWhite) {
                Mage::helper('aitcg/image')->setBlackWhiteImage($response['url'], $filename);
            }

            $this->_setBodyJson($response);
        }
    }

    public function addMaskAction()
    {
        if ($this->getRequest()->isPost()) {
            $maskId = Mage::app()->getRequest()->get('mask_id');
            $image  = Mage::getModel('aitcg/mask')->load($maskId);

            $response              = array();
            $response['url']       = $image->getImagesUrl() . 'alpha/' . $image->getFilename();
            $response['url_base']  = $image->getImagesUrl() . 'alpha/';
            $response['url_white'] = $image->getImagesUrl() . 'alpha/' . 'white' . $image->getFilename();
            $response['resize']    = $image->getResize();
            $response['id']        = $image->getId();

	        list($response['naturalWidth'], $response['naturalHeight']) = getimagesize($response['url']);
            $this->_setBodyJson($response);
        }
    }

    public function getInstagramPhotosAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $token  = $params['token'];
        $url    = "https://api.instagram.com/v1/users/self/media/recent/?access_token=" . $token;
        $next   = null;

        $photosArray = array();

        for (; ;) {
            $getPhotos = file_get_contents($url);
            $photos    = json_decode($getPhotos);
            foreach ($photos->data as $photo) {
                $photosArray[] = $photo;
            }
            $next = $photos->pagination->next_url;
            if ($next === null) {
                break;
            } else {
                $url = $next;
            }
        }


        $response = array('photos' => $photosArray);

        $this->_setBodyJson($response);
    }

    public function getInstagramUserTokenAction()
    {
        $userToken = Mage::getSingleton('core/session')->getInstagramToken();
        $response  = array('token' => $userToken);

        $this->_setBodyJson($response);
    }

    public function getPinterestPhotosAction()
    {
        $params = Mage::app()->getRequest()->getParams();
        $token  = $params['token'];
        $url    = "https://api.pinterest.com/v1/me/pins/?access_token=" . $token . '&limit=100&fields=image';
        $next   = null;

        $photosArray = array();

        for (; ;) {
            $getPhotos = file_get_contents($url);
            $photos    = json_decode($getPhotos);
            foreach ($photos->data as $photo) {
                $photosArray[] = $photo;
            }
            $next = $photos->page->next;
            if ($next === null) {
                break;
            } else {
                $url = $next;
            }
        }


        $response = array('photos' => $photosArray);

        $this->_setBodyJson($response);
    }

    public function getPinterestUserTokenAction()
    {
        $userToken = Mage::getSingleton('core/session')->getPinterestToken();
        $response  = array('token' => $userToken);

        $this->_setBodyJson($response);
    }

    public function createMaskAction()
    {
        if ($this->getRequest()->isPost()) {
            $request = Mage::app()->getRequest();

            $mask_created = Mage::getModel('aitcg/mask_created');
            if($request->get('id')>0) {
	            $mask_created->setId($request->get('id'));
            }
            $mask_created->setX($request->get('x'));
            $mask_created->setY($request->get('y'));
            $mask_created->setMaskId($request->get('mask_id'));
            $mask_created->setWidth($request->get('width'));
            $mask_created->setHeight($request->get('height'));
            $mask_created->save();

            $response       = array();
            $response['id'] = $mask_created->getId();
            $this->_setBodyJson($response);
        }
    }

    public function getMaskAction()
    {
        $mask_created = Mage::getModel('aitcg/mask_created')->load(Mage::app()->getRequest()->get('id'));

        $response                      = array();
        $response['mask']              = array();
        $response['mask']['id']        = $mask_created->getId();
        $response['mask']['x']         = $mask_created->getX();
        $response['mask']['y']         = $mask_created->getY();
        $response['mask']['mask_id']   = $mask_created->getMaskId();
        $response['mask']['width']     = $mask_created->getWidth();
        $response['mask']['height']    = $mask_created->getHeight();
        $response['mask']['url']       = $mask_created->getImagesUrl() . 'mask_inverted.png';
        $response['mask']['url_base']  = Mage::getModel('aitcg/mask')->getImagesUrl() . 'alpha/';
        $response['mask']['url_white'] = $mask_created->getImagesUrl() . 'white_mask_inverted.png';
        $response['mask']['url_black'] = $mask_created->getImagesUrl() . 'black_mask_inverted.png';
        $this->_setBodyJson($response);
    }


    public function delMaskAction()
    {
        $mask_created = Mage::getModel('aitcg/mask_created')->load(Mage::app()->getRequest()->get('id'));
        $mask_created->delete();
        $response         = array();
        $response['send'] = 'ok';
        $this->_setBodyJson($response);
    }

    public function setUpUploader()
    {
        $uploader = new Varien_File_Uploader('new_image');
        $uploader->setAllowedExtensions(explode(', ', Mage::getStoreConfig('catalog/aitcg/aitcg_image_extensions')));
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        return $uploader;
    }
    /**
     * Upload Cover Image
     */
    public function addCoverImageAction()
    {
        $response      = array();
        $blackAndWhite = Mage::app()->getRequest()->get('useblackAndWhite');

        if (isset($_FILES['new_image']['name']) && (file_exists($_FILES['new_image']['tmp_name']))) {
            $uploader = $this->setUpUploader();
            $path     = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
            try {
                $uploader->save($path, preg_replace('/[^A-Za-z\d\.]/', '_', $_FILES['new_image']['name']));
                $filename = $uploader->getUploadedFileName();

                $filename = $this->_convertToImg($filename);

                if (getimagesize($path . $filename) !== false) {
                    $response['src']   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                        . 'custom_product_preview/quote/' . $filename;
                    $response['error'] = 0;

                    if ($blackAndWhite) {
                        Mage::helper('aitcg/image')->setBlackWhiteImage($response['src'], $filename);
                    }
                } else {
                    $response['error'] = Mage::helper('aitcg')->__('Image file is empty or corrupt');
                }
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }

        } else {
            $response['error'] = Mage::helper('aitcg')->__('Something went wrong. Please try again.');
        }

        $this->_setBodyJson($response);
    }
    public function addImageAction()
    {
        $response      = array();
        $blackAndWhite = Mage::app()->getRequest()->get('useblackAndWhite');

        if (isset($_FILES['new_image']['name']) && (file_exists($_FILES['new_image']['tmp_name']))) {
            $uploader = $this->setUpUploader();
            $path     = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
            try {
                $uploader->save($path, preg_replace('/[^A-Za-z\d\.]/', '_', $_FILES['new_image']['name']));
                $filename = $uploader->getUploadedFileName();

                $filename = $this->_convertToImg($filename);

                if (getimagesize($path . $filename) !== false) {
                    $response['src']   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                        . 'custom_product_preview/quote/' . $filename;
                    $response['error'] = 0;

                    if ($blackAndWhite) {
                        Mage::helper('aitcg/image')->setBlackWhiteImage($response['src'], $filename);
                    }
                } else {
                    $response['error'] = Mage::helper('aitcg')->__('Image file is empty or corrupt');
                }
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }

        } else {
            $response['error'] = Mage::helper('aitcg')->__('Something went wrong. Please try again.');
        }

        $this->_setBodyJson($response);
    }

	public function setUpLogoUploader()
	{
		$uploader = new Varien_File_Uploader('new_logo_image');
		$uploader->setAllowedExtensions(explode(', ', Mage::getStoreConfig('catalog/aitcg/aitcg_image_extensions')));
		$uploader->setAllowRenameFiles(true);
		$uploader->setFilesDispersion(false);

		return $uploader;
	}

	/**
	 * Convert Hex Color to RGB
	 *
	 * @param $hex
	 * @return array returns an array with the rgb values
	 */
	public function converHex2RGB($hex)
	{
		$hex = str_replace("#", "", $hex);
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		return $rgb;
	}

	public function addLogoAction()
	{
		$response   = array();
		$logoColor  = Mage::app()->getRequest()->get('logoColor');
		$useColor   = Mage::app()->getRequest()->get('usecolor');
		$lastImage  = Mage::app()->getRequest()->get('lastimage');

		if (isset($_FILES['new_logo_image']['name']) && (file_exists($_FILES['new_logo_image']['tmp_name']))) {
			$uploader = $this->setUpLogoUploader();
			$path     = Mage::getBaseDir( 'media' ) . DS . 'custom_product_preview' . DS . 'quote' . DS;
			try {
				$uploader->save( $path, preg_replace( '/[^A-Za-z\d\.]/', '_', $_FILES['new_logo_image']['name'] ) );
				$filename = $uploader->getUploadedFileName();
				$filename = $this->_convertToImg( $filename );

				if ( getimagesize( $path . $filename ) !== false ) {
					$response['src']   = Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA ) . 'custom_product_preview/quote/' . $filename;
					$response['error'] = 0;

					if ( ! empty( $logoColor ) && $useColor === "true" ) {
						Mage::helper( 'aitcg/image' )->setColorImage( $response['src'], $filename, $this->converHex2RGB( $logoColor ) );
					}
				} else {
					$response['error'] = Mage::helper( 'aitcg' )->__( 'Image file is empty or corrupt' );
				}
			} catch ( Exception $e ) {
				$response['error'] = $e->getMessage();
			}
		} else if(!empty( $lastImage)) {

			$response = ''. $lastImage;
			if ( ! empty( $logoColor ) && $useColor === "true" ) {
				$filename = md5(date('Y-m-d H:i:s')).'_'. basename($lastImage);
				Mage::helper( 'aitcg/image' )->setColorImage( $lastImage, $filename, $this->converHex2RGB( $logoColor ) );
				$response = str_replace(basename($lastImage), $filename, $lastImage);
			}

			$this->getResponse()->setBody($response);

		} else {
			$response['error'] = Mage::helper('aitcg')->__('Something went wrong. Please try again.');
		}

		$this->_setBodyJson($response);
	}

    public function uploadImageAction()
    {
        copy('http://www.google.co.in/intl/en_com/images/srpr/logo1w.png', '/tmp/file.jpeg');


        $url      = $this->getRequest()->get('img_url');
        $response = array();

        $filename = rand(100000, 999999) . '.jpg';

        $filePath     = Mage::getBaseDir('media') . '/custom_product_preview/quote/' . $filename;
        $fullFilePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'custom_product_preview/quote/'
            . $filename;;

        copy($url, $filePath);

        $response['url'] = $fullFilePath;

        $this->_setBodyJson($response);

    }

    /*
     * @param mixed $model
     */
    protected function _createImage($model)
    {
        $result        = '';
        $productId     = $this->getRequest()->get('productId');
        $reservedImgId = $this->getRequest()->get('reservedImgId');
        $pngData       = $this->getRequest()->get('pngData');
        $lastRequest   = $this->getRequest()->get('lastRequest');
        $firstRequest  = $this->getRequest()->get('firstRequest');

        $session = Mage::getSingleton('core/session', array('name' => 'frontend'));// Session storage method may be refactored to files storage or something else probably

        if ($firstRequest == 1) {
            $session->unsPngDataCPP();
        }
        $pngData = $session->getPngDataCPP() . $pngData;
        if ($lastRequest == 1) {
            $session->unsPngDataCPP();
            $result = $model->createImage($productId, $reservedImgId, $pngData);
        } else {
            $session->setPngDataCPP($pngData);
        }

        if ($result == Aitoc_Aitcg_Model_Image::RESULT_CODE_ERROR_IMG_SIZE) {
            echo 'imgSizeError';

            return;
        } elseif ($result == Aitoc_Aitcg_Model_Image::RESULT_CODE_SUCCESS) {
            echo 'success';

            return;
        }

        echo 'waiting for next part of data';
    }

    /*
     * creates image for social networks sharing functionality
     */
    public function createImageAction()
    {
        $model = Mage::getModel('aitcg/sharedimage');

        $this->_createImage($model);
    }

    public function sharedImgWasCreatedAction()
    {
        $sharedImgId = $this->getRequest()->get('sharedImgId');
        if (Mage::helper('aitcg')->sharedImgWasCreated($sharedImgId)) {
            echo 'success';
        } else {
            echo 'false';
        }
    }

    public function createImageDefaultAction()
    {
        $model = Mage::getModel('aitcg/image');

        $this->_createImage($model);
    }

    protected function _setBodyJson($response)
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    protected function _convertToImg($filename)
    {
        if (Mage::getStoreConfig('catalog/aitcg/aitcg_use_imagemagick')
            && !in_array(pathinfo($filename, PATHINFO_EXTENSION), array('jpg', 'jpeg', 'bmp', 'gif', 'png'))
        ) {
            $path = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
            exec('convert ' . $path . $filename . ' -flatten ' . $path . $filename . '.jpg');
            $filename .= '.jpg';
        }

        return $filename;
    }

}