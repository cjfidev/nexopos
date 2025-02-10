<template>
    <div class="w-95vw flex flex-col h-95vh shadow-lg md:w-3/5-screen lg:w-2/5-screen md:h-3/5-screen ns-box">
        <!-- ... (kode lainnya tetap sama) ... -->
    </div>
</template>

<script lang="ts">
import { __ } from '~/libraries/lang';
import FormValidation from '~/libraries/form-validation';
import popupResolver from '~/libraries/popup-resolver';
import popupCloser from '~/libraries/popup-closer';
import { forkJoin } from 'rxjs';
import { nsSnackBar } from '~/bootstrap';

declare const POS;

export default {
    name: 'ns-pos-quick-product-popup',
    props: [ 'popup' ],
    methods: {
        __,
        popupCloser,
        popupResolver,

        close() {
            this.popupResolver( false );
        },

        async addProduct() {
            // ... (kode lainnya tetap sama) ...
        },

        loadData() {
            this.loaded     =   false;

            forkJoin(
                nsHttpClient.get( `/api/units` ),
                nsHttpClient.get( `/api/taxes/groups` ),
            ).subscribe({
                next: ( result ) => {
                    this.units          =   result[0];
                    this.tax_groups     =   result[1];

                    // Map units to options for the unit_id field
                    const unitOptions = this.units.map( unit => {
                        return {
                            label: unit.name,
                            value: unit.id,
                        };
                    });

                    // Update the unit_id field with the mapped options
                    this.fields = this.fields.map( field => {
                        if ( field.name === 'unit_id' ) {
                            field.options = unitOptions;
                            field.value = this.options.ns_pos_quick_product_default_unit;
                        }
                        return field;
                    });

                    this.buildForm();
                },
                error: ( error ) => {
                    // Handle error
                }
            })
        },

        buildForm() {
            this.fields     =   this.validation.createFields( this.fields );
            this.loaded     =   true;

            setTimeout(() => {
                this.$el.querySelector( '#name' ).select();
            }, 100);
        }
    },
    computed: {
        form() {
            return this.validation.extractFields( this.fields );
        }
    },
    data() {
        return {
            units: [],
            options: POS.options.getValue(),
            tax_groups: [],
            loaded: false,
            validation: new FormValidation,
            fields: [
                {
                    label: __( 'Name' ),
                    name: 'name',
                    type: 'text',
                    value: 'Jasa Masak/Seduh',
                    validation: 'required',
                }, {
                    label: __( 'Product Type' ),
                    name: 'product_type',
                    type: 'select',
                    description: __( 'Define the product type.' ),
                    options: [{
                        label: __( 'Normal' ),
                        value: 'product',
                    }, {
                        label: __( 'Dynamic' ),
                        value: 'dynamic',
                    }],
                    value: 'product',
                    validation: 'required',
                }, {
                    label: __( 'Rate' ),
                    name: 'rate',
                    type: 'text',
                    description: __( 'In case the product is computed based on a percentage, define the rate here.' ),
                    validation: 'required',
                    show( form ) {
                        return form.product_type === 'dynamic';
                    }
                }, {
                    label: __( 'Unit Price' ),
                    name: 'unit_price',
                    type: 'text',
                    description: __( 'Define what is the sale price of the item.' ),
                    validation: '',
                    value: 1000,
                    show( form ) {
                        return form.product_type === 'product';
                    }
                }, {
                    label: __( 'Quantity' ),
                    name: 'quantity',
                    type: 'text',
                    value: 1,
                    description: __( 'Set the quantity of the product.' ),
                    validation: '',
                    show( form ) {
                        return form.product_type === 'product';
                    }
                }, {
                    label: __( 'Unit' ),
                    name: 'unit_id',
                    type: 'select',
                    options: [], // Options will be populated in loadData()
                    description: __( 'Assign a unit to the product.' ),
                    validation: '',  
                    show( form ) {
                        return form.product_type === 'product';
                    }                  
                }, {
                    label: __( 'Tax Type' ),
                    name: 'tax_type',
                    type: 'select',
                    options: [
                        {
                            label: __( 'Disabled' ),
                            value: '',
                        }, {
                            label: __( 'Inclusive' ),
                            value: 'inclusive',
                        }, {
                            label: __( 'Exclusive' ),
                            value: 'exclusive'
                        }
                    ],
                    description: __( 'Define what is tax type of the item.' ),  
                    show( form ) {
                        return form.product_type === 'product';
                    }             
                }, {
                    label: __( 'Tax Group' ),
                    name: 'tax_group_id',
                    type: 'select',
                    options: [], // Options will be populated in loadData()
                    description: __( 'Choose the tax group that should apply to the item.' ),  
                    show( form ) {
                        return form.product_type === 'product';
                    }                 
                }
            ]
        }
    },
    mounted() {
        this.popupCloser();
        this.loadData();
    }
}
</script>