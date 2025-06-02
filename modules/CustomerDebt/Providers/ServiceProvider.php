<?php
namespace Modules\CustomerDebt\Providers;

use App\Classes\Hook;
use App\Providers\AppServiceProvider;

class ServiceProvider extends AppServiceProvider
{
    // public function register()
    // {

    //     Hook::addFilter( 'ns-dashboard-menus', function ( $menus ) {
    //         $menus = array_insert_before( $menus, 'modules', [
    //             'customers-debts-menus' => [
    //                 'label' => __( 'Customers Debts' ),
    //                 'icon' => 'la-credit-card',
    //                 'href' => ns()->url( '/dashboard/customers-debts' ),
    //             ]
    //         ]);

    //         return $menus;
    //     });
    // }

    public function registerCrud( $identifier )
    {
        switch( $identifier ) {
            case 'procurements-returns.returns': return ServiceCrud::class; 
            default: return $identifier; // required
        }
    }
}