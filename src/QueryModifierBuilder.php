<?php

namespace Asparagus;

use InvalidArgumentException;

/**
 * Package-private class to build the modifiers of a SPARQL query.
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryModifierBuilder {

	/**
	 * @var string[] list of modifiers including limit, offset and order by
	 */
	private $modifiers = array();

	/**
	 * @var ExpressionValidator
	 */
	private $expressionValidator;

	/**
	 * @var UsageValidator
	 */
	private $usageValidator;

	public function __construct( UsageValidator $usageValidator ) {
		$this->expressionValidator = new ExpressionValidator();
		$this->usageValidator = $usageValidator;
	}

	/**
	 * Sets the GROUP BY modifiers.
	 *
	 * @param string[] $expressions
	 */
	public function groupBy( array $expressions )  {
		foreach ( $expressions as $expression ) {
			$this->expressionValidator->validate( $expression,
				ExpressionValidator::VALIDATE_VARIABLE | ExpressionValidator::VALIDATE_FUNCTION_AS
			);
		}

		$expression = implode( ' ', $expressions );
		$this->usageValidator->trackUsedPrefixes( $expression );
		$this->usageValidator->trackUsedVariables( $expression );
		$this->modifiers['GROUP BY'] = $expression;
	}

	/**
	 * Sets the HAVING modifier.
	 *
	 * @param string $expression
	 */
	public function having( $expression ) {
		$this->expressionValidator->validate( $expression, ExpressionValidator::VALIDATE_FUNCTION );

		$this->usageValidator->trackUsedPrefixes( $expression );
		$this->usageValidator->trackUsedVariables( $expression );
		$this->modifiers['HAVING'] = '(' . $expression . ')';
	}

	/**
	 * Sets the ORDER BY modifier.
	 *
	 * @param string $expression
	 * @param string $direction one of ASC or DESC
	 * @throws InvalidArgumentException
	 */
	public function orderBy( $expression, $direction = 'ASC' ) {
		$direction = strtoupper( $direction );
		if ( !in_array( $direction, array( 'ASC', 'DESC' ) ) ) {
			throw new InvalidArgumentException( '$direction has to be either ASC or DESC' );
		}

		$this->expressionValidator->validate( $expression,
			ExpressionValidator::VALIDATE_VARIABLE | ExpressionValidator::VALIDATE_FUNCTION
		);

		$this->usageValidator->trackUsedPrefixes( $expression );
		$this->usageValidator->trackUsedVariables( $expression );
		$this->modifiers['ORDER BY'] = $direction . ' (' . $expression . ')';
	}

	/**
	 * Sets the LIMIT modifier.
	 *
	 * @param int $limit
	 * @throws InvalidArgumentException
	 */
	public function limit( $limit ) {
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit has to be an integer' );
		}

		$this->modifiers['LIMIT'] = $limit;
	}

	/**
	 * Sets the OFFSET modifier.
	 *
	 * @param int $offset
	 * @throws InvalidArgumentException
	 */
	public function offset( $offset ) {
		if ( !is_int( $offset ) ) {
			throw new InvalidArgumentException( '$offset has to be an integer' );
		}

		$this->modifiers['OFFSET'] = $offset;
	}

	/**
	 * Returns the plain SPARQL string of these modifiers.
	 *
	 * @return string
	 */
	public function getSPARQL() {
		$modifiers = $this->modifiers;
		return implode( array_map( function( $key ) use ( $modifiers ) {
			if ( isset( $modifiers[$key] ) ) {
				return ' ' . $key . ' ' . $modifiers[$key];
			}
		}, array( 'GROUP BY', 'HAVING', 'ORDER BY', 'LIMIT', 'OFFSET' ) ) );
	}

}
