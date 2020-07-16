<?php
class Aitoc_Aitcg_Varien_Image extends Varien_Image
{
    function __construct($fileName=null)
    {
        $this->_getAdapter();
        $this->_fileName = $fileName;
        if( isset($fileName) ) {
            $this->open();
        }
    }
    
    protected function _getAdapter($adapter=null)
    {
        if( !isset($this->_adapter) ) {
            $this->_adapter = new Aitoc_Aitcg_Varien_Image_Adapter_Gd2();
        }
        return $this->_adapter;
    }

    public function getSrcImagedimension() {
        return $this->_adapter->getSrcImageDimension();
    }
}