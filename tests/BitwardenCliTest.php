<?php

use Aleex1848\LaravelBitwardenCli\Facades\LaravelBitwardenCli as Bitwarden;

it('can list the items', function () {
   $response = Bitwarden::listItems();
   expect($response)->toBeArray();
});
 
// Instead of 'it', you can also use 'test'