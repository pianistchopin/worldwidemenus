<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Config_Source_Position {

	public function toOptionArray() {

		/* let's see what positions are enabled */
		$selectedEnabled = Mage::helper('flexslider')->getEnabledScope('selected');
		$globalEnabled = Mage::helper('flexslider')->getEnabledScope('global');
		$accountEnabled = Mage::helper('flexslider')->getEnabledScope('customer');
		$checkoutEnabled = Mage::helper('flexslider')->getEnabledScope('checkout');

		return array(
			array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select --'),
                'value' => ''
			),
			array(
                'label' => Mage::helper('adminhtml')->__('Position On Selected CMS Pages, Categories or Products'),
                'value' => array(
                    array('value' => 'CONTENT_TOP', 'label' => Mage::helper('adminhtml')->__('Main Content Top'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'CONTENT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Main Content Bottom'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'RIGHT_TOP', 'label' => Mage::helper('adminhtml')->__('Right Sidebar Top'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'RIGHT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Right Sidebar Bottom'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'LEFT_TOP', 'label' => Mage::helper('adminhtml')->__('Left Sidebar Top'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'LEFT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Left Sidebar Bottom'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'FOOTER_TOP', 'label' => Mage::helper('adminhtml')->__('Footer Top'), 'disabled' => $selectedEnabled == true ? '' : 'disabled'),
					array('value' => 'FOOTER_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Footer Bottom'), 'disabled' => $selectedEnabled == true ? '' : 'disabled')
				)
			),
			array(
                'label' => '---------',
                'value' => array(
                    array('value' => '-', 'label' => Mage::helper('adminhtml')->__('The Following Positions Will Discard Any CMS,'), 'disabled' => 'disabled'),
					array('value' => '--', 'label' => Mage::helper('adminhtml')->__('Category And Product Selections In Group Settings'), 'disabled' => 'disabled'),
					array('value' => '---', 'label' => Mage::helper('adminhtml')->__('These Positions Are Disabled By Default For Optimal Performance'), 'disabled' => 'disabled'),
					array('value' => '----', 'label' => Mage::helper('adminhtml')->__('Enable Them In The General Settings If Desired'), 'disabled' => 'disabled'),
					array('value' => '-----', 'label' => '------', 'disabled' => 'disabled')
				)
			),
			array(
                'label' => Mage::helper('adminhtml')->__('Custom (select this when using template tag or XML)'),
                'value' => array(
                    array('value' => 'CUSTOM', 'label' => Mage::helper('adminhtml')->__('Custom'))
				)
			),
			array(
                'label' => Mage::helper('adminhtml')->__('Show On All Pages (Global)'),
                'value' => array(
                    array('value' => 'ALL_CONTENT_TOP', 'label' => Mage::helper('adminhtml')->__('All Main Content Top'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_CONTENT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('All Main Content Bottom'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_RIGHT_TOP', 'label' => Mage::helper('adminhtml')->__('All Right Sidebar Top'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_RIGHT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('All Right Sidebar Bottom'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_LEFT_TOP', 'label' => Mage::helper('adminhtml')->__('All Left Sidebar Top'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_LEFT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('All Left Sidebar Bottom'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_FOOTER_TOP', 'label' => Mage::helper('adminhtml')->__('All Footer Top'), 'disabled' => $globalEnabled == true ? '' : 'disabled'),
					array('value' => 'ALL_FOOTER_BOTTOM', 'label' => Mage::helper('adminhtml')->__('All Footer Bottom'), 'disabled' => $globalEnabled == true ? '' : 'disabled')
				)
			),
            array(
                'label' => Mage::helper('adminhtml')->__('Customer Based Positions'),
                'value' => array(
					array('value' => 'ACCOUNT_LOGIN_TOP', 'label' => Mage::helper('adminhtml')->__('Account Login Top'), 'disabled' => $accountEnabled == true ? '' : 'disabled'),
					array('value' => 'ACCOUNT_LOGIN_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Account Login Bottom'), 'disabled' => $accountEnabled == true ? '' : 'disabled'),
                    array('value' => 'ACCOUNT_TOP', 'label' => Mage::helper('adminhtml')->__('Account Dashboard Top'), 'disabled' => $accountEnabled == true ? '' : 'disabled'),
					array('value' => 'ACCOUNT_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Account Dashboard Bottom'), 'disabled' => $accountEnabled == true ? '' : 'disabled')
				)
			),
			array(
                'label' => Mage::helper('adminhtml')->__('Checkout Based Positions'),
                'value' => array(
					array('value' => 'CHECKOUT_CART_TOP', 'label' => Mage::helper('adminhtml')->__('Cart Top'), 'disabled' => $checkoutEnabled == true ? '' : 'disabled'),
					array('value' => 'CHECKOUT_CART_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Cart Bottom'), 'disabled' => $checkoutEnabled == true ? '' : 'disabled'),
                    array('value' => 'CHECKOUT_PAGE_TOP', 'label' => Mage::helper('adminhtml')->__('Checkout Page Top'), 'disabled' => $checkoutEnabled == true ? '' : 'disabled'),
					array('value' => 'CHECKOUT_PAGE_BOTTOM', 'label' => Mage::helper('adminhtml')->__('Checkout Page Bottom'), 'disabled' => $checkoutEnabled == true ? '' : 'disabled')
				)
			),
		);

	}

	/**
	 * Options getter
	 *
	 * @return array
	*/
	public function toGridOptionArray() {
		return array(
			'CONTENT_TOP'				=> Mage::helper('adminhtml')->__('Main Content Top'),
			'CONTENT_BOTTOM'			=> Mage::helper('adminhtml')->__('Main Content Bottom'),
			'RIGHT_TOP'					=> Mage::helper('adminhtml')->__('Right Sidebar Top'),
			'RIGHT_BOTTOM'				=> Mage::helper('adminhtml')->__('Right Sidebar Bottom'),
			'LEFT_TOP'					=> Mage::helper('adminhtml')->__('Left Sidebar Top'),
			'LEFT_BOTTOM'				=> Mage::helper('adminhtml')->__('Left Sidebar Bottom'),
			'FOOTER_TOP'				=> Mage::helper('adminhtml')->__('Footer Top'),
			'FOOTER_BOTTOM'				=> Mage::helper('adminhtml')->__('Footer Bottom'),
			'CUSTOM'					=> Mage::helper('adminhtml')->__('Custom'),
			'ALL_CONTENT_TOP'			=> Mage::helper('adminhtml')->__('All Main Content Top'),
			'ALL_CONTENT_BOTTOM'		=> Mage::helper('adminhtml')->__('All Main Content Bottom'),
			'ALL_RIGHT_TOP'				=> Mage::helper('adminhtml')->__('All Right Sidebar Top'),
			'ALL_RIGHT_BOTTOM'			=> Mage::helper('adminhtml')->__('All Right Sidebar Bottom'),
			'ALL_LEFT_TOP'				=> Mage::helper('adminhtml')->__('All Left Sidebar Top'),
			'ALL_LEFT_BOTTOM'			=> Mage::helper('adminhtml')->__('All Left Sidebar Bottom'),
			'ALL_FOOTER_TOP'			=> Mage::helper('adminhtml')->__('All Footer Top'),
			'ALL_FOOTER_BOTTOM'			=> Mage::helper('adminhtml')->__('All Footer Bottom'),
			'ACCOUNT_LOGIN_TOP'			=> Mage::helper('adminhtml')->__('Account Login Top'),
			'ACCOUNT_LOGIN_BOTTOM'		=> Mage::helper('adminhtml')->__('Account Login Bottom'),
			'ACCOUNT_TOP'				=> Mage::helper('adminhtml')->__('Account Top'),
			'ACCOUNT_BOTTOM'			=> Mage::helper('adminhtml')->__('Account Bottom'),
			'CHECKOUT_CART_TOP'			=> Mage::helper('adminhtml')->__('Checkout Cart Top'),
			'CHECKOUT_CART_BOTTOM'		=> Mage::helper('adminhtml')->__('Checkout Cart Bottom'),
			'CHECKOUT_PAGE_TOP'			=> Mage::helper('adminhtml')->__('Checkout Page Top'),
			'CHECKOUT_PAGE_BOTTOM'		=> Mage::helper('adminhtml')->__('Checkout Page Bottom'),
		);
	}

}