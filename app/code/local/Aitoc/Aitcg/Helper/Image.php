<?php

class Aitoc_Aitcg_Helper_Image extends Mage_Catalog_Helper_Image
{
    protected $baseDir = '';

    public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile = null)
    {
        return $this->load($attributeName, $imageFile);
    }

    public function loadLite($imageFile, $baseDir = 'catalog/product_media_config')
    {
        if ($baseDir) {
            if ($baseDir == 'media') {
                $this->setBaseDir(Mage::getBaseDir('media'));
            } elseif ($baseDir == 'adjconfigurable') {//compatibility with VYA extension
                $this->setBaseDir(Mage::getBaseDir('media') . DS . $baseDir);
            } else {
                $this->setBaseDir(Mage::getSingleton($baseDir)->getBaseMediaPath());
            }
        }

        return $this->load(Aitoc_Aitcg_Helper_Options::OPTION_TYPE_AITCUSTOMER_IMAGE, $imageFile);
    }

    public function load($attributeName, $imageFile)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('aitcg/product_image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setWatermark(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image"));
        if (version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
            $this->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity"));
        }
        $this->setWatermarkPosition(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position"));
        $this->setWatermarkSize(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size"));
        $this->setImageFile($imageFile);

        return $this;
    }

    public function getBaseDir()
    {
        return $this->_baseDir;
    }

    public function setBaseDir($baseDir)
    {
        $this->_baseDir = $baseDir;
    }

    public function __toString()
    {
        try {
            if ($this->getImageFile()) {
                $this->_getModel()->setBaseFile($this->getImageFile(), $this->getBaseDir());
            } else {
                $this->_getModel()->setBaseFile(
                    $this->getProduct()->getData(
                        $this->_getModel()
                            ->getDestinationSubdir()
                    )
                );
            }
            if ($this->_getModel()->isCached()) {
                return $this->_getModel()->getUrl();
            } else {
                if ($this->_scheduleResize) {
                    $this->_getModel()->resize();
                }
                if ($this->_scheduleRotate) {
                    $this->_getModel()->rotate($this->getAngle());
                }
                if ($this->getWatermark()) {
                    $this->_getModel()->setWatermark($this->getWatermark());
                }
                $url = $this->_getModel()->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }

        return $url;
    }

    public function getNewFile()
    {
        return $this->_getModel()->getNewFile();
    }

    public function getOriginalSize()
    {
        return $this->_getModel()->calcOriginalSize();
    }

    public function setBlackWhiteImage($url, $filename)
    {
        $path  = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
        $image = $this->_getImg($url);
        $srcW  = imagesx($image);
        $srcH  = imagesy($image);
        for ($x = 0; $x < $srcW; $x++) {
            for ($y = 0; $y < $srcH; $y++) {
                $srcColor = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $r        = $srcColor['red'];
                $g        = $srcColor['green'];
                $b        = $srcColor['blue'];
                $pixel    = ($r + $g + $b) / 3;
                $srcColor = imagecolorallocatealpha(
                    $image, $pixel, $pixel, $pixel, $srcColor['alpha']
                );
                imagesetpixel($image, $x, $y, $srcColor);
            }
        }
        imagepng($image, $path . $filename);

        return true;
    }

	public function setColorImage($url, $filename, $color = array(0,0,0))
	{
		$path  = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
		$image = $this->_getImg($url);
		$srcW  = imagesx($image);
		$srcH  = imagesy($image);
		list($R, $G, $B) = $color;

		$isblack = true;
		for ($x = 0; $x < $srcW; $x++) {
			for ($y = 0; $y < $srcH; $y++) {
				$rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                if(($rgb['red']>40 && ($rgb['red'] > 0 || $rgb['green'] > 0 || $rgb['blue'] > 0) ) && ($rgb['red'] != 255 && $rgb['green'] != 255 || $rgb['blue'] != 255 && $rgb['alpha'] != 127 ) ) {
                   // if(($rgb['red'] > 0 || $rgb['green'] > 0 || $rgb['blue'] > 0 ) ) {
                   $isblack = false; break;
				}
			}
			if(!$isblack) { break; }
		}

		for ($x = 0; $x < $srcW; $x++) {
			for ($y = 0; $y < $srcH; $y++) {

				// Apply new color + Alpha
				$rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));

				$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
				imagesetpixel($image, $x, $y, $transparent);

				if($isblack) {
					$red_set = $color[0]; $green_set = $color[1]; $blue_set = $color[2];
				} else {
					$pixel = ($rgb['red'] + $rgb['green']+ $rgb['blue'])/3;

					// Here, you would make your color transformation.
					$red_set    = $color[0]/100*$pixel;
					$green_set  = $color[1]/100*$pixel;
					$blue_set   = $color[2]/100*$pixel;
				}

				if($red_set>255)$red_set=255;
				if($green_set>255)$green_set=255;
				if($blue_set>255)$blue_set=255;

				$pixelColor = imagecolorallocatealpha($image, $red_set, $green_set, $blue_set, $rgb['alpha']);
				imagesetpixel ($image, $x, $y, $pixelColor);
			}
		}

		// Restore Alpha
		imageAlphaBlending($image, true);
		imageSaveAlpha($image, true);

		imagepng($image,$path . $filename);

		return true;
	}

    public function _getImg($file)
    {
        $extension = strrchr($file, '.');
        $extension = strtolower($extension);
        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                $im = @imagecreatefromjpeg($file);
                break;
            case '.gif':
                $im = @imagecreatefromgif($file);
                break;
            case '.png':
                $im = @imagecreatefrompng($file);
                imageAlphaBlending($im, true);
                imageSaveAlpha($im, true);
                break;
            default:
                $im = false;
                break;
        }

        return $im;
    }
}