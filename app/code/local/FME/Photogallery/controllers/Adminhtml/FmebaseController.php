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
 * @copyright 2016 Magento Inc. (https://www.fmeextensions.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link      https://www.fmeextensions.com
 */

/**
 * @category FME
 * @package FME_Fmebase
 * @author Altaf Ahmed <support@fmeextension.com>
 * @license http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link https://www.fmeextensions.com
 */
class FME_Photogallery_Adminhtml_FmebaseController 
extends Mage_Adminhtml_Controller_Action
{
    /**
     * SendAction
     *
     * @access public
     * @author Altaf Ahmed
     * @return true or false
     */
    public function sendAction()
    {
        $data = $this->getRequest()->getPost();
        $emailto='support@fmeextensions.com';
        $toName='FME Support';
        $from = $data['email'];
        $fromName = $data['name'];
        $subject= "FME Base Module - ".$data['subject'];
        $telephone=$data['telephone'];
        $extension = $data['extension'];
        $message= $data['message'];
        $emailTemplate  = Mage::getModel('core/email_template')
                        ->loadDefault('fme_Support_email');
        $emailTemplateVar = array();
        $emailTemplateVar['toName'] = $toName;
        $emailTemplateVar['senderemail'] = $data['email'];
        $emailTemplateVar['name'] = $data['name'];
        $emailTemplateVar['telephone'] = $telephone;
        $emailTemplateVar['extension'] = $extension;
        $emailTemplateVar['message'] = $message;
        $emailTemplateVar['url'] = Mage::getUrl();
        $emailTemplate->setSenderName($fromName);
        $emailTemplate->setSenderEmail($from);
        $emailTemplate->setTemplateSubject($subject);
        $emailTemplate->getProcessedTemplate($emailTemplateVar);
        try {
            $emailTemplate->send($emailto, $toName, $emailTemplateVar);
        } catch (Exception $e){
            $result['message'] = $e->getMessage();
            $this->ajaxResponse($result);
            return;
        }

        $result['message'] = $this->__('Message sent');
        $this->ajaxResponse($result);
    }
    /**
     * _ajaxResponse
     *
     * @access protected
     * @param  array $result
     * @return json response
     * @author Altaf Ahmed
     */
    protected function ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }
    /**
     * _isAllowed
     *
     * @access protected
     * @author Altaf Ahmed
     * @return true
     */
    protected function _isAllowed()
    {
        return true;
    }
}