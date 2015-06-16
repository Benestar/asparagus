<?php

namespace Asparagus\Tests;

use Asparagus\QueryBuilder;

/**
 * @covers Asparagus\QueryBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$queryBuilder = new QueryBuilder();

		$this->assertInstanceOf( 'Asparagus\QueryBuilder', $queryBuilder );
	}

	public function testEmptyQuery() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( 'SELECT * WHERE {}', $queryBuilder->getSPARQL() );
	}

	public function testGroupByModifier() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->groupBy( 'test' );

		$this->assertContains( 'GROUP BY ?test', $queryBuilder->getSPARQL() );
	}

	public function testHavingModifier() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->having( 'test' );

		$this->assertContains( 'test', $queryBuilder->getSPARQL() );
	}

	public function testOrderByModifier() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->orderBy( 'test' );

		$this->assertContains( 'ORDER BY ?test ASC', $queryBuilder->getSPARQL() );
	}

	public function testOrderByDescModifier() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->orderBy( 'test', 'DESC' );

		$this->assertContains( 'ORDER BY ?test DESC', $queryBuilder->getSPARQL() );
	}

	public function testLimitModifier() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->limit( 42 );

		$this->assertContains( 'LIMIT 42', $queryBuilder->getSPARQL() );
	}

	public function testOffsetModifier() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->offset( 42 );

		$this->assertContains( 'OFFSET 42', $queryBuilder->getSPARQL() );
	}

}
