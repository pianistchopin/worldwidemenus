<?php
class Aitoc_Aitcg_Helper_Data extends Aitoc_Aitcg_Helper_Abstract
{
    const MODULE_NAME = 'Aitoc_Aitcg';
    protected static $_margin=array( 'A4'=>'26',
        'A5'=>'18',
        'A3'=>'36',
        //'2/3 A4'=>,
        //'1/2 A4'=>,
        'DEFAULT'=>'18'
    );
    /**
     * Integration type constants
     */
    const INTEGRATION_POPUP   = 'popup';
    const INTEGRATION_GALLERY = 'gallery';
    const INTEGRATION_DEFAULT = self::INTEGRATION_POPUP;
    /*
     * compatibility with VYA extension
     */
   const VYA_INTEGRATION_POPUP_PREVIEW_SIZE = 135;
   const VYA_INTEGRATION_GALLERY_PREVIEW_SIZE = 56;
  // const VYA_INTEGRATION_POPUP_PREVIEW_SIZE = 295;
  // const VYA_INTEGRATION_GALLERY_PREVIEW_SIZE = 295;
    /*
     * compatibility with VYA extension
     */
    protected $_isVYAEnabled = null;
    public function getOneOffCostLabel(){
        return __("I'm agree with One of cost Condition.");
    }
    public function getOneOffCostAdminLabel(){
        return __("I'm agree with One of cost Condition.");
    }
    public function getMarginSize(){
        $product=Mage::registry('current_product');
        if ($product && $product->getId()) {
            $size =$product->getResource()->getAttribute('size')->setStoreId(0)->getFrontend()->getValue($product);
        }
        if(!empty($size)){
            $size=strtoupper($size);
            if(isset(self::$_margin[$size])){
                $marginPx=self::$_margin[$size];
            }
        }
        $marginPx=!empty($marginPx)?$marginPx:self::$_margin['DEFAULT'];
        return $marginPx;
    }
    public function isRequirePreview()
    {
        return Mage::getStoreConfig('catalog/aitcg/aitcg_preview_is_required');
    }
    public function getIsMarginPx(){
        $product=Mage::registry('current_product');
        if ($product && $product->getId()) {
            $categoryIds = $product->getCategoryIds();
            foreach ($categoryIds as $category_id) {
                // $_cat = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
                $_cat = Mage::getModel('catalog/category')->load($category_id);

                if ($_cat->hasData('margin_cover_border')) {
                    if ($_cat->getData('margin_cover_border') == 0 || $_cat->getData('margin_cover_border') == "0") {
                        return 0;
                    }

                }
                // echo $_cat->getName();
            }
            return 1;
        }
        else{
            return 1;
        }

    }
    public function recursiveDelete($str)
    {
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }
    
    public function uniqueFilename($strExt = '.tmp') {
            // explode the IP of the remote client into four parts
            $arrIp = explode('.', $_SERVER['REMOTE_ADDR']);
            // get both seconds and microseconds parts of the time
            list($usec, $sec) = explode(' ', microtime());
            // fudge the time we just got to create two 16 bit words
            $usec = (integer) ($usec * 65536);
            $sec = ((integer) $sec) & 0xFFFF;
            // fun bit--convert the remote client's IP into a 32 bit
            // hex number then tag on the time.
            // Result of this operation looks like this xxxxxxxx-xxxx-xxxx
            $strUid = sprintf("%08x-%04x-%04x", ($arrIp[0] << 24) | ($arrIp[1] << 16) | ($arrIp[2] << 8) | $arrIp[3], $sec, $usec);
            // tack on the extension and return the filename
            return $strUid . $strExt;
    }
    
    final public function getModuleName()
    {
        return self::MODULE_NAME;
    }

    public function getSecureUnsecureUrl($jsonData)
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, Mage::app()->getStore()->isCurrentlySecure());
        $allStores = Mage::app()->getStores();
        $urlForReplace = array();
        foreach ($allStores as $storeId => $val)
        {
            $urlForReplace[] = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false);
            $baseUrlStoreSecure = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
            $urlForReplace[] = $baseUrlStoreSecure;
            $urlForReplace[] = str_replace('https:','http:', $baseUrlStoreSecure);
        }
        $urlForReplace = array_unique($urlForReplace);
        foreach($urlForReplace as $key => $value)
        {
            if(strlen(trim($value)) < 5)//validation that url is real
            unset($urlForReplace[$key]);
        }
        $jsonData = str_replace($urlForReplace, $baseUrl, $jsonData);
        return $jsonData;
    }

    public function getSharedImgUrl($sharedImgId, $router = 'aitcg')
    {
        return Mage::getUrl($router.'/index/sharedimage', array('id' => $sharedImgId));
    }

    public function getSharedImgWasCreatedUrl($router = 'aitcg')
    {
        return Mage::getUrl($router.'/ajax/sharedimgwascreated', array());
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string $sharedImgId
     */
    public function getEmailToFriendUrl(Mage_Catalog_Model_Product $product, $sharedImgId = '0')
    {
        return Mage::helper('catalog/product')->getEmailToFriendUrl($product) . 'aitcg_shared_img_id/' . $sharedImgId . '/';
    }

    public function getImgCreatePath($router = 'aitcg')
    {
        return Mage::getUrl($router.'/ajax/createimage', array());
    }

    public function getAitcgMainJsClass()
    {
        if(Mage::getStoreConfig('catalog/aitcg/aitcg_use_social_networks_sharing')) 
            return 'Aitcg.Main.SocialWidgets';
        
        return 'Aitcg.Main';
    }

    /**
     *
     * @param number
     *
     */
    public function getSharedImgId($rand)
    {
        return time() . $rand;
    }   

    public function sharedImgWasCreated($sharedImgId = 0)
    {
        $model = Mage::getModel('aitcg/sharedimage');
        $model->load($sharedImgId);
        if(is_null($model->getId()))
            return false;

        if($model->imagesNotExist())
            return false;

        return true;
    }

    //remove Social Widgets code from Admin area and from Cart Sidebar at frontend
    public function removeSocialWidgetsFromHtml($html = '')
    {
        $array = explode('<!-- aitoc social widgets DO NOT TOUCH THIS LINE !!! -->', $html);
        if(count($array) > 1)
        {
            $html = '';
            foreach($array as $val)
            {
                if(!strstr($val, '<!-- aitoc social widgets inner html DO NOT TOUCH THIS LINE ALSO !!! -->'))
                {
                    $html .= $val;
                }
            }
        }
        else
        {
            return $html;
        }

        return $html;
    }
    
    public function getAllowedFormats()
    {
        $formats = Mage::getStoreConfig('catalog/aitcg/allowed_formats_for_save');
        if(!$formats) {
            return array();
        }
        return explode(',', $formats);
    }
    
    public function isFormatAllowed($format)
    {
    	$allowedFormats = $this->getAllowedFormats();
    	
    	return in_array($format, $allowedFormats) ? true : false;
    }
    
    public function getFormatsTranslations()
    {
        $array = array(
            'svg' => 'Save as SVG',
            'jpg' => 'Convert to JPG',
            'gif' => 'Convert to GIF',
            'png' => 'Convert to PNG',
            'pdf' => 'Save as PDF',
        );
        $allowedFormats = $this->getAllowedFormats();
        $result = '';
        foreach($allowedFormats as $format) {
            $result .= $format.': "'.$this->__($array[$format]).'",'."\n";
        }
        return $result;
    }

    /*
     * Compatibility with VYA extension
     * @param string $type
     * @return array
     */
    public function getThumbSize($type = self::INTEGRATION_DEFAULT)
    {
        if($this->isVYAEnabled()){
            switch($type){
                case self::INTEGRATION_DEFAULT:
                    return array(self::VYA_INTEGRATION_POPUP_PREVIEW_SIZE,self::VYA_INTEGRATION_POPUP_PREVIEW_SIZE);
                default:
                    return array(self::VYA_INTEGRATION_GALLERY_PREVIEW_SIZE,self::VYA_INTEGRATION_GALLERY_PREVIEW_SIZE);
            }
        }

        return explode('x', Mage::getStoreConfig('catalog/aitcg/thumb_size_' . $type ));
    }

    /*
     * Compatibility with FYA extension
     * @return boolean
     */
    public function isVYAEnabled()
    {
        if($this->_isVYAEnabled === null){
            $this->_isVYAEnabled = Mage::getConfig()->getModuleConfig('AdjustWare_Icon')->is('active', 'true');
        }

        return $this->_isVYAEnabled;
    }

    public function requestIsSuitable($stack)
    {
        $request = Mage::app()->getRequest();
        $requestString = implode(DS, array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()));
        return in_array($requestString, $stack);
    }
    public  function getSlug($str, $delimiter = '-'){

        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;

    }
    public function getEngravingBlock($isEngraving){
        if(!empty($isEngraving) && $isEngraving)
        {
            $htmlContent = str_replace('\n','',Mage::getModel('cms/block')->load('engraving')->getContent());
            return preg_replace('/^\s+|\n|\r|\s+$/m', '', ($htmlContent));
            /*$htmlContent=str_replace(PHP_EOL, '', addslashes($htmlContent));
            $htmlContent=str_replace('\n', '', ($htmlContent));
            $htmlContent=str_replace('\r', '', ($htmlContent));
            return $htmlContent;*/
        }
        else{
            return '';
        }

    }
}