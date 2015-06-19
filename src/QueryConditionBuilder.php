<?php

namespace Asparagus;

/**
 * Package-private class to build the conditions of a SPARQL query.
 *
 * @todo support filter, optional, union, minus
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryConditionBuilder {

	/**
	 * @var array nested list of conditions, grouped by subject and predicate
	 */
	private $conditions = array();

	/**
	 * @var string[] list of filter expressions
	 */
	private $filters = array();

	/**
	 * @var string
	 */
	private $currentSubject = null;

	/**
	 * @var string
	 */
	private $currentPredicate = null;

	/**
	 * @var ExpressionValidator
	 */
	private $expressionValidator;

	public function __construct() {
		$this->expressionValidator = new ExpressionValidator();
	}

	/**
	 * Adds the given triple as a condition.
	 *
	 * @param string $subject
	 * @param string $predicate
	 * @param string $object
	 */
	public function where( $subject, $predicate, $object ) {
		$this->expressionValidator->validate( $subject,
			ExpressionValidator::VALIDATE_PREFIXED_IRI | ExpressionValidator::VALIDATE_VARIABLE
		);
		$this->expressionValidator->validate( $predicate,
			ExpressionValidator::VALIDATE_PATH | ExpressionValidator::VALIDATE_VARIABLE
		);
		$this->expressionValidator->validate( $object,
			ExpressionValidator::VALIDATE_PREFIXED_IRI | ExpressionValidator::VALIDATE_VARIABLE
		);

		$this->currentSubject = $subject;
		$this->currentPredicate = $predicate;
		$this->conditions[$subject][$predicate][] = $object;
	}

	/**
	 * Adds the given triple/double/single value as an additional condition
	 * to the previously added condition.
	 *
	 * @param string $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 */
	public function also( $subject, $predicate = null, $object = null ) {
		if ( $predicate === null ) {
			$this->where( $this->currentSubject, $this->currentPredicate, $subject );
		} else if ( $object === null ) {
			$this->where( $this->currentSubject, $subject, $predicate );
		} else {
			$this->where( $subject, $predicate, $object );
		}
	}

	/**
	 * Adds the given expression as a filter to this query.
	 *
	 * @param string $expression
	 */
	public function filter( $expression ) {
		$this->expressionValidator->validate( $expression, ExpressionValidator::VALIDATE_FUNCTION );
		$this->filters[] = $expression;
	}

	/**
	 * Adds a filter that the given condition builder exists.
	 *
	 * @param QueryConditionBuilder $conditionBuilder
	 */
	public function filterExists( QueryConditionBuilder $conditionBuilder ) {
		// @todo track variables and prefixes
		$this->filters[] = 'EXISTS {' . $conditionBuilder->getSPARQL() . ' }';
	}

	/**
	 * Adds a filter that the given condition builder does not exist.
	 *
	 * @param QueryConditionBuilder $conditionBuilder
	 */
	public function filterNotExists( QueryConditionBuilder $conditionBuilder ) {
		// @todo track variables and prefixes
		$this->filters[] = 'NOT EXISTS {' . $conditionBuilder->getSPARQL() . ' }';
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

		$sparql .= $this->formatFilters();

		return $sparql;
	}

	private function formatPredicates( array $predicates ) {
		return implode( ' ;', array_map( function( $predicate, $objects ) {
			return ' ' . $predicate . ' ' . implode( ' , ', $objects );
		}, array_keys( $predicates ), $predicates ) );
	}

	private function formatFilters() {
		return implode( array_map( function( $filter ) {
			return ' FILTER ' . $filter;
		}, $this->filters ) );
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
