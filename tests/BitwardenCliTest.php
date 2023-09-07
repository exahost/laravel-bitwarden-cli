<?php

use Aleex1848\LaravelBitwardenCli\Facades\LaravelBitwardenCli as Bitwarden;

beforeAll(function () {
   config([
           'bitwarden-cli.default_identifier' => 'name',
           'bitwarden-cli.password' => 'seret',
           'bitwarden-cli.url' => 'http://bitwarden-cli:8087/'
   ]);
});

it('can list the items', function () {   
   $response = Bitwarden::listItems();
   expect(count($response))->toBeGreaterThan(0);
});
 
// Instead of 'it', you can also use 'test'