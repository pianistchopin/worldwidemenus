<?php
class Custom_Price_Model_Observer
{
	public function checkCart(Varien_Event_Observer $observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		if ($params['custom_layout']) {
			if ($params['custom_layout'] == 1) {
				$item = $observer->getQuoteItem();
				$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
				$qty = $_customOptions['info_buyRequest']['qty'];
				$customPrice = $_customOptions['info_buyRequest']['custom_total_price'];
				$item = ($item->getParentItem() ? $item->getParentItem() : $item);
				if ($params['foil_print_branding'] == 0) {
					$item->setCustomPrice($customPrice);
					$item->setOriginalCustomPrice($customPrice);
					$item->getProduct()->setIsSuperMode(true);
				} else {
					$customPrice = (($customPrice * $qty) - (40 * ($qty - 1))) / $qty;
					$item->setCustomPrice($customPrice);
					$item->setOriginalCustomPrice($customPrice);
					$item->getProduct()->setIsSuperMode(true);
				}
			}
		}
	}
}
