<?php
class Escoin_Representative_Model_Entity_Region extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();
            $this->_options[] = array(
                    'value' => '',
                    'label' => 'Choose Option..'
            );
            $this->_options[] = array(
                    'value' => 1,
                    'label' => 'British Columbia'
            );
            $this->_options[] = array(
                    'value' => 2,
                    'label' => 'Alberta'
            );
            $this->_options[] = array(
                    'value' => 3,
                    'label' => 'Saskatchewan'
            );
            $this->_options[] = array(
                    'value' => 4,
                    'label' => 'Manitoba'
            );
            $this->_options[] = array(
                    'value' => 5,
                    'label' => 'Ontario'
            );
            $this->_options[] = array(
                    'value' => 6,
                    'label' => 'Quebec'
            );
            $this->_options[] = array(
                    'value' => 7,
                    'label' => 'Novascotia'
            );
            $this->_options[] = array(
                    'value' => 8,
                    'label' => 'Newfoundland'
            );
            $this->_options[] = array(
                    'value' => 9,
                    'label' => 'Prince Edward Island & Labrador'
            );
            $this->_options[] = array(
                    'value' => 10,
                    'label' => 'Yukon Territories'
            );
            $this->_options[] = array(
                    'value' => 11,
                    'label' => 'Nunavut'
            );
            $this->_options[] = array(
                    'value' => 12,
                    'label' => 'Northwest Territories'
            );
             $this->_options[] = array(
                    'value' => 13,
                    'label' => 'New Brunswick'
            );
             
        }
 
        return $this->_options;
    }
}











