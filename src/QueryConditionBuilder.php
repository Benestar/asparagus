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
	 * @param string|null $subject
	 * @param string|null $predicate
	 * @param string $object
	 */
	public function also( $subject, $predicate, $object ) {
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
			return ' ' . $predicate . ' ' . implode( ' , ', $objects );
		}, array_keys( $predicates ), $predicates ) );
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
