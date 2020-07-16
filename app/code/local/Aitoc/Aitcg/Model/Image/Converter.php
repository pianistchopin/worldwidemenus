<?php

class Aitoc_Aitcg_Model_Image_Converter extends Mage_Core_Model_Abstract 
{

    public function vmlToSvg($sVmlHtml)
    {
        $sVml = $this->_prepareVml($sVmlHtml);
        return $this->_xsltProcess($sVml, $this->_getXslFilePath());
    }
    
    protected function _getXslFilePath()
    {
        return Mage::getModuleDir('', Mage::helper('aitcg')->getModuleName()).DS.'vml2svg'.DS.'vml2svg.xsl';
    }
    
    private function _xsltProcess($sVml,$sXslFilePath)
    {
        $oProcessor = new xsltprocessor();
        $oXml = new DomDocument();
        $oXml->loadXML($sVml);
        
        $oXsl = new DomDocument;
        $oXsl->load($sXslFilePath);
        
        $oProcessor->importStyleSheet($oXsl);
        return $oProcessor->transformToXML($oXml);
    }
    
    private function _prepareVml($sHtmlVml)
    {
        preg_match('#<rvml.*/rvml:group>#si',$sHtmlVml,$aVmlElements);
        if (count ($aVmlElements) > 0)
        {
            $sVmlElements = $aVmlElements[0];
            $sVmlElements = str_replace('rvml:','v:',$sVmlElements);
            $sVmlElements = str_replace('class=rvml','',$sVmlElements);
            $sVmlElements = str_replace('filterMatrix','',$sVmlElements);

            $from="/(<meta[^>]*[^\/]?)>/i";
            $sVmlElements = preg_replace($from,"$1/>",$sVmlElements);
            $from="/\/(\/>)/i";
            $sVmlElements = preg_replace($from,"$1",$sVmlElements);
        }
        else
        {
            $sVmlElements = '';
        }
        $block = Mage::getBlockSingleton('core/template');
        $block->setTemplate('aitcg/image/converter/vml.phtml');
        $block->setVmlElements($sVmlElements);
        return $block->toHtml();
    }
    
    public function imageToBase64($sFilePath)
    {
        $sImg = file_get_contents($sFilePath);
        $sMimeType = $this->_getImageMimeType($sFilePath);
        return 'data:'.$sMimeType.';base64,'.base64_encode($sImg);
    }
    
    private function _getImageMimeType($sFilePath)
    {
        $iExtType = exif_imagetype($sFilePath);
        if($iExtType<1 || $iExtType > 17)
        {
            Mage::logException('Aitcg: Warning: class '.__CLASS__.': Invalid image type.');
        }    
        $sMimeType = image_type_to_mime_type($iExtType);
        return $sMimeType;
    }        
            
}
