<?php

class Aitoc_Aitcg_Model_System_Config_Backend_EnableSvgToPdf extends Mage_Core_Model_Config_Data
{
    public function _beforeSave() {
        parent::_beforeSave();
        if ($this->getValue() == 1)
        {
            exec("convert -version", $out, $rcode); //Try to get ImageMagick "convert" program version number.
            //echo "Version return code is $rcode <br>"; //Print the return code: 0 if OK, nonzero if error.

            if (!($rcode === 0))
                throw Mage::exception('Mage_Core', Mage::helper('aitcg')->__('Requires ImageMagick to be installed at your host and allowed php exec command;  check http://www.imagemagick.org/script/formats.php for possible format conversions'));

            try{
            $imagick = new Imagick();
            }
            catch (Exception $e)
            {
                throw Mage::exception('Mage_Core', Mage::helper('aitcg')->__('Requires ImageMagick to be installed at your host and allowed php exec command;  check http://www.imagemagick.org/script/formats.php for possible format conversions'));
            }
            // return $this->_getPromoBlock();

        }
        
    }
    
}
