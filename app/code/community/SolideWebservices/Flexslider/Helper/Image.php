<?php
/**
 * @category    Solide Webservices
 * @package     Flexslider
 */
 
class SolideWebservices_Flexslider_Helper_Image extends Mage_Core_Helper_Abstract {

	/**
	 * Storeage for image object, used for resizing images
	 *
	 * @var null/Varien_Image
	 */
	protected $_imageObject = null;

	/**
	 * Filename currently initialized in the image object
	 *
	 * @var null|string
	 */
	protected $_filename = '';

	/**
	 * The folders used to store (resized) images and thumbnails
	 * This is relative to the media directory
	 *
	 * @var const string
	 */
	const IMAGE_FOLDER = 'flexslider';
	const THUMB_FOLDER = 'flexslider/thumbnails';
	const SMALL_FOLDER = 'flexslider/small';
	const MEDIUM_FOLDER = 'flexslider/medium';
	const LARGE_FOLDER = 'flexslider/large';

	/**
	 * Retrieve the image URL's where images are stored
	 *
	 * @return string
	 */
	public function getImageUrls($imagepath) {
		return Mage::getBaseUrl('media') . $imagepath . '/';
	}

	/**
	 * Retrieve the directory/path where images are stored
	 *
	 * @return string
	 */
	public function getImagePaths($imagepath) {
		return Mage::getBaseDir('media') . DS . $imagepath . DS;
	}

	/**
	 * Retrieve the full image URL
	 * Null returned if image does not exist
	 *
	 * @param string $image
	 * @return string|null
	 */
	public function getImageUrl($image, $imagepath = self::IMAGE_FOLDER) {
		if ($this->imageExists($image)) {
			return $this->getImageUrls($imagepath) . $image;
		}

		return null;
	}

	/**
	 * Retrieve the full image path
	 * Null returned if image does not exist
	 *
	 * @param string $image
	 * @return string|null
	 */
	public function getImagePath($image, $imagepath = self::IMAGE_FOLDER) {
		if ($this->imageExists($image)) {
			return $this->getImagePaths($imagepath) . $image;
		}
		
		return null;
	}

	/**
	 * determine whether the image exists
	 *
	 * @param string $image
	 * @return bool
	 */
	public function imageExists($image) {
		return is_file($this->getImagePaths(self::IMAGE_FOLDER) . $image);
	}

	/**
	 * Converts a filename, width and height into it's resized uri path
	 * returned path does not include base path
	 *
	 * @param string $filename
	 * @param int $width = null
	 * @param int $height = null
	 * @return string
	 */
	public function getResizedImageUrl($filename, $width = null, $height = null) {
		return $this->getImageUrls(self::IMAGE_FOLDER) . $this->_getRelativeResizedImagePath($filename, $width, $height);
	}

	/**
	 * Converts a filename, width and height into it's resized path
	 * returned path does not include base path
	 *
	 * @param string $filename
	 * @param int $width = null
	 * @param int $height = null
	 * @return string
	 */
	public function getResizedImagePath($filename, $width = null, $height = null) {
		return $this->getImagePaths(self::IMAGE_FOLDER) . $this->_getRelativeResizedImagePath($filename, $width, $height);
	}

	/**
	 * Converts a filename, width and height into it's resized path
	 * returned path does not include base path
	 *
	 * @param string $filename
	 * @param int $width = null
	 * @param int $height = null
	 * @return string
	 */	
	protected function _getRelativeResizedImagePath($filename, $width = null, $height = null) {
		if (!is_null($width) || !is_null($height)) {
			return 'cache' . DS . trim($width.'x'.$height, 'x') . DS . $filename;
		}
		
		return $filename;
	}

	/**
	 * Resize the image loaded into the image object
	 *
	 * @param int $width = null
	 * @param int $height = null
	 * @return string
	 */
	public function resize($width = null, $height = null) {
		if ($this->isActive()) {
			$cachedFilename = $this->getResizedImagePath($this->_filename, $width, $height);
				
			if ($this->_forceRecreate || !is_file($cachedFilename)) {
				$this->_imageObject->resize($width, $height);
				$this->_imageObject->save($cachedFilename);
			}
			
			return $this->getResizedImageUrl($this->_filename, $width, $height);
		}

		return '';
	}

	/**
	 * Determine whether the image object has been initialised
	 *
	 * @return bool
	 */
	public function isActive() {
		return is_object($this->_imageObject);
	}

	/**
	 * Upload an image based on the $fileKey
	 *
	 * @param string $fileKey
	 * @param string|null $filename - set a custom filename
	 * @return null|string - returns saved filename
	 */
	public function uploadImage($fileKey, $filename = null) {
		try {
			$uploader = new Varien_File_Uploader($fileKey);
			$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
			$uploader->setAllowRenameFiles(true);
			$result = $uploader->save($this->getImagePaths(self::IMAGE_FOLDER));

			$imageUrl = $this->getImagePaths(self::IMAGE_FOLDER) . $result['file'];
			$thumbResized = $this->getImagePaths(self::THUMB_FOLDER) . $result['file'];
			$smallResized = $this->getImagePaths(self::SMALL_FOLDER) . $result['file'];
			$mediumResized = $this->getImagePaths(self::MEDIUM_FOLDER) . $result['file'];
			$largeResized = $this->getImagePaths(self::LARGE_FOLDER) . $result['file'];
			$size = Mage::getStoreConfig('flexslider/general/thumbnail_upload_width');

			if (!file_exists($thumbResized) && file_exists($imageUrl)) :
				$thumbObj = new Varien_Image($imageUrl);
				$thumbObj->constrainOnly(TRUE);
				$thumbObj->keepAspectRatio(TRUE);
				$thumbObj->keepFrame(FALSE);
				$thumbObj->resize($size, null);
				$thumbObj->save($thumbResized);
			endif;

			if (!file_exists($smallResized) && file_exists($imageUrl)) :
				$smallObj = new Varien_Image($imageUrl);
				$smallObj->constrainOnly(TRUE);
				$smallObj->keepAspectRatio(TRUE);
				$smallObj->keepFrame(FALSE);
				$smallObj->resize(320, null);
				$smallObj->save($smallResized);
			endif;

			if (!file_exists($mediumResized) && file_exists($imageUrl)) :
				$mediumObj = new Varien_Image($imageUrl);
				$mediumObj->constrainOnly(TRUE);
				$mediumObj->keepAspectRatio(TRUE);
				$mediumObj->keepFrame(FALSE);
				$mediumObj->resize(640, null);
				$mediumObj->save($mediumResized);
			endif;
			
			if (!file_exists($largeResized) && file_exists($imageUrl)) :
				$largeObj = new Varien_Image($imageUrl);
				$largeObj->constrainOnly(TRUE);
				$largeObj->keepAspectRatio(TRUE);
				$largeObj->keepFrame(FALSE);
				$largeObj->resize(1024, null);
				$largeObj->save($largeResized);
			endif;

			return $result['file'];
		}
		catch (Exception $e) {
			if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY) {
				throw $e;
			}
		}

		return null;
	}
	
	/**
	 * Create smaller sized images for existing slides on upgrade from version 1.7.2 or lower
	 */
	public function resizeImages() {		
		$result = Mage::getModel('flexslider/slide')->getCollection()
			->addFieldToFilter('hosted_image', array('eq' => 0))
			->addFieldToFilter('slidetype', array('eq' => 'image'));

		foreach ($result as $result_row) {		
			$imageUrl = $this->getImagePaths(self::IMAGE_FOLDER) . $result_row['image'];
			$smallResized = $this->getImagePaths(self::SMALL_FOLDER) . $result_row['image'];
			$mediumResized = $this->getImagePaths(self::MEDIUM_FOLDER) . $result_row['image'];
			$largeResized = $this->getImagePaths(self::LARGE_FOLDER) . $result_row['image'];

			if (!file_exists($smallResized) && file_exists($imageUrl)) :
				$smallObj = new Varien_Image($imageUrl);
				$smallObj->constrainOnly(TRUE);
				$smallObj->keepAspectRatio(TRUE);
				$smallObj->keepFrame(FALSE);
				$smallObj->resize(320, null);
				$smallObj->save($smallResized);
			endif;

			if (!file_exists($mediumResized) && file_exists($imageUrl)) :
				$mediumObj = new Varien_Image($imageUrl);
				$mediumObj->constrainOnly(TRUE);
				$mediumObj->keepAspectRatio(TRUE);
				$mediumObj->keepFrame(FALSE);
				$mediumObj->resize(640, null);
				$mediumObj->save($mediumResized);
			endif;
			
			if (!file_exists($largeResized) && file_exists($imageUrl)) :
				$largeObj = new Varien_Image($imageUrl);
				$largeObj->constrainOnly(TRUE);
				$largeObj->keepAspectRatio(TRUE);
				$largeObj->keepFrame(FALSE);
				$largeObj->resize(1024, null);
				$largeObj->save($largeResized);
			endif;
		}

	}
}