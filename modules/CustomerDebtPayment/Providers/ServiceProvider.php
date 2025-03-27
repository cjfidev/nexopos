<?php
namespace Modules\CustomerDebtPayment\Providers;

use App\Classes\Hook;
use App\Providers\AppServiceProvider;

class ServiceProvider extends AppServiceProvider
{
    public function register()
    {

        Hook::addFilter( 'ns-dashboard-menus', function ( $menus ) {
            $menus = array_insert_before( $menus, 'modules', [
                'customers-debt-payments-menus' => [
                    'label' => __( 'Customers Debt Payments' ),
                    'icon' => 'la-money-bill',
                    'href' => ns()->url( '/dashboard/customers-debt-payments' ),
                ]
            ]);

            return $menus;
        });
    }

    public function registerCrud( $identifier )
    {
        switch( $identifier ) {
            case 'procurements-returns.returns': return ServiceCrud::class; 
            default: return $identifier; // required
        }
    }
}