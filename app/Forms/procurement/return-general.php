<?php

use App\Crud\ProviderCrud;
use App\Models\Provider;
use App\Services\Helper;

return [
    'label' => __( 'Procurement' ),
    'fields' => [
        [
            'type' => 'text',
            'name' => 'invoice_reference',
            'value' => $procurement->invoice_reference ?? '',
            'label' => __( 'Invoice Number' ),
            'description' => __( 'If the procurement has been issued outside of NexoPOS, please provide a unique reference.' ),
        ],
        [
            'type' => 'date',
            'name' => 'return_date',
            'value' => $procurement->return_date ?? ns()->date->now()->format( 'Y-m-d' ),
            'label' => __( 'Return Date' ),
            'description' => __( 'If you would like to define a custom return date.' ),
        ],  
        [
            'type' => 'date',
            'name' => 'invoice_date',
            'value' => $procurement->invoice_date ?? null,
            'label' => __( 'Invoice Date' ),
            'description' => __( 'If you would like to define a custom invoice date.' ),
        ], 
        // [
        //     'type' => 'switch',
        //     'name' => 'automatic_approval',
        //     'value' => $procurement->automatic_approval ?? 1,
        //     'options' => Helper::kvToJsOptions( [
        //         0 => __( 'No' ),
        //         1 => __( 'Yes' ),
        //     ] ),
        //     'label' => __( 'Automatic Approval' ),
        //     'description' => __( 'Determine if the procurement should be marked automatically as approved once the Delivery Time occurs.' ),
        // ], 
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
    ],
];
