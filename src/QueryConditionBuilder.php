<?php

namespace Asparagus;

use InvalidArgumentException;

/**
 * Package-private class to build the conditions of a SPARQL query.
 *
 * @todo support filter, filterExists, filterNotExists, optional, union, minus
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryConditionBuilder {

	/**
	 * @var string[] list of conditions
	 */
	private $conditions = array();

	/**
	 * @var string
	 */
	private $currentSubject = null;

	/**
	 * @var string
	 */
	private $currentPredicate = null;

	/**
	 * Adds the given triple as a condition.
	 *
	 * @param string $subject
	 * @param string $predicate
	 * @param string $object
	 * @throws InvalidArgumentException
	 */
	public function where( $subject, $predicate, $object ) {
		// @todo better string validation
		if ( !is_string( $subject ) || !is_string( $predicate ) || !is_string( $object ) ) {
			throw new InvalidArgumentException( '$subject, $predicate and $object have to be strings' );
		}

		$this->currentSubject = $subject;
		$this->currentPredicate = $predicate;
		$this->conditions[$subject][$predicate][] = $object;
	}

	/**
	 * Adds the given triple/double/single value as an additional condition
	 * to the previously added condition.
	 *
	 * @param string|null $subject
	 * @param string|null $predicate
	 * @param string $object
	 */
	public function plus( $subject, $predicate, $object ) {
		$this->where(
			$subject ?: $this->currentSubject,
			$predicate ?: $this->currentPredicate,
			$object
		);
	}

	/**
	 * Returns the plain SPARQL string of these conditions.
	 * Surrounding brackets are not included.
	 *
	 * @return string
	 */
	public function getSPARQL() {
		$sparql = '';

		foreach ( $this->conditions as $subject => $predicates ) {
			$sparql .= ' ' . $subject;
			$sparql .= $this->formatPredicates( $predicates ) . ' .';
		}

		return $sparql;
	}

	private function formatPredicates( array $predicates ) {
		return implode( ' ;', array_map( function( $predicate, $objects ) {
			return ' ' . $predicate . $this->formatObjects( $objects );
		}, array_keys( $predicates ), $predicates ) );
	}

	private function formatObjects( array $objects ) {
		return implode( ' ,', array_map( function( $object ) {
			return ' ' . $object;
		}, $objects ) );
	}

}
