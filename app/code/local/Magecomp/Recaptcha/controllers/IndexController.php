<?php
class Magecomp_Recaptcha_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	public function saveAction()
	{
	    try {
            $post = $this->getRequest()->getPost();
            if ( $post ) {
                $postObject = new Varien_Object();
                $postObject->setData($post);
                $g_response = $this->getRequest()->getParam('g-recaptcha-response');

                if (isset($g_response) && !empty($g_response)):
                    if (Mage::helper('recaptcha')->Validate_captcha($g_response)):
                            try {
                                Mage::helper('recaptcha')->contactmailsent($postObject);
                                Mage::getSingleton('core/session')->addSuccess('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.');
                                return $this->_redirectReferer();
                            } catch (Exception $e) {
                                echo $e->getMessage();
                            }
                    else:
                        Mage::getSingleton('core/session')->addError('Please click on the reCAPTCHA box.');
                        return $this->_redirectReferer();
                    endif;
                else:
                    Mage::getSingleton('core/session')->addError('Please click on the reCAPTCHA box.');
                    return $this->_redirectReferer();
                endif;
            }
        }
        catch (Exception $e){
	        Mage::log("Captcha Error :".$e->getMessage(),null,"recaptcha.log");
        }
	}
}
?>