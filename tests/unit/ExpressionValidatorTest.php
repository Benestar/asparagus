<?php

namespace Asparagus\Tests;

use Asparagus\ExpressionValidator;

/**
 * @covers Asparagus\ExpressionValidator
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ExpressionValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidExpressions
	 */
	public function testValidate_validExpressions( $expression, $options, array $variables, array $prefixes ) {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( $expression, $options );

		$this->assertEquals( $variables, $expressionValidator->getVariables() );
		$this->assertEquals( $prefixes, $expressionValidator->getPrefixes() );
	}

	public function provideValidExpressions() {
		return array(
			array( '?a', ExpressionValidator::VALIDATE_VARIABLE, array( 'a' ), array() ),
			array( '$b', ExpressionValidator::VALIDATE_VARIABLE, array( 'b' ), array() ),
			array( '<http://www.example.com/test#>', ExpressionValidator::VALIDATE_IRI, array(), array() ),
			array( 'test:FooBar', ExpressionValidator::VALIDATE_PREFIXED_IRI, array(), array( 'test' ) ),
			array( 'abc', ExpressionValidator::VALIDATE_PREFIX, array(), array( 'abc' ) ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION, array( 'x' ), array( 'xsd' ) ),
			array( '?abc', ExpressionValidator::VALIDATE_FUNCTION, array( 'abc' ), array() ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION, array( 'x', 'y', 'z' ), array() ),
			array( 'COUNT (?x) AS ?count', ExpressionValidator::VALIDATE_FUNCTION_AS, array( 'x' ), array() ),
		);
	}

	/**
	 * @dataProvider provideInvalidExpressions
	 */
	public function testValidate_invalidExpressions( $expression, $options ) {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'UnexpectedValueException' );

		$expressionValidator->validateExpression( $expression, $options );
	}

	public function provideInvalidExpressions() {
		return array(
			array( 'nyan', ExpressionValidator::VALIDATE_VARIABLE ),
			array( 'http://www.example.com/test#', ExpressionValidator::VALIDATE_IRI ),
			array( '<http://www.example.com/test#', ExpressionValidator::VALIDATE_IRI ),
			array( '<abc><>', ExpressionValidator::VALIDATE_IRI ),
			array( 'foobar', ExpressionValidator::VALIDATE_PREFIXED_IRI ),
			array( 'test:Foo:Bar', ExpressionValidator::VALIDATE_PREFIXED_IRI ),
			array( 'ab:cd', ExpressionValidator::VALIDATE_PREFIX ),
			array( 'ab/cd', ExpressionValidator::VALIDATE_PREFIX ),
			array( 'ab cd', ExpressionValidator::VALIDATE_PREFIX ),
			array( 'foobar (?x > ?y)', ExpressionValidator::VALIDATE_FUNCTION ),
			array( '(RAND ())', ExpressionValidator::VALIDATE_FUNCTION ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION_AS ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION_AS ),
			array( ' AS ?abc', ExpressionValidator::VALIDATE_FUNCTION_AS ),
			array( '', ExpressionValidator::VALIDATE_ALL ),
			array( '     ', ExpressionValidator::VALIDATE_ALL ),
		);
	}

	public function testValidate_invalidArgument() {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'InvalidArgumentException' );

		$expressionValidator->validateExpression( null );
	}

}
