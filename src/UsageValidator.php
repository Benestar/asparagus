<?php

namespace Asparagus;

use RangeException;

/**
 * Package-private class to validate the usage of variables and prefixes.
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class UsageValidator {

	/**
	 * @var string[] list of used variables without prefixes
	 */
	private $usedVariables = array();

	/**
	 * @var string[] list of defined variables without prefixes
	 */
	private $definedVariables = array();

	/**
	 * @var string[] list of used prefixes
	 */
	private $usedPrefixes = array();

	/**
	 * @var string[] list of defined prefixes
	 */
	private $definedPrefixes = array();

	/**
	 * Trackes the list of variables as used.
	 *
	 * @param string[] $variables list of variables without prefixes
	 */
	public function trackUsedVariables( array $variables ) {
		$this->usedVariables = array_merge( $this->usedVariables, $variables );
	}

	/**
	 * Trackes the list of variables as defined.
	 *
	 * @param string[] $variables list of variables without prefixes
	 */
	public function trackDefinedVariables( array $variables )  {
		$this->definedVariables = array_merge( $this->definedVariables, $variables );
	}

	/**
	 * Trackes the list of prefixes as used.
	 *
	 * @param string[] $prefixes list of prefixes
	 */
	public function trackUsedPrefixes( array $prefixes )  {
		$this->usedPrefixes = array_merge( $this->usedPrefixes, $prefixes );
	}

	/**
	 * Trackes the list of prefixes as defined.
	 *
	 * @param string[] $prefixes list of prefixes
	 */
	public function trackDefinedPrefixes( array $prefixes )  {
		$this->definedPrefixes = array_merge( $this->definedPrefixes, $prefixes );
	}

	/**
	 * Validates the variables and prefixes tracked.
	 *
	 * @throws RangeException
	 */
	public function validate() {
		$this->validatePrefixes();
		$this->validateVariables();
	}

	private function validatePrefixes() {
		$diff = array_diff( $this->usedPrefixes, $this->definedPrefixes );
		if ( !empty( $diff ) ) {
			throw new RangeException( 'The prefixes ' . implode( ', ', $diff ) . ' aren\'t defined for this query.' );
		}
	}

	private function validateVariables() {
		$diff = array_diff( $this->usedVariables, $this->definedVariables );
		if ( !empty( $diff ) ) {
			throw new RangeException( 'The variables ?' . implode( ', ?', $diff ) . ' don\'t occur in this query.' );
		}
	}

}
