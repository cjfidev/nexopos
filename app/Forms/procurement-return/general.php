<?php

use App\Crud\ProviderCrud;
use App\Models\Provider;
use App\Services\Helper;

return [
    'label' => __( 'Procurement Return' ),
    'fields' => [
        [
            'type' => 'date',
            'name' => 'return_date',
            'label' => __( 'Return Date' ),
            'description' => __( 'If you would like to define a custom return date.' ),
        ],
        [
            'type' => 'search-select',
            'name' => 'provider_id',
            'props' => ProviderCrud::getFormConfig(),
            'value' => $procurement->provider_id ?? '',
            'validation' => 'required',
            'options' => Helper::toJsOptions( Provider::get(), [ 'id', 'first_name' ] ),
            'label' => __( 'Provider' ),
            'description' => __( 'Determine what is the actual provider of the current procurement.' ),
        ],
        [
            'type' => 'text',
            'name' => 'notes',
            'label' => __( 'Notes' ),
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'value' => '1',
            'validation' => 'required',
            'options' => Helper::kvToJsOptions( [
                '1' => __( 'Pending' ),
                '0' => __( 'Cancel' ),
                '2' => __( 'Finish' ),
            ] ),
            'label' => __( 'Status' ),
        ],
    ],
];
