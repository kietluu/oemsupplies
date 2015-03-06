<?php
/**
 * Escoin_Representative extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category   	Escoin
 * @package		Escoin_Representative
 * @copyright  	Copyright (c) 2013
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Representative edit form tab
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Block_Adminhtml_Representative_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form{	
	/**
	 * prepare the form
	 * @access protected
	 * @return Representative_Representative_Block_Adminhtml_Representative_Edit_Tab_Form
	 * @author Ultimate Module Creator
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('representative_');
		$form->setFieldNameSuffix('representative');
		$this->setForm($form);
		$fieldset = $form->addFieldset('representative_form', array('legend'=>Mage::helper('representative')->__('Representative')));

		$fieldset->addField('representative', 'text', array(
			'label' => Mage::helper('representative')->__('Sales Representative'),
			'name'  => 'representative',
			'note'	=> $this->__('Name of the sales representative'),
			'required'  => true,
			'class' => 'required-entry',

		));

		$fieldset->addField('email', 'text', array(
			'label' => Mage::helper('representative')->__('Email'),
			'name'  => 'email',
			'note'	=> $this->__('Email associated to sales representive'),
			'required'  => true,
			'class' => 'required-entry',

		));

                
		$fieldset->addField('customer', 'multiselect', array(
			'label' => Mage::helper('representative')->__('Associated Customer'),
			'name'  => 'customer[]',
                        'required' => false,
                        'values' =>Mage::getSingleton('representative/representative')->getCustomers(),
                        //'value' => array(1,5),

		));
		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('representative')->__('Status'),
			'name'  => 'status',
			'values'=> array(
				array(
					'value' => 1,
					'label' => Mage::helper('representative')->__('Enabled'),
				),
				array(
					'value' => 0,
					'label' => Mage::helper('representative')->__('Disabled'),
				),
			),
		));
		if (Mage::app()->isSingleStoreMode()){
			$fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            Mage::registry('current_representative')->setStoreId(Mage::app()->getStore(true)->getId());
		}
		if (Mage::getSingleton('adminhtml/session')->getRepresentativeData()){
			$form->setValues(Mage::getSingleton('adminhtml/session')->getRepresentativeData());
			Mage::getSingleton('adminhtml/session')->setRepresentativeData(null);
		}
		elseif (Mage::registry('current_representative')){
			$form->setValues(Mage::registry('current_representative')->getData());
		}
		return parent::_prepareForm();
	}
}