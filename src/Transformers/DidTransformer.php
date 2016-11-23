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
        'tags'
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
        ];
    }

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeTags(Did $did)
    {
        $tags = $did->tags;

        return $this->collection($tags, new TagTransformer);
    }
}