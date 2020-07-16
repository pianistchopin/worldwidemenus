<?php
class AdjustWare_Icon_Block_Adminhtml_Icon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'adjicon';
        $this->_controller = 'adminhtml_icon';
        
        $this->_addButton('save2', array(
            'label'     => Mage::helper('adjicon')->__('Save and Continue Edit'),
            'onclick'   => 'sendAndContinue()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function sendAndContinue(){
                $('edit_form').action += 'continue/1';
                editForm.submit();
            }
        ";
        $this->_formScripts[] = "
            editForm.submit = function()
            {
                var MaxFileUploads = ".ini_get('max_file_uploads').";
                var files = $('adjicon_images').select('input[type=\"file\"]');
                var count = files.length;
                for (var i = 0; i < count; i++)
                {
                    if(files[i].value == '')
                    {
                        files[i].remove();
                    }
                }
                files = $('adjicon_images').select('input[type=\"file\"]');
                count = files.length;
                if (files.length > MaxFileUploads)
                {
                    var FilesNotUploaded = '';
                    for (i = MaxFileUploads; i < count; i++)
                    {
                        FilesNotUploaded += files[i].value + '\\n';
                    }
                    var message = '".$this->helper('adjicon')->jsQuoteEscape($this->__('You are trying to upload more files than allowed by your server\'s configuration.\\nYou can upload only %d file(s) at a time.\\nThe following file(s) will not be uploaded:\\n%s'))."';
                    alert(message.replace('%d', MaxFileUploads).replace('%s', FilesNotUploaded));
                }
                if(this.validator.validate())
                {
                    this._submit();
                }
            }";
    }

    public function getHeaderText()
    {
        return Mage::helper('adjicon')->__('Attribute Icons');
    }
}