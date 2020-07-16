<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * PHP version 5
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  FME
 * @package   FME_Fmebase
 * @author    Altaf Ahmed <support@fmeextension.com>
 * @copyright 2016 XFME Extensions (https://www.fmeextensions.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link      https://www.fmeextensions.com
 */

/**
 * Class FME_Fmebase_Block_System_Config_Form_Fme_Social
 *
 * @category FME
 * @package  FME_Fmebase
 * @author   Altaf Ahmed <support@fmeextension.com>
 * @license  http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link     https://www.fmeextensions.com
 */
class FME_Photogallery_Block_System_Config_Form_Fme_Social 
extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_getHeaderHtml($element);
        $facebookImg = $this->getSkinUrl('images/photogallery/footer_facebook.png');
        $twitterImg = $this->getSkinUrl('images/photogallery/footer_twitter.png');
        $fmeImg = $this->getSkinUrl('images/photogallery/footer_logo.png');
        $plusImg = $this->getSkinUrl('images/photogallery/footer_google_plus.png');
        $linkedImg = $this->getSkinUrl('images/photogallery/footer_linkedin.png');
        
        return '<style> .links { width:100%; }

.links ul { width:100%; list-style:none; text-align:center; }

.links ul li { display:inline-block; margin:0 15px }

.links ul li a { font-family:"Open Sans", sans-serif; font-size:15px; font-weight:600; color:#484848; text-transform:uppercase; }

.links ul li a:hover { color:#db4d2d; }

.links ul li img.logo { margin-bottom:-28px; }

.links ul li img.icon { margin-bottom:-6px; } </style><div class="links">
<ul>

<li>
<a href="https://www.facebook.com/FMEMagentoExtensions" target="_blank">
<img width="26" height="26" class="icon" alt="FME Facebook" src="'.$facebookImg.'">
</a>
</li>
<li>
<a href="https://twitter.com/fmeextension" target="_blank">
<img width="26" height="26" class="icon" alt="FME Twitter" src="'.$twitterImg.'">
</a>
</li>
<li>
<a href="https://www.fmeextensions.com/" target="_blank">
<img style="height:74px"; width="81" height="74" class="logo" 
alt="FME Magento Extensions" src="'.$fmeImg.'">
</a>
</li>
<li>
<a href="https://plus.google.com/+Fmeextensions" target="_blank">
<img width="26" height="26" class="icon" alt="FME Google Plus" src="'.$plusImg.'">
</a>
</li>
<li>
<a href="#" target="_blank">
<img width="26" height="26" class="icon" alt="FME Linkedin" src="'.$linkedImg.'">
</a>
</li>

</ul>
</div>';
    }
    
    
}