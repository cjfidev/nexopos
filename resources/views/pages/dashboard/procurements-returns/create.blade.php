@extends( 'layout.dashboard' )

@section( 'layout.dashboard.body' )
<div class="h-full flex-auto flex flex-col">
    @include( Hook::filter( 'ns-dashboard-header-file', '../common/dashboard-header' ) )
    <div class="px-4 flex-auto flex flex-col" id="dashboard-content">
        @include( 'common.dashboard.title' )
        <ns-procurement-return
            submit-url="{{ ns()->url( '/api/procurements-returns' ) }}"
            src="{{ ns()->url( '/api/forms/ns.procurement-return' ) }}"
            return-url="{{ ns()->url( '/dashboard/procurements-returns' ) }}">
            <template v-slot:title>{{ __( 'Procurement Name' ) }}</template>
            <template v-slot:error-no-products>{{ __( 'Unable to proceed no products has been provided.' ) }}</template>
            <template v-slot:error-invalid-products>{{ __( 'Unable to proceed, one or more products is not valid.' ) }}</template>
            <template v-slot:error-invalid-form>{{ __( 'Unable to proceed the procurement form is not valid.' ) }}</template>
            <template v-slot:error-no-submit-url>{{ __( 'Unable to proceed, no submit url has been provided.' ) }}</template>
            <template v-slot:search-placeholder>{{ __( 'SKU, Barcode, Product name.' ) }}</template>
        </ns-procurement-return>
    </div>
</div>
@endsection