<?php
class Aitoc_Aitcg_Model_Rewrite_Sendfriend_Sendfriend extends Mage_Sendfriend_Model_Sendfriend
{
    //overwrite parent
    public function getTemplate()
    {
        $aitcgSharedImgId = Mage::app()->getRequest()->getParam('aitcg_shared_img_id');
        if(isset($aitcgSharedImgId))
        {
            return Mage::getStoreConfig('catalog/aitcg/aitcg_share_img_template');
        }

        return parent::getTemplate();
    }

    //overwrite parent
    public function send()
    {
        $sharedImgId = Mage::app()->getRequest()->getParam('aitcg_shared_img_id');
        if(!isset($sharedImgId))
            return parent::send();

        if(version_compare(Mage::getVersion(), '1.4.0.0', '<'))
            return $this->_send13();
        else
           return $this->_send14();
    }

    protected function _getSharedImgPath()
    {
        $sharedImgId = (string) Mage::app()->getRequest()->getParam('aitcg_shared_img_id');
        $sharedImgModel = Mage::getModel('aitcg/sharedimage');
        $sharedImgPath = $sharedImgModel->load($sharedImgId)->getSharedImgPath();

        return $sharedImgPath;
    }

    //copied from Magento send() method, only attached image to sended email
    protected function _send13()
    {
        $translate = Mage::getSingleton('core/translate');

        /* @var $translate Mage_Core_Model_Translate */

        $translate->setTranslateInline(false);

        $errors = array();

        $this->_emailModel = Mage::getModel('core/email_template');

        $message = nl2br(htmlspecialchars($this->_sender['message']));
        $sender  = array(
            'name' => strip_tags($this->_sender['name']),
            'email' => strip_tags($this->_sender['email'])
            );

        foreach($this->_emails as $key => $email) {
            // <<< start aitoc customization
            $fileContents = file_get_contents($this->_getSharedImgPath()); /*(Here put the filename with full path of file, which have to be send)*/
            $this->_emailModel->getMail()->createAttachment($fileContents, 'image/png');
            // >>> end aitoc customization
            $this->_emailModel->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_SENDFRIEND_EMAIL_TEMPLATE),
                $sender,
                $email,
                $this->_names[$key],
                array(
                    'name'          => $this->_names[$key],
                    'email'         => $email,
                    'product_name'  => $this->_product->getName(),
                    'product_url'   => $this->_product->getProductUrl(),
                    'message'       => $message,
                    'sender_name'   => strip_tags($this->_sender['name']),
                    'sender_email'  => strip_tags($this->_sender['email']),
                    'product_image' => Mage::helper('catalog/image')->init($this->_product, 'small_image')->resize(75),
                )
            );
        }

        $translate->setTranslateInline(true);

        return $this;

    }

    //copied from Magento send() method, only attached image to sended email
    protected function _send14()
    {
        if ($this->isExceedLimit()){
            Mage::throwException(Mage::helper('sendfriend')->__('You have exceeded limit of %d sends in an hour', $this->getMaxSendsToFriend()));
        }

        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate = Mage::getModel('core/email_template');

        $message = nl2br(htmlspecialchars($this->getSender()->getMessage()));
        $sender  = array(
            'name'  => $this->_getHelper()->htmlEscape($this->getSender()->getName()),
            'email' => $this->_getHelper()->htmlEscape($this->getSender()->getEmail())
        );

        $mailTemplate->setDesignConfig(array(
            'area'  => 'frontend',
            'store' => Mage::app()->getStore()->getId()
        ));

        foreach ($this->getRecipients()->getEmails() as $k => $email) {
            $name = $this->getRecipients()->getNames($k);
            // <<< start aitoc customization
            $fileContents = file_get_contents($this->_getSharedImgPath()); /*(Here put the filename with full path of file, which have to be send)*/
            $mailTemplate->getMail()->createAttachment($fileContents, 'image/png');
            // >>> end aitoc customization
            $mailTemplate->sendTransactional(
                $this->getTemplate(),
                $sender,
                $email,
                $name,
                array(
                    'name'          => $name,
                    'email'         => $email,
                    'product_name'  => $this->getProduct()->getName(),
                    'product_url'   => $this->getProduct()->getUrlInStore(),
                    'message'       => $message,
                    'sender_name'   => $sender['name'],
                    'sender_email'  => $sender['email'],
                    'product_image' => Mage::helper('catalog/image')->init($this->getProduct(),
                        'small_image')->resize(75),
                )
            );
        }

        $translate->setTranslateInline(true);
        $this->_incrementSentCount();

        return $this;
    }
}