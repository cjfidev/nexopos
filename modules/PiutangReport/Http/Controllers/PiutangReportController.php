<?php

/**
 * PiutangReport Controller
 * @since 1.0
 * @package modules/PiutangReport
**/

namespace Modules\PiutangReport\Http\Controllers;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

class PiutangReportController extends Controller
{
    /**
     * Main Page
     * @since 1.0
    **/
    public function index()
    {
        return view( 'PiutangReport::index' );
    }
}
