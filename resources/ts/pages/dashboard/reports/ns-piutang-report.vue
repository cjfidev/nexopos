<template>
    <div id="report-section" class="px-4">
        <div class="flex -mx-2">
            <div class="px-2">
                <ns-date-time-picker :field="startDateField"></ns-date-time-picker>
            </div>
            <div class="px-2">
                <ns-date-time-picker :field="endDateField"></ns-date-time-picker>
            </div>
            <div class="px-2">
                <div class="ns-button success">
                    <button @click="loadReport()" class="rounded flex justify-between shadow py-1 items-center px-2">
                        <i class="las la-sync-alt text-xl"></i>
                        <span class="pl-2">{{ __( 'Load' ) }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex -mx-2 mt-2">
            <div class="px-2">
                <button @click="printPiutangReport()" class="rounded flex justify-between bg-input-button shadow py-1 items-center text-primary px-2">
                    <i class="las la-print text-xl"></i>
                    <span class="pl-2">{{ __( 'Print' ) }}</span>
                </button>
            </div>
        </div>

        <div id="piutang-report" class="anim-duration-500 fade-in-entrance">
            <div class="flex w-full">
                <div class="my-4 flex justify-between w-full">
                    <div class="text-secondary">
                        <ul>
                            <li class="pb-1 border-b border-dashed" v-html="__( 'Range : {date1} &mdash; {date2}' ).replace( '{date1}', startDateField.value ).replace( '{date2}', endDateField.value )"></li>
                            <li class="pb-1 border-b border-dashed">{{ __( 'Document : Account Receivable Report' ) }}</li>
                            <li class="pb-1 border-b border-dashed">{{ __( 'By : {user}' ).replace( '{user}', ns.user.username ) }}</li>
                        </ul>
                    </div>
                    <div>
                        <img class="w-24" :src="storeLogo" :alt="storeName">
                    </div>
                </div>
            </div>

            <div class="bg-box-background shadow rounded my-4">
                <div class="border-b border-box-edge">
                    <table class="table ns-table w-full">
                        <thead class="text-primary">
                            <tr>
                                <th class="border p-2 text-left">{{ __( 'No' ) }}</th>
                                <th class="border p-2 text-left">{{ __( 'Customer' ) }}</th>
                                <th width="150" class="border p-2 text-right">{{ __( 'Credit Limit' ) }}</th>
                                <th width="120" class="border p-2 text-center">{{ dateRangeDisplay }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-primary">
                            <tr v-for="(product, index) of result" :key="product.id || index">
                                <td class="p-2 border">{{ index + 1 }}</td>
                                <td class="p-2 border">{{ product.customer }}</td>
                                <td class="p-2 border text-right">{{ nsCurrency(product.last_add_amount || 0) }}</td>
                                <td class="p-2 border text-right">{{ nsCurrency(product.credit) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="text-primary">
                                <td class="p-2 border" colspan="3">{{ __( 'Total' ) }}</td>
                                <td class="p-2 border text-right">{{ nsCurrency(summary.total_credit) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import nsDatepicker from "~/components/ns-datepicker.vue";
import { default as nsDateTimePicker } from '~/components/ns-date-time-picker.vue';
import { nsHttpClient, nsSnackBar } from '~/bootstrap';
import { __ } from '~/libraries/lang';
import { nsCurrency } from '~/filters/currency';

export default {
    name: 'ns-piutang-report',
    props: ['storeLogo', 'storeName'],
    components: {
        nsDatepicker,
        nsDateTimePicker,
    },
    data() {
        return {
            startDateField: {
                name: 'start_date',
                type: 'datetime',
                value: moment().startOf('month').startOf('day').format(),
            },
            endDateField: {
                name: 'end_date',
                type: 'datetime',
                value: moment().endOf('day').format(),
            },
            result: [],
            isLoading: false,
            ns: window.ns,
            summary: {},
        };
    },
    computed: {
        dateRangeDisplay() {
            const start = this.startDateField.value ? moment(this.startDateField.value) : null;
            const end = this.endDateField.value ? moment(this.endDateField.value) : null;
            if (!start || !end) return '';
            return `${start.date()} / ${end.date()}`;
        }
    },
    methods: {
        __,
        nsCurrency,

        printPiutangReport() {
            this.$htmlToPaper('piutang-report');
        },

        loadReport() {
            const startDate = this.startDateField.value;
            const endDate = this.endDateField.value;

            if (!startDate || !endDate) {
                return nsSnackBar.error(__('Unable to proceed. Select a correct time range.')).subscribe();
            }

            const startMoment = moment(startDate);
            const endMoment = moment(endDate);

            if (endMoment.isBefore(startMoment)) {
                return nsSnackBar.error(__('Unable to proceed. The current time range is not valid.')).subscribe();
            }

            this.isLoading = true;

            nsHttpClient.post('/api/reports/piutang-report/get', {
                startDate,
                endDate,
            }).subscribe({
                next: (response) => {
                    this.isLoading = false;
                    this.result = response.result;
                    this.summary = response.summary;
                },
                error: (error) => {
                    this.isLoading = false;
                    nsSnackBar.error(error.message).subscribe();
                }
            });
        },
    }
};
</script>
