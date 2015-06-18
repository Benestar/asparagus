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

	public function testValidateVariable() {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( '?a', ExpressionValidator::VALIDATE_VARIABLE );

		$this->assertEquals( array( 'a' ), $expressionValidator->getVariables() );
		$this->assertEquals( array(), $expressionValidator->getPrefixes() );
	}

	public function testValidateIRI() {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( '<http://www.example.com/test#>', ExpressionValidator::VALIDATE_IRI );

		$this->assertEquals( array(), $expressionValidator->getVariables() );
		$this->assertEquals( array(), $expressionValidator->getPrefixes() );
	}

	public function testValidatePrefixedIRI() {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( 'test:FooBar', ExpressionValidator::VALIDATE_PREFIXED_IRI );

		$this->assertEquals( array(), $expressionValidator->getVariables() );
		$this->assertEquals( array( 'test' ), $expressionValidator->getPrefixes() );
	}

	public function testValidatePrefix() {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( 'abc', ExpressionValidator::VALIDATE_PREFIX );

		$this->assertEquals( array(), $expressionValidator->getVariables() );
		$this->assertEquals( array( 'abc' ), $expressionValidator->getPrefixes() );
	}

	public function testValidateFunction() {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION );

		$this->assertEquals( array( 'x' ), $expressionValidator->getVariables() );
		$this->assertEquals( array( 'xsd' ), $expressionValidator->getPrefixes() );
	}

	public function testValidateFunctionAs() {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validateExpression( 'COUNT (?x) AS ?count', ExpressionValidator::VALIDATE_FUNCTION );

		$this->assertEquals( array( 'x' ), $expressionValidator->getVariables() );
		$this->assertEquals( array(), $expressionValidator->getPrefixes() );
	}

	public function testValidate_invalidArgument() {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'InvalidArgumentException' );

		$expressionValidator->validateExpression( null );
	}

}
