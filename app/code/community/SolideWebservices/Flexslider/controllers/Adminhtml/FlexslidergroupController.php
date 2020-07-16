<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Adminhtml_FlexslidergroupController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout();
		$this->_setActiveMenu('cms/flexslider');
		return $this;
	}

	public function indexAction() {
		$this->_initAction();
		$this->_addContent($this->getLayout()->createBlock('flexslider/adminhtml_group'));
		$this->renderLayout();
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('cms/flexslider/group');
	}

	/**
	 * Forward to the edit action so the user can add a new slide
	 */
	public function newAction() {
		$this->_forward('edit');
	}

	/**
	 * Display the edit/add form
	 */
	public function editAction() {
		$groupId = $this->getRequest()->getParam('id');
		$_model	 = Mage::getModel('flexslider/group')->load($groupId);

		if ($_model->getId()) {
			$this->_title($_model->getId() ? Mage::helper('flexslider')->__('Edit '). $_model->getTitle() : Mage::helper('flexslider')->__('Create a Group'));

			Mage::register('group_data', $_model);
			Mage::register('current_group', $_model);

			$this->_initAction();

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->renderLayout();
		} else {
			$this->_title(Mage::helper('flexslider')->__('Create a Group'));

			$_model	 = Mage::getModel('flexslider/group');
			Mage::register('group_data', $_model);
			Mage::register('current_group', $_model);

			$this->_initAction();

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->renderLayout();
		}
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$_model = Mage::getModel('flexslider/group');

			if (isset($data['categories'])) {
				$data['categories'] = explode(',',$data['categories']);
				if (is_array($data['categories'])) {
					$data['categories'] = array_unique($data['categories']);
				}
			}

			if (isset($data['product_sku'])) {
				$data['product_sku'] = explode(', ',$data['product_sku']);
				if (is_array($data['product_sku'])) {
					$data['product_sku'] = array_unique($data['product_sku']);
				}
			}

			$_model->setData($data)
					->setId($this->getRequest()->getParam('id'));

			try {
				$_model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('flexslider')->__('Group was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $_model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('flexslider')->__('Unable to find group to save'));
		$this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('flexslider/group');

				$model->setId($this->getRequest()->getParam('id'))
						->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Group was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction() {
		$IDList = $this->getRequest()->getParam('group');
		if(!is_array($IDList)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select group(s)'));
		} else {
			try {
				foreach ($IDList as $itemId) {
					$_model = Mage::getModel('flexslider/group')
							->setIsMassDelete(true)->load($itemId);
					$_model->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully deleted', count($IDList)
						)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massStatusAction() {
		$IDList = $this->getRequest()->getParam('group');

		if(!is_array($IDList)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select group(s)'));
		}
		else {
			if (!empty($IDList)) {
				try {
					foreach ($IDList as $itemId) {
						$group = Mage::getModel('flexslider/group')
								->load($itemId)
								->setIsActive($this->getRequest()->getParam('status'))
								->setIsMassStatus(true)
								->save();
					}
					$this->_getSession()->addSuccess(
							$this->__('Total of %d record(s) were successfully updated', count($IDList))
					);

				} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}

		}

		$this->_redirect('*/*/index');
	}

	public function categoriesJsonAction() {
		$groupId = $this->getRequest()->getParam('id');
		$_model	 = Mage::getModel('flexslider/group')->load($groupId);
		Mage::register('group_data', $_model);
		Mage::register('current_group', $_model);

		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('flexslider/adminhtml_group_edit_tab_category')
				->getCategoryChildrenJson($this->getRequest()->getParam('category'))
		);
	}

	protected function _title($text = null, $resetIfExists = true) {
		if (is_string($text)) {
			$this->_titles[] = $text;
		} elseif (-1 === $text) {
			if (empty($this->_titles)) {
				$this->_removeDefaultTitle = true;
			} else {
				array_pop($this->_titles);
			}
		} elseif (empty($this->_titles) || $resetIfExists) {
			if (false === $text) {
				$this->_removeDefaultTitle = false;
				$this->_titles = array();
			} elseif (null === $text) {
				$this->_removeDefaultTitle = true;
				$this->_titles = array();
			}
		}
		return $this;
	}

}
