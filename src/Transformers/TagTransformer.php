<?php
namespace Didbot\DidbotApi\Transformers;

use \Didbot\DidbotApi\Models\Did;
use \Didbot\DidbotApi\Models\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
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
     * @return array
     */
    public function transform(Tag $tag)
    {
        return [
                'id' => $tag->id,
                'text' => $tag->text
        ];
    }

    /**
     * Include Author
     * @return \League\Fractal\ItemResource
     */
    public function includeDids(Did $did)
    {
        $tags = $did->tags;

        return $this->item($tags, new TagTransformer);
    }
}