
<?php
class Escoin_Representative_Model_Entity_Salesrep extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();

			$this->_options[] = array(
                    'value' => '',
                    'label' => 'Choose Option..'
            );
            $reps = Mage::getModel('representative/representative')->getCollection();
            foreach ($reps as $rep) {
            	

            	$this->_options[] = array(
                    'value' => $rep->getEntityId(),
                    'label' => $rep->getRepresentative()
            	);

            }
                       
        }
 
        return $this->_options;
    }
}











