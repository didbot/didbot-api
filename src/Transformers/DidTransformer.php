<?php
namespace Didbot\DidbotApi\Transformers;

use \Didbot\DidbotApi\Models\Did;
use League\Fractal\TransformerAbstract;

class DidTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'tags', 'client'
    ];

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Did $did)
    {
        return [
            'id'    => (int)$did->id,
            'text'  => $did->text,
            'created_at' => $did->created_at->toDateTimeString()
        ];
    }

    /**
     * Include Tags

     * @return \League\Fractal\Resource\Collection
     */
    public function includeTags(Did $did)
    {
        return $this->collection($did->tags, new TagTransformer);
    }

    /**
     * Include Client

     * @return \League\Fractal\Resource\Item
     */
    public function includeClient(Did $did)
    {
        return $this->item($did->client, new ClientTransformer);
    }
}