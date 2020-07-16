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

class MT_Exitoffer_Model_Module_License
{
    public function update()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $response = $this->request($this->getSystemConfigKeyArray());
        $requestStatus = $response->getStatus();
        $responseBody = json_decode($response->getRawBody());

        if ($requestStatus == 200) {
            foreach ($responseBody as $key) {
                if ($key->status == 1 && $key->success != '') {
                    Mage::getConfig()->saveConfig('mtlicense/'.$key->module.'/key', $key->key);
                    Mage::getConfig()->saveConfig('mtlicense/'.$key->module.'/key_status', $key->success);
                    Mage::getConfig()->saveConfig('mtlicense/'.$key->module.'/key_input', '');
                    $session->addSuccess('License ('.$key->key.') has been added successful. ');
                }

                if ($key->status != 1) {
                    Mage::getConfig()->saveConfig('mtlicense/'.$key->module.'/key_input', '');
                    $session->addError('Error with license key ('.$key->key.'): '.$key->error);
                }

                if ($key->key == Mage::getStoreConfig('mtlicense/'.$key->module.'/key', 0)) {
                    if ($key->error) {
                        Mage::getConfig()->saveConfig('mtlicense/'.$key->module.'/key_status', $key->error);
                    }

                    if ($key->success) {
                        Mage::getConfig()->saveConfig('mtlicense/'.$key->module.'/key_status', $key->success);
                    }
                }
            }
        } elseif ($requestStatus != 401 &&  $requestStatus != 400) {
            $session->addError('Server not response. Please contact with us by email support@magetrend.com');
        }
    }

    public function check()
    {
        $key = $this->getKey();
        $response = $this->request(array(
            'eop' => base64_encode($key)
        ));
        $requestStatus = $response->getStatus();
        $responseBody = json_decode($response->getRawBody());

        if ($requestStatus == 200) {
            if ($responseBody[0]->status != 1) {
                Mage::getSingleton('adminhtml/session')->addError('Error: '.$responseBody[0]->error.' Please go to "System >> Configuration >> MageTrend Extensions >> Licensing" and add the license key.');
                Mage::getConfig()->saveConfig(Mage::app()->getRequest()->getParam('section').'/general/is_active', 0);
            }
        }
    }

    public function request($keyArray)
    {
        $httpClient = new Varien_Http_Client();
        $httpClient->setUri('https://www.magetrend.com/mtlicense/api/checkKey/')
            ->setMethod('POST')
            ->setConfig(array(
                'maxredirects' => 0,
                'timeout' => 5,
            ));

        $httpClient->setParameterPost('url', $this->getUrlArray());
        $httpClient->setParameterPost('key', $keyArray);
        $response = $httpClient->request();

        return $response;
    }

    public function getUrlArray()
    {
        $stores = Mage::app()->getStores();
        $urlArray = array();
        if (count($stores)> 0 )  {
            foreach ($stores as $store) {
                $urlArray[] = base64_encode(Mage::getStoreConfig('web/unsecure/base_url', $store->getId()));
            }
        }
        return $urlArray;
    }

    public function getSystemConfigKeyArray()
    {
        $params = Mage::app()->getRequest()->getParams();
        $keyArray = array();
        foreach ($params['groups'] as $moduleKey => $options) {
            if(isset($options['fields']['key_input']['value']) && !empty($options['fields']['key_input']['value']) ) {
                $keyArray[$moduleKey] = base64_encode($options['fields']['key_input']['value']);
            }
        }
        return $keyArray;
    }

    public function getKey()
    {
        return Mage::getStoreConfig('mtlicense/eop/key');
    }

}
