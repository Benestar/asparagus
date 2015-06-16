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
	 * Sets the GROUP BY modifier.
	 *
	 * @param type $variable
	 * @throws InvalidArgumentException
	 */
	public function groupBy( $variable )  {
		// @todo better string validation
		if ( !is_string( $variable ) ) {
			throw new InvalidArgumentException( '$variable has to be a string' );
		}

		$this->modifiers['GROUP BY'] = '?' . $variable;
	}

	/**
	 * Sets the HAVING modifier.
	 *
	 * @param string $expression
	 */
	public function having( $expression ) {
		// @todo better string validation
		if ( !is_string( $expression ) ) {
			throw new InvalidArgumentException( '$expression has to be a string' );
		}

		$this->modifiers['HAVING'] = $expression;
	}

	/**
	 * Sets the ORDER BY modifier.
	 *
	 * @param string $variable
	 * @param string $direction one of ASC or DESC
	 * @throws InvalidArgumentException
	 */
	public function orderBy( $variable, $direction = 'ASC' ) {
		// @todo better string validation
		if ( !is_string( $variable ) ) {
			throw new InvalidArgumentException( '$variable has to be a string' );
		}

		// @todo also allow lower case
		if ( !in_array( $direction, array( 'ASC', 'DESC' ) ) ) {
			throw new InvalidArgumentException( '$direction has to be either ASC or DESC' );
		}

		$this->modifiers['ORDER BY'] = '?' . $variable . ' ' . $direction;
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
	public function getModifiers() {
		$modifiers = $this->modifiers;
		return implode( array_map( function( $key ) use ( $modifiers ) {
			if ( isset( $modifiers[$key] ) ) {
				return ' ' . $key . ' ' . $modifiers[$key];
			}
		}, array( 'GROUP BY', 'HAVING', 'ORDER BY', 'LIMIT', 'OFFSET' ) ) );
	}

}
