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

	public function __construct() {
		$this->expressionValidator = new ExpressionValidator();
	}

	/**
	 * Sets the GROUP BY modifier.
	 *
	 * @param string $expression
	 */
	public function groupBy( $expression )  {
		$this->expressionValidator->validateExpression( $expression, ExpressionValidator::VALIDATE_VARIABLE );
		$this->modifiers['GROUP BY'] = $expression;
	}

	/**
	 * Sets the HAVING modifier.
	 *
	 * @param string $expression
	 */
	public function having( $expression ) {
		// @todo this isn't right :S
		$this->expressionValidator->validateExpression( $expression, ExpressionValidator::VALIDATE_VARIABLE );
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
		$this->expressionValidator->validateExpression( $expression, ExpressionValidator::VALIDATE_VARIABLE );
		$direction = strtoupper( $direction );

		if ( !in_array( $direction, array( 'ASC', 'DESC' ) ) ) {
			throw new InvalidArgumentException( '$direction has to be either ASC or DESC' );
		}

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

	/**
	 * @return string[]
	 */
	public function getPrefixes() {
		return $this->expressionValidator->getPrefixes();
	}

	/**
	 * @return string[]
	 */
	public function getVariables() {
		return $this->expressionValidator->getVariables();
	}

}
