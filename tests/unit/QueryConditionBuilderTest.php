<?php

namespace Asparagus\Tests;

use Asparagus\QueryConditionBuilder;

/**
 * @covers Asparagus\QueryConditionBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryConditionBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testWhere() {
		$conditionBuilder = new QueryConditionBuilder();
		$conditionBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals( ' ?a ?b ?c .', $conditionBuilder->getSPARQL() );
	}

	public function testWhere_invalidSubject() {
		$conditionBuilder = new QueryConditionBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$conditionBuilder->where( null, '?b', '?c' );
	}

	public function testWhere_invalidPredicate() {
		$conditionBuilder = new QueryConditionBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$conditionBuilder->where( '?a', null, '?c' );
	}

	public function testWhere_invalidObject() {
		$conditionBuilder = new QueryConditionBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$conditionBuilder->where( '?a', '?b', null );
	}

	public function testPlus_knownSubject() {
		$conditionBuilder = new QueryConditionBuilder();
		$conditionBuilder->where( '?a', '?b', '?c' );
		$conditionBuilder->plus( null, '?x', '?y' );

		$this->assertEquals( ' ?a ?b ?c ; ?x ?y .', $conditionBuilder->getSPARQL() );
	}

	public function testPlus_knownPredicate() {
		$conditionBuilder = new QueryConditionBuilder();
		$conditionBuilder->where( '?a', '?b', '?c' );
		$conditionBuilder->plus( null, null, '?x' );

		$this->assertEquals( ' ?a ?b ?c , ?x .', $conditionBuilder->getSPARQL() );
	}

	public function testPlus_unknownSubject() {
		$conditionBuilder = new QueryConditionBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$conditionBuilder->plus( null, '?x', '?y' );
	}

	public function testPlus_unknownPredicate() {
		$conditionBuilder = new QueryConditionBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$conditionBuilder->plus( '?x', null, '?y' );
	}

	public function testGetSPARQL() {
		$conditionBuilder = new QueryConditionBuilder();
		$conditionBuilder->where( '?a', '?b', '?c' );
		$conditionBuilder->plus( null, '?x', '?y' );
		$conditionBuilder->plus( null, null, '?z' );
		$conditionBuilder->where( '?a', '?b', '?z' );

		$this->assertEquals( ' ?a ?b ?c , ?z ; ?x ?y , ?z .', $conditionBuilder->getSPARQL() );
	}

}