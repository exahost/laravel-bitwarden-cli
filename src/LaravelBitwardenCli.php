<?php

namespace Aleex1848\LaravelBitwardenCli;

use Exception;
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

    public function sync()
    {
        return $this->request('sync','post');
    }

    public function getValue($item,$fieldd)
    {
        $field = str($fieldd);
        if($field->contains('.'))
        {
            $sub = $field->before('.')->value;
            $key = $field->after('.')->value;
            if(is_array($item[$sub]))
            {
                return collect($item[$sub])->where('name',$key)->first()->value;
            }
            else
            {
                return $item[$sub]->$key;
            }
        }
        else {
#            dd($item);
            return $item[$field->value];
        }
    }

    public function getValues($identifier, array $fields)
    {
        $result = [];
        $item = $this->getItem($identifier);
        foreach($fields as $field)
        {

           array_push($result, [
            $field => $this->getValue($item, $field)
           ]);
        }

        return $result;
    }

    public function getItem($identifier) : Collection|null
    {
        return match(config('bitwarden-cli.default_identifier')) {
            'name' => $this->getItemByName($identifier),
            'id' => $this->getItemById($identifier),
            default => throw new Exception('unknown identifier')
        };
    }

    public function getItemById($id) : Collection|null
    {
        $result = $this->request('object/item/'.$id, 'get');
        if($result)
        {
            $data = json_decode($result->body());
            if($data->success)
            {
                return collect($data->data);
            } else return null;

        } else return null;
    }

    public function getItemByName($name) : Collection|null
    {
        $item = $this->listItems()->where('name',$name)->first();
        if($item) {
            //dd($item);
            return collect($item);
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
