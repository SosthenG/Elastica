<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Composite;
use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query;
use Elastica\Type\Mapping;

class CompositeTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $mapping = new Mapping($index->getType('_doc'), [
            'price' => ['type' => 'integer'],
            'color' => ['type' => 'keyword'],
            'created' => ['type' => 'date'],
        ]);
        $index->getType('_doc')->setMapping($mapping);

        $index->getType('_doc')->addDocuments([
            new Document(1, ['price' => 5, 'color' => 'blue', 'created' => '2019-01-01T00:00:00']),
            new Document(2, ['price' => 8, 'color' => 'blue', 'created' => '2019-01-02T00:00:00']),
            new Document(3, ['price' => 1, 'color' => 'red', 'created' => '2019-01-02T00:00:00']),
            new Document(4, ['price' => 30, 'color' => 'green', 'created' => '2019-01-03T00:00:00']),
            new Document(5, ['price' => 40, 'color' => 'red', 'created' => '2019-01-04T00:00:00']),
            new Document(6, ['price' => 35, 'color' => 'green', 'created' => '2019-01-05T00:00:00']),
            new Document(7, ['price' => 42, 'color' => 'red', 'created' => '2019-01-05T00:00:00']),
            new Document(8, ['price' => 41, 'color' => 'blue', 'created' => '2019-01-05T00:00:00']),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testCompositeAggregation()
    {
        $histogram_price = new Histogram('price', 'price', 10);
        $date_histogram = new DateHistogram('created', 'created', '1d');

        $terms_color = new Terms('color');
        $terms_color->setField('color');

        $composite = new Composite('composite');
        $composite->addSource($terms_color);
        $composite->addSource($histogram_price);
        $composite->addSource($date_histogram);

        $query = new Query();
        $query->addAggregation($composite);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('composite');
        $this->assertCount(8, $results['buckets']);
        // TODO
    }

    /**
     * @group functional
     */
    public function testCompositeSize()
    {

    }

    /**
     * @group unit
     */
    public function testAddUnsupportedSource()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $composite = new Composite('composite');
        $composite->addSource('avg', new Avg('avg'));
    }

    /**
     * @group unit
     */
    public function testToArrayEmptySources()
    {
        $this->expectException(InvalidException::class);

        $composite = new Composite('composite');
        $composite->toArray();
    }
}
