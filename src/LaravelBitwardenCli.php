<?php

namespace Aleex1848\LaravelBitwardenCli;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use stdClass;
/*
** Bitwarden API https://bitwarden.com/help/vault-management-api/
*/
class LaravelBitwardenCli
{
    public $url;

    public function __construct()
    {
        $this->url = config('bitwarden-cli.url');
    }

    public function getStatus() : stdClass|null
    {
        $result = Http::get($this->url.'status');
        if($result->ok())
        {
            return json_decode($result->body());
        } else return null;
    }

    public function isLocked() : bool
    {
        return $this->getStatus()->data->template->status === 'locked';
    }

    public function lock() : bool
    {
        $result = Http::post($this->url.'lock');
        return $result->ok();
    }

    public function unlock() : bool
    {
        $result = Http::post($this->url.'unlock', [
            'password' => config('bitwarden-cli.password')
        ]);
        return $result->ok();

    }

    public function request($path, $verb) : Response|null
    {
        if($this->isLocked()) {
            $this->unlock();
        }

        $result = Http::$verb($this->url.$path);
        
        if($result->ok())
        {
            $this->lock();
            return $result;
        }
        else return null;
    }

    

    public function getLogin($identifier) : Collection|null
    {
        return match(config('bitwarden-cli.default_identifier')) {
            'name' => $this->getLoginByName($identifier),
            'id' => $this->getLoginById($identifier)
        };        
    }

    public function getLoginById($id) : Collection|null
    {
        $result = $this->request('object/item/'.$id, 'get');        
        if($result)
        {
            $data = json_decode($result->body());
            if($data->success)
            {
                return collect($data->data->login);
            } else return null;
            
        } else return null;
    }

    public function getLoginByName($name) : Collection|null
    {
        $item = $this->listItems()->where('name',$name)->first();
        if($item) {
            //dd($item);
            return collect($item->login);
        } else return null;
        
    }

    public function listItems() : Collection|null
    {
        $result = $this->request('list/object/items', 'get');        
        if($result)
        {
            $data = json_decode($result->body());
            if($data->success)
            {
                return collect($data->data->data);
            } else return null;
            
        } else return null;
    }
}
