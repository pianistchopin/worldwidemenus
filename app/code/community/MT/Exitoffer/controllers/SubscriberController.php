<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

include_once('Mage/Newsletter/controllers/SubscriberController.php');

class MT_Exitoffer_SubscriberController extends Mage_Newsletter_SubscriberController
{

    public function newAction()
    {
        $helper = Mage::helper('exitoffer');
        $session = Mage::getSingleton('core/session');
        $showCode = false;
        $error = '';
        try {
            if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
                $customerSession = Mage::getSingleton('customer/session');
                $postData = $this->getRequest()->getPost();
                $email = (string) $this->getRequest()->getPost('email');

                if (!$this->validateCaptcha()) {
                    Mage::throwException($this->__('Incorrect security code'));
                }

                if (!Mage::getModel('exitoffer/subscriber')->isValidAdditional($postData)) {
                    Mage::throwException($this->__('There was a problem with the subscription.'));
                }

                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($helper->translate('error_email_not_valid'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($helper->translate('email_is_assigned_to_another_user'));
                }

                $subscriberId = Mage::getModel('newsletter/subscriber')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
                if ($subscriberId !== null)
                    Mage::throwException($helper->translate('email_already_exist'));

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($helper->translate('success_message_need_to_confirm'));
                } else {
                    $session->addSuccess($helper->translate('success_message'));
                }
            } else {
                Mage::throwException($helper->translate('error_email_not_valid'));
            }
        } catch (Mage_Core_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = $helper->translate('error_with_subscription');
            $session->addException($e, $helper->translate('error_with_subscription'));
        }

        $messages = $session->getMessages(true);
        $success = $messages->getItemsByType('success');
        $response = array(
            'errorMsg' => '',
            'successMsg' => ''
        );

        if (!empty($error)) {
            $response['errorMsg'] = $error;
        } elseif ($success) {
            if ($this->_allowToShowCode()) {
                $response['couponCode'] = Mage::registry('subscriber_exit_offer_coupon_code');
            }
            $response['successMsg'] = $success[0]->getText();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    protected function _allowToShowCode()
    {
        $campaign = $this->getCampaign();

        if ($campaign && !$campaign->getPopup()->getShowCouponCode()) {
            return false;
        }

        return true;
    }

    protected function getCampaign()
    {
        $campaignId = $this->getRequest()->getParam('campaign_id');
        if (empty($campaignId) || !is_numeric($campaignId)) {
            return false;
        }

        $campaign = Mage::getModel('exitoffer/campaign')->load($campaignId);
        if (!$campaign->getId()) {
            return false;
        }

        return $campaign;
    }

    protected function validateCaptcha()
    {
        $campaign = $this->getCampaign();
        if (!$campaign) {
            return false;
        }

        $formId = 'exit_offer_popup';
        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);

        if ($campaign->getPopup()->getUseCaptcha()) {
            $word = $this->getCaptchaString($formId);
            if (!$captchaModel->isCorrect($word)) {
                return false;
            }
        }

        return true;
    }

    protected function getCaptchaString($formId)
    {
        $captchaParams = $this->getRequest()->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
        return $captchaParams[$formId];
    }

}
