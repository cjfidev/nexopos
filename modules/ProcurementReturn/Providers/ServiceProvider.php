<?php
namespace Modules\ProcurementReturn\Providers;

use App\Classes\Hook;
use App\Providers\AppServiceProvider;

class ServiceProvider extends AppServiceProvider
{
    public function register()
    {

        Hook::addFilter( 'ns-dashboard-menus', function ( $menus ) {
            $menus = array_insert_before( $menus, 'modules', [
                'procurements-returns-menus' => [
                    'label' => __( 'Procurements Returns' ),
                    'icon' => 'la-undo',
                    'href' => ns()->url( '/dashboard/procurements-returns' ),
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