<?php

namespace Asparagus\Tests;

use Asparagus\QueryModifierBuilder;

/**
 * @covers Asparagus\QueryModifierBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryModifierBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testGroupByModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->groupBy( 'test' );

		$this->assertEquals( ' GROUP BY ?test', $queryBuilder->getSPARQL() );
	}

	public function testHavingModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->having( 'test' );

		$this->assertEquals( ' HAVING test', $queryBuilder->getSPARQL() );
	}

	public function testOrderByModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->orderBy( 'test' );

		$this->assertEquals( ' ORDER BY ?test ASC', $queryBuilder->getSPARQL() );
	}

	public function testOrderByDescModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->orderBy( 'test', 'DESC' );

		$this->assertEquals( ' ORDER BY ?test DESC', $queryBuilder->getSPARQL() );
	}

	public function testLimitModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->limit( 42 );

		$this->assertEquals( ' LIMIT 42', $queryBuilder->getSPARQL() );
	}

	public function testOffsetModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->offset( 42 );

		$this->assertEquals( ' OFFSET 42', $queryBuilder->getSPARQL() );
	}

}
