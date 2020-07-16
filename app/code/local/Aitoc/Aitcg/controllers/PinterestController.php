<?php

/**
 * Created by PhpStorm.
 * User: artemsklarik
 * Date: 24.08.16
 * Time: 16:30
 */
class Aitoc_Aitcg_PinterestController extends Mage_Core_Controller_Front_Action
{
    public function redirectAction()
    {
        $code = $_GET['code'];

        $apiData = array(
            'client_id'       => Mage::getStoreConfig('catalog/aitcg/aitcg_pinterest_client_id'),
            'client_secret'   => Mage::getStoreConfig('catalog/aitcg/aitcg_pinterest_client_secret'),
            'grant_type'      => 'authorization_code',
            'code'            =>  $code
        );

        $apiHost = 'https://api.pinterest.com/v1/oauth/token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiHost);
        curl_setopt($ch, CURLOPT_POST, count($apiData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $jsonData = curl_exec($ch);
        curl_close($ch);

        $user = json_decode($jsonData);

        var_dump($user);

        $token = $user->access_token;

        Mage::getSingleton('core/session')->setPinterestToken($token);

        $this->getResponse()->setBody("
            <script>
                window.close();
            </script>
        ");
    }
}