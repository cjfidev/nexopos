<?php
namespace Modules\ServiceManagement\Providers;

use App\Classes\Hook;
use App\Providers\AppServiceProvider;

class ServiceProvider extends AppServiceProvider
{
    public function register()
    {

        Hook::addFilter( 'ns-dashboard-menus', function ( $menus ) {
            $menus = array_insert_before( $menus, 'modules', [
                'service-menus' => [
                    'label' => __( 'Service Management' ),
                    'href' => ns()->url( '/dashboard/service-managements' ),
                ]
            ]);

            return $menus;
        });
    }

    public function registerCrud( $identifier )
    {
        switch( $identifier ) {
            case 'service-management.services': return ServiceCrud::class; 
            default: return $identifier; // required
        }
    }
}