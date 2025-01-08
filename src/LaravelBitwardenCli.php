<?php

namespace Aleex1848\LaravelBitwardenCli;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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
        } else $result->throw();
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



    public function request($path, $verb, $body = null) : Response|null
    {
        if($this->isLocked()) {
            $this->unlock();
        }

        $result = Http::$verb($this->url.$path,$body);        

        if($result->ok())
        {
            if(config('bitwarden-cli.lock_after_request')) $this->lock();
            return $result;
        }
        else $result->throw();
    }

    public function sync()
    {
        if(config('bitwarden-cli.cache.enabled')) Artisan::call('cache:clear');
        return $this->request('sync','post');
    }

    public function makeUrisItem(array $uris)
    {
        $result = [];
        foreach($uris as $uri)
        {
            $result[] = [
                'match' => 0,
                'uri' => $uri
            ];
        }
        return $result;
    }
    public function makeLoginItem(string $username, string $password, ?array $uris = null)
    {
        $uris ?: $uris = [];
        return [
            'uris' => $uris,
            'username' => $username,
            'password' => $password,
            'totp' => null
        ];
    }
    public function uploadLoginItem(
        string $organizationId,
        array $collectionIds,
        array $login,
        string $name,
        ?array $fields = null,
        ?int $folderId = null
    )
    {
        $item = [
            'organizationId' => $organizationId,
            'collectionIds' => $collectionIds,
            'folderId' => $folderId,
            'type' => 1,
            'name' => $name,
            'notes' => null,
            'favorite' => false,
            'login' => $login,
            'reprompt' => 0
        ];
        if($fields) $item['fields'] = $fields;
        else $item['fields'] = [];

        $this->sync();
        return $this->request('object/item','post',$item);
    }

    public function deleteItem($id)
    {
        $result = $this->request('object/item/'.$id, 'delete');
        return $result;
        if($result)
        {
            $data = json_decode($result->body());
            if($data->success)
            {
                $this->sync();
                return collect($data->data);
            } else return null;

        } else $result->throw();
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

        } else $result->throw();
    }

    public function getItemByName($name) : Collection|null
    {
        $item = $this->listItems()->where('name',$name)->first();
        if($item) {
            //dd($item);
            return collect($item);
        } else return null;

    }

    public function listItems($collectionId = null) : Collection|null
    {        
        $endpoint = $collectionId ? 'list/object/items?collectionId='.$collectionId : 'list/object/items';
        $cacheName = $collectionId ? 'bitwarden-cli-listItems-'.$collectionId : 'bitwarden-cli-listItems';
        if(config('bitwarden-cli.cache.enabled'))
        {
            return Cache::flexible(
                $cacheName,
                [config('bitwarden-cli.cache.ttl_seconds'),config('bitwarden-cli.cache.ttl_seconds')+10],
                function() use ($endpoint){
                        $result = $this->request($endpoint, 'get');
                        $data = json_decode($result->body());
                        if($data->success)
                        {
                            return collect($data->data->data);
                        } else return null;
                }
            );
        } else {
            $result = $this->request($endpoint, 'get');
            $data = json_decode($result->body());
            if($data->success)
            {
                return collect($data->data->data);
            } else return null;
        }

//        $result = $this->request('list/object/items', 'get');
        // if($result)
        // {
        //     $data = json_decode($result->body());
        //     if($data->success)
        //     {
        //         return collect($data->data->data);
        //     } else return null;

        // } else $result->throw();
    }
}
