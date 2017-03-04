<?php
namespace Didbot\DidbotApi\Transformers;

use Didbot\DidbotApi\Models\Source;
use League\Fractal\TransformerAbstract;

class SourceTransformer extends TransformerAbstract
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
     * @param Source $source
     * @return array
     */
    public function transform(Source $source)
    {
        return [
                'id' => $source->id,
                'name' => $source->name,
        ];
    }

    /**
     * Include Dids
     * @return \League\Fractal\Resource\Collection
     */
    public function includeDids(Source $source)
    {
        $dids = $source->dids;

        return $this->collection($dids, new DidTransformer);
    }
}