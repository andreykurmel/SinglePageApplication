<?php

namespace Vanguard\Http\Controllers\Web\Tablda\Applications;


use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallInput;
use Vanguard\Http\Controllers\Web\Tablda\Applications\Transfers\DirectCallOut;
use Vanguard\Models\Correspondences\CorrespApp;

interface AppControllerInterface
{
    /**
     * @param Request $request
     * @param CorrespApp $correspApp
     */
    public function get(Request $request, CorrespApp $correspApp);

    /**
     * @param Request $request
     */
    public function post(Request $request);

    /**
     * @param DirectCallInput $input
     * @return DirectCallOut
     */
    public function direct_call(DirectCallInput $input);

}
