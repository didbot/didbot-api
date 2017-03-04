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
        'tags', 'source'
    ];

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Did $did)
    {
        return [
            'id'    => $did->id,
            'text'  => $did->text,
            'created_at' => $did->created_at->toIso8601String()
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
     * Include Source

     * @return \League\Fractal\Resource\Item
     */
    public function includeSource(Did $did)
    {
        return $this->item($did->source, new SourceTransformer);
    }
}