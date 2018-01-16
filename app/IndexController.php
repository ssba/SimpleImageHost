<?php

namespace App;

use Core\LayoutProcessor;

class IndexController
{
     function __construct()
    {
    }

    public function index()
    {

        if (! isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
        }

        return LayoutProcessor::getTPL('MainTPL','index', ['csrf_token'=>$_SESSION['csrf_token']]);
    }
}