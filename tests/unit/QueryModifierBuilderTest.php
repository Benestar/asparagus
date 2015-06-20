<?php

namespace Asparagus\Tests;

use Asparagus\QueryModifierBuilder;
use Asparagus\UsageValidator;

/**
 * @covers Asparagus\QueryModifierBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryModifierBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testGroupByModifier() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$queryBuilder->groupBy( array( '?test' ) );

		$this->assertEquals( ' GROUP BY ?test', $queryBuilder->getSPARQL() );
	}

	public function testGroupByModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->groupBy( array( null ) );
	}

	public function testHavingModifier() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$queryBuilder->having( '?test' );

		$this->assertEquals( ' HAVING (?test)', $queryBuilder->getSPARQL() );
	}

	public function testHavingModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->having( null );
	}

	public function testOrderByModifier() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$queryBuilder->orderBy( '?test' );

		$this->assertEquals( ' ORDER BY ASC (?test)', $queryBuilder->getSPARQL() );
	}

	public function testOrderByDescModifier() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$queryBuilder->orderBy( '?test', 'DESC' );

		$this->assertEquals( ' ORDER BY DESC (?test)', $queryBuilder->getSPARQL() );
	}

	public function testOrderByModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->orderBy( null );
	}

	public function testOrderByModifier_invalidDirection() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->orderBy( '?test', 'FOO' );
	}

	public function testLimitModifier() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$queryBuilder->limit( 42 );

		$this->assertEquals( ' LIMIT 42', $queryBuilder->getSPARQL() );
	}

	public function testLimitModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->limit( null );
	}

	public function testOffsetModifier() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$queryBuilder->offset( 42 );

		$this->assertEquals( ' OFFSET 42', $queryBuilder->getSPARQL() );
	}

	public function testOffsetModifier_invalidArgument() {
		$queryBuilder = new QueryModifierBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->offset( null );
	}

}
