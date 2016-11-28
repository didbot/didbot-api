<?php
namespace Didbot\DidbotApi\Transformers;

use \Laravel\Passport\Client;
use League\Fractal\TransformerAbstract;

class ClientTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     * @var array
     */
    protected $availableIncludes = [
            'did'
    ];

    /**
     * Turn this item object into a generic array
     *
     * @param Client $client
     * @return array
     */
    public function transform(Client $client)
    {
        return [
                'id' => (int)$client->id,
                'name' => $client->name,
        ];
    }

    /**
     * Include Dids
     * @return \League\Fractal\Resource\Collection
     */
    public function includeDids(Client $client)
    {
        $dids = $client->dids;

        return $this->collection($dids, new TagTransformer);
    }
}