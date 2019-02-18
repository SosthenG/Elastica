<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class Composite.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-composite-aggregation.html
 */
class Composite extends AbstractAggregation
{
    /**
     * @var array Aggregations used as sources for the composite
     */
    protected $_sources = [];

    /**
     * Sets the amount of composite buckets to be returned.
     *
     * @param int $size The amount of composite buckets to be returned.
     *
     * @return $this
     */
    public function setSize($size)
    {
        return $this->setParam('size', $size);
    }

    /**
     * Sets the key after which the buckets should be returned (after_key of a previous composite result)
     *
     * @param array $after
     *
     * @return Composite
     */
    public function setAfter($after)
    {
        return $this->setParam('after', $after);
    }

    /**
     * Adds a source
     *
     * @param AbstractAggregation $aggregation The aggregation to add as source
     *
     * @return $this
     */
    public function addSource($aggregation)
    {
        if (!($aggregation instanceof Histogram) && !($aggregation instanceof Terms)) {
            throw new \InvalidArgumentException('The composite aggregations only supports histograms, date-histograms and terms aggregations as sources');
        }
        $this->_sources[] = $aggregation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (count($this->_sources) === 0) {
            throw new InvalidException(
                'At least one source should be set'
            );
        }
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        // We need the sources field to be an array of objects
        $array[$baseName]['sources'] = array_map(function($aggregation) { return [$aggregation->getName() => $aggregation->toArray()]; }, $this->_sources);

        return $array;
    }
}
