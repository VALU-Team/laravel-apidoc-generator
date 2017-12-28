<?php

namespace Mpociot\ApiDoc\Postman;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;

class CollectionWriter
{
    /**
     * @var Collection
     */
    private $routeGroups;

    /**
     * CollectionWriter constructor.
     *
     * @param Collection $routeGroups
     */
    public function __construct(Collection $routeGroups)
    {
        $this->routeGroups = $routeGroups;
    }

    public function getCollection()
    {
        $collection = [
            'variables' => [],
            'info' => [
                'name' => '',
                '_postman_id' => Uuid::uuid4()->toString(),
                'description' => '',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.0.0/collection.json',
            ],
            'item' => $this->routeGroups->map(function ($routes, $groupName) {
                $title = explode(PHP_EOL, $groupName)[0];
                return [
                    'name' => $title,
                    'description' => '',
                    'item' => $routes->map(function ($route) {
                        return [
                            'name' => $route['resource'] != '' ? $route['resource'] : 
                                $route['title'] != '' ? $route['title'] : url($route['uri']),
                            'request' => [
                                'url' => secure_url($route['uri']),
                                'method' => $route['methods'][0],
                                'body' => [
                                    'mode' => 'formdata',
                                    'formdata' => collect($route['parameters'])->map(function ($parameter, $key) {
                                        return [
                                            'key' => $key,
                                            'value' => isset($parameter['value']) ? $parameter['value'] : '',
                                            'type' => 'text',
                                            'enabled' => true,
                                        ];
                                    })->values()->toArray(),
                                ],
                                'description' => $route['description'],
                                'response' => [],
                                'header' => collect($route['headers'])->map(function ($header) {
                                    $header_key2value = explode(":", $header);
                                    return [
                                        'key' => $header_key2value[0],
                                        'value' => $header_key2value[1],
                                    ];
                                })->values()->toArray(),
                            ],
                        ];
                    })->toArray(),
                ];
            })->values()->toArray(),
        ];

        return json_encode($collection);
    }
}
