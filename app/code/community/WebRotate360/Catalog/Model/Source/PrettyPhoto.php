<?php

class WebRotate360_Catalog_Model_Source_PrettyPhoto
{
    public function toOptionArray()
    {
        return array(

            array(
                'value' => 'default',
                'label' => 'Default'
            ),

            array(
                'value' => 'light_rounded',
                'label' => 'Light Rounded'
            ),

            array(
                'value' => 'dark_rounded',
                'label' => 'Dark Rounded'
            ),

            array(
                'value' => 'dark_square',
                'label' => 'Dark Square'
            ),

            array(
                'value' => 'light_square',
                'label' => 'Light Square'
            ),

            array(
                'value' => 'facebook',
                'label' => 'Facebook'
            ),
        );
    }
}