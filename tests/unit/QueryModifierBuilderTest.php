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

	public function testGroupByModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->groupBy( null );
	}

	public function testHavingModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->having( 'test' );

		$this->assertEquals( ' HAVING test', $queryBuilder->getSPARQL() );
	}

	public function testHavingModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->having( null );
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

	public function testOrderByModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->orderBy( null );
	}

	public function testOrderByModifier_invalidDirection() {
		$queryBuilder = new QueryModifierBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->orderBy( 'test', 'FOO' );
	}

	public function testLimitModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->limit( 42 );

		$this->assertEquals( ' LIMIT 42', $queryBuilder->getSPARQL() );
	}

	public function testLimitModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->limit( null );
	}

	public function testOffsetModifier() {
		$queryBuilder = new QueryModifierBuilder();
		$queryBuilder->offset( 42 );

		$this->assertEquals( ' OFFSET 42', $queryBuilder->getSPARQL() );
	}

	public function testOffsetModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->offset( null );
	}

}
