<?php

namespace Didbot\DidbotApi;

use League\Fractal\Pagination\CursorInterface;

class CustomCursor implements CursorInterface
{
    /**
     * Current cursor value.
     * @var mixed
     */
    protected $current;

    /**
     * Previous cursor value.
     * @var mixed
     */
    protected $prev;

    /**
     * Next cursor value.
     * @var mixed
     */
    protected $next;

    /**
     * Items being held for the current cursor position.
     * @var int
     */
    protected $count;


    public function __construct($current, $prev, $object, $limit)
    {
        $this->current = ($current) ? $current : null;
        $this->prev    = ($prev) ? $prev : null;
        $this->count   = count($object);
        $this->setNext($object, $limit);
    }

    /**
     * Get the current cursor value.
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Get the prev cursor value.
     * @return mixed
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * Get the next cursor value.
     * @return mixed
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Returns the total items in the current cursor.
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    protected function setNext($object, $limit)
    {
        if($limit > $this->count){
            $this->next = null;
            return;
        }

        $this->next = ($this->count) ? $object->last()->id : null;
    }
}
