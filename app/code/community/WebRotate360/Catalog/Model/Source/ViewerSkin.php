<?php

class WebRotate360_Catalog_Model_Source_ViewerSkin
{
    public function toOptionArray()
    {
        return array(

            array(
                'value' => 'basic',
                'label' => 'basic'
            ),

            array(
                'value' => 'thin',
                'label' => 'thin'
            ),

            array(
                'value' => 'round',
                'label' => 'round'
            ),

            array(
                'value' => 'retina',
                'label' => 'retina'
            ),

            array(
                'value' => 'empty',
                'label' => 'empty'
            ),
        );
    }
}