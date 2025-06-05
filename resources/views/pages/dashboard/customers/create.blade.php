@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="h-full flex flex-col flex-auto">
    @include(Hook::filter('ns-dashboard-header-file', '../common/dashboard-header'))
    <div class="px-4 flex-auto flex flex-col" id="dashboard-content">
        @include('common.dashboard.title')

        <ns-crud-form 
            return-url="{{ url('/dashboard/customers') }}"
            submit-url="{{ url('/api/crud/ns.customers') }}"
            src="{{ url('/api/crud/ns.customers/form-config') }}">
            
            <!-- Default Slot -->
            <template v-slot:title>Customer Name</template>
            <template v-slot:save>Save Customer</template>

            <!-- Tambahkan Slot untuk QR Code -->
            <template v-slot:after-save="{ entry }">
                <div v-if="entry && entry.customer_no" class="mt-6 border p-4 rounded shadow bg-white">
                    <h3 class="text-lg font-semibold mb-2">QR Code for Customer No</h3>
                    <img :src="getQRCodeUrl(entry.id)" alt="QR Code" class="w-48 h-48 border rounded shadow mx-auto" />
                    
                    <!-- Tombol Download -->
                    <div class="mt-4 text-center">
                        <a :href="getQRCodeUrl(entry.id)" download class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Download QR Code</a>
                    </div>
                </div>
            </template>
        </ns-crud-form>
    </div>
</div>
@endsection

@push('javascript')
<script>
    // Fungsi helper untuk ambil URL QR Code
    function getQRCodeUrl(customerId) {
        return "{{ ns()->route('ns.crud.action', ['namespace' => 'ns.customers', 'id' => '__ID__', 'action' => 'generate_qrcode']) }}".replace('__ID__', customerId);
    }

    window.getQRCodeUrl = getQRCodeUrl;

    document.addEventListener('DOMContentLoaded', () => {
        if (window.ns && window.ns.crud && window.ns.crud.form) {
            window.ns.crud.form.on('after-save', (entry) => {
                console.log('Customer saved:', entry);
            });
        }
    });
</script>
@endpush