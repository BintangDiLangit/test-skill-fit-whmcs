<?php

namespace App\Http\Controllers;

use DarthSoup\Whmcs\Facades\Whmcs;
use DarthSoup\Whmcs\WhmcsManager;
use Illuminate\Http\Request;

class WHMCSController extends Controller
{
    private WhmcsManager $whmcsManager;

    public function __construct(WhmcsManager $whmcsManager)
    {
        $this->whmcsManager = $whmcsManager;
    }

    public function index()
    {
        $result = Whmcs::Users()->getUsers(['search' => 'foo@bar.org']);

        // $result = $this->whmcsManager->client()->getClients();
        dd($result);
    }
}