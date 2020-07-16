<?php
class Aitoc_Aitcg_IndexController extends Mage_Core_Controller_Front_Action
{
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @param bool $svg
     *
     * @param bool $apply_offsets
     *
     * @return Aitoc_Aitcg_Model_Image_Svg
     */
    protected function _processSvg( $apply_offsets = false )
    {
        $request = $this->getRequest();

        $data = $request->getPost('data');
        $model = Mage::getModel('aitcg/image_svg');
        /** @var $model Aitoc_Aitcg_Model_Image_Svg */
        $model
            ->setApplyOffsets($apply_offsets)
            ->setDataType($request->getPost('type'))
            ->initLayer('top', $data)
            ->initLayer('bottom', $request->getPost('data_bottom'))
            ->setPrintType($request->getPost('print_type'))
            ->prepareBackground($request->getPost('background'), $request->getPost('areaOffsetX'), $request->getPost('areaOffsetY'), $request->getPost('print_scale'))
            ->normalizeMask()
            ->processAdditional($request->getPost('additional'));

        return $model;
    }

    public function svgAction()
    {
        $model = $this->_processSvg();
        $data = $model->normalizeSvgData();
        //$data = $mode->getSvgData();

        if ($model->getAdditionalData('order_increment_id'))
        {
            $filename = 'Order_' . $model->getAdditionalData('order_increment_id') . '_Image.svg';
        } else {
            $filename = 'Customer_Product_Image.svg';
        }

        $this
            ->getResponse()->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'image/svg+xml', true)
            ->setHeader('Content-Disposition','attachment; filename="'.$filename.'"',true);
        $this->getResponse()->clearBody();

        //$this->getResponse()->setBody(str_replace('xlink:','',$data));
        $this->getResponse()->setBody($data);
    }

    public function svgtopngAction()
    {
        $model = $this->_processSvg(true);
        $model->resetMaskForPng();
        $data = $model->getSvgData(false, true);
        $data = $model->applyXlinkToData($data);
        $this->getResponse()->setBody($data);
    }
    public function pdfAction()
    {
        $request = $this->getRequest();
        $model = Mage::getModel('aitcg/image_svg');
        $order_increment_id=$request->getParam('order_increment_id',false);
        if ($order_increment_id)
        {
            $filename = 'Order_' .$order_increment_id . '_Image.pdf';
        } else {
            $filename = 'Customer_Product_Image.pdf';
        }
        $this->getResponse()->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            //->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/pdf', true)
            ->setHeader('Content-Disposition','attachment; filename="' . $filename . '"',true);
        $this->getResponse()->clearBody();
        $base64=$this->getRequest()->getParam('data',false);
        if($base64)
        {
            // $imageBlob = base64_decode($base64);
            $imageBlob = str_replace("data:image/png;base64,", "", $base64);

            $imageBlob = base64_decode($imageBlob);
            if (!$imageBlob) die('Unable to decode the string');

            $imagick = new Imagick();
            $imagick->readImageBlob($imageBlob);
            $imagick->setImageFormat("pdf");

            /*$imagick->writeImage(MAGENTO_ROOT.'/media/us-map.pdf');scaleImage */
            $this->getResponse()->setBody($imagick);
            $imagick->clear();
            $imagick->destroy();
        }

    }
    public function pdfoldAction()
    {
        $model = $this->_processSvg();

        $scale = $this->getRequest()->getPost('print_scale');
        $model
            //->resetMaskForPDF()
            ->addWhiteFontForPDF();
        $data = $model->normalizeSvgData();

        if ($model->getAdditionalData('order_increment_id'))
        {
            $filename = 'Order_' . $model->getAdditionalData('order_increment_id') . '_Image.pdf';
        } else {
            $filename = 'Customer_Product_Image.pdf';
        }

        $this->getResponse()->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            //->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/pdf', true)
            ->setHeader('Content-Disposition','attachment; filename="' . $filename . '"',true);
        $this->getResponse()->clearBody();

        //$this->getResponse()->setBody(str_replace('xlink:','',$data));
        $imagick = new Imagick();
        $imagick->readImageBlob($data);
        if ($scale !=1 && $scale <= 15)
        {
            $imagick->scaleImage($scale*$imagick->getImageWidth(), $scale*$imagick->getImageHeight());
        }
        $imagick->setImageFormat("pdf");

        /*$imagick->writeImage(MAGENTO_ROOT.'/media/us-map.pdf');scaleImage */
        $this->getResponse()->setBody($imagick);
        $imagick->clear();
        $imagick->destroy();
    }

    protected function _initSharedImage()
    {
        $id = (string) $this->getRequest()->getParam('id');

        if (!$id)
        {
            return false;
        }

        $sharedImgModel = Mage::getModel('aitcg/sharedimage');
        $sharedImgModel->load($id);
        if($sharedImgModel->productNotExist() || $sharedImgModel->imagesNotExist())
        {
            return false;
        }

        return true;
    }

    public function sharedimageAction()
    {
        if(!$this->_initSharedImage())
        {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $pageId = Mage::getStoreConfig('web/default/cms_no_route');
            if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
                $this->_forward('defaultNoRoute');}
        }

        $this->loadLayout();
        $this->renderLayout();
    }
}