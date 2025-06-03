<?php
namespace Modules\PiutangReport\Providers;

use App\Classes\Hook;
use App\Providers\AppServiceProvider;

class ServiceProvider extends AppServiceProvider
{
    public function register()
    {

        Hook::addFilter( 'ns-dashboard-menus', function ( $menus ) {
            $menus = array_insert_before( $menus, 'modules', [
                'piutang-report-menus' => [
                    'label' => __( 'Debt Report' ),
                    'icon' => 'la-money-bill',
                    'href' => ns()->url( '/dashboard/piutang-report' ),
                ]
            ]);

            return $menus;
        });
    }
}