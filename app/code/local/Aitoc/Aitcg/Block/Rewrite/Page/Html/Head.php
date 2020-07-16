<?php

class Aitoc_Aitcg_Block_Rewrite_Page_Html_Head extends Mage_Page_Block_Html_Head 
{
    public function aitocAddPositionedItem($aItem, $aPosition)
    {
        if(array_key_exists(key($aItem), $this->getItems()))
        {
            return false;
        }
        
        if(array_key_exists($aPosition['item'], $this->getItems()))
        {
            $insertion = 0;
            foreach($this->getItems()as $key=>$item) {
                if($key===$aPosition['item'])
                {
                    break;
                }
                $insertion++;
            }
            
            switch($aPosition['place'])
            {
                case 'after':
                    $insertion = $insertion + 1;
                    break;
                case 'before':
                    break;
                default :
                    return false;      
            }
            //$this->_hasDataChanges = true;
            //insert in to elements
            $arrayHead = array_slice($this->getItems(),0,$insertion);
            $arrayTail = array_slice($this->getItems(),$insertion);
            $this->unsetData('items');
            $this->setItems(array_merge($arrayHead,$aItem,$arrayTail));
            return;
        }
        return  false;
    }
}
