<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Adminhtml_FlexsliderslideController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout();
		$this->_setActiveMenu('cms/flexslider');
		return $this;
	}

	public function indexAction() {
		$this->_initAction();
		$this->renderLayout();
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('cms/flexslider/slide');
	}

	/**
	 * Display the slide grid
	 */
	public function gridAction() {
		$this->getResponse()
			->setBody($this->getLayout()->createBlock('flexslider/adminhtml_slide_grid')->toHtml());
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
		$slide = $this->_initSlideModel();
		$this->loadLayout();

		if ($headBlock = $this->getLayout()->getBlock('head')) {
			$titles = array('Flexslider');

			if ($slide) {
				array_unshift($titles, 'Edit '. $slide->getTitle());
			}
			else {
				array_unshift($titles, 'Create a Slide');
			}

			$headBlock->setTitle(implode(' - ', $titles));
		}

		$this->renderLayout();
	}

	/**
	 * Save the slide
	 */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost('slide')) {

			$slide = Mage::getModel('flexslider/slide')
				->setData($data)
				->setId($this->getRequest()->getParam('id'));

			try {
				if((empty($data['video_id']) && empty($data['hosted_image_url'])) || (!isset($data['video_id']) && !isset($data['hosted_image_url']))){
					$this->_handleImageUpload($slide);
				}

				$slide->save();
				$this->_getSession()->addSuccess($this->__('Slide was saved'));
			}
			catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
				Mage::logException($e);
			}

			if ($this->getRequest()->getParam('back') && $slide->getId()) {
				$this->_redirect('*/*/edit', array('id' => $slide->getId()));
				return;
			}
		}
		else {
			$this->_getSession()->addError($this->__('There was no data to save'));
		}

		$this->_redirect('*/*');
	}

	/**
	 * Upload an image and assign it to the model
	 *
	 * @param SolideWebservices_Flexslider_Model_Slide $slide
	 * @param string $field = 'image'
	 */
	protected function _handleImageUpload(SolideWebservices_Flexslider_Model_Slide $slide, $field = 'image') {
		$data = $slide->getData($field);

		if (isset($data['value'])) {
			$slide->setData($field, $data['value']);
		}

		if (isset($data['delete']) && $data['delete'] == '1') {
			$slide->setData($field, '');
		}

		if ($filename = Mage::helper('flexslider/image')->uploadImage($field)) {
			$slide->setData($field, $filename);
		}
	}

	/**
	 * Delete a Flexslider slide
	 */
	public function deleteAction() {
		if ($slideId = $this->getRequest()->getParam('id')) {
			$slide = Mage::getModel('flexslider/slide')->load($slideId);

			if ($slide->getId()) {
				try {
					$slide->delete();
					$this->_getSession()->addSuccess($this->__('The slide was deleted'));
				}
				catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}

		$this->_redirect('*/*');
	}

	/**
	 * Batch delete multiple Flexslider slides
	 *
	 */
	public function massDeleteAction() {
		$slideIds = $this->getRequest()->getParam('slide');

		if (!is_array($slideIds)) {
			$this->_getSession()->addError($this->__('Please select slide(s)'));
		}
		else {
			if (!empty($slideIds)) {
				try {
					foreach ($slideIds as $slideId) {
						$slide = Mage::getSingleton('flexslider/slide')->load($slideId);

						Mage::dispatchEvent('flexslider_controller_slide_delete', array('flexslider_slide' => $slide));

						$slide->delete();
					}

					$this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($slideIds)));
				}
				catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}

		$this->_redirect('*/*');
	}

	/**
	 * Batch edit multiple Flexslider slides
	 *
	 */
	public function massStatusAction() {
		$slideIds = $this->getRequest()->getParam('slide');
		$data = array('is_active'=>1);

		if (!is_array($slideIds)) {
			$this->_getSession()->addError($this->__('Please select slide(s)'));
		}
		else {
			if (!empty($slideIds)) {
				try {
					foreach ($slideIds as $slideId) {
						$slide = Mage::getSingleton('flexslider/slide')
							->load($slideId)
							->setIsEnabled($this->getRequest()->getParam('status'))
							->setIsMassupdate(true)
							->save();
					}

				$this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($slideIds)));

				}
				catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}

		$this->_redirect('*/*');
	}

	/**
	 * Initialise the slide model
	 *
	 * @return null|SolideWebservices_Flexslider_Model_Slide
	 */
	protected function _initSlideModel() {
		if ($slideId = $this->getRequest()->getParam('id')) {
			$slide = Mage::getModel('flexslider/slide')->load($slideId);

			if ($slide->getId()) {
				Mage::register('flexslider_slide', $slide);
			}
		}

		return Mage::registry('flexslider_slide');
	}

}
