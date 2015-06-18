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
		$expressionValidator->validate( $expression, $options );

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
	public function testValidate_invalidExpressions( $expression, $options, $errorMessage ) {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'UnexpectedValueException', $errorMessage );

		$expressionValidator->validate( $expression, $options );
	}

	public function provideInvalidExpressions() {
		return array(
			array( 'nyan', ExpressionValidator::VALIDATE_VARIABLE, 'variable' ),
			array( 'http://www.example.com/test#', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( '<http://www.example.com/test#', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( '<abc><>', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( 'foobar', ExpressionValidator::VALIDATE_PREFIXED_IRI, 'prefixed IRI' ),
			array( 'test:Foo:Bar', ExpressionValidator::VALIDATE_PREFIXED_IRI, 'prefixed IRI' ),
			array( 'ab:cd', ExpressionValidator::VALIDATE_PREFIX, 'prefix' ),
			array( 'ab/cd', ExpressionValidator::VALIDATE_PREFIX, 'prefix' ),
			array( 'ab cd', ExpressionValidator::VALIDATE_PREFIX, 'prefix' ),
			array( 'foobar (?x > ?y)', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( '(RAND ())', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( ' AS ?abc', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( '', ExpressionValidator::VALIDATE_ALL, 'variable' ),
			array( '     ', ExpressionValidator::VALIDATE_ALL, 'variable' ),
		);
	}

	public function testValidate_invalidArgument() {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'InvalidArgumentException' );

		$expressionValidator->validate( null );
	}

}
