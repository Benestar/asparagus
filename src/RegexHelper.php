<?php

namespace Asparagus;

/**
 * Package-private class to help with regexes.
 *
 * Supported magic words are:
 * - \{variable}
 * - \{iri}
 * - \{prefix}
 * - \{name}
 * - \{prefixed_iri}
 * - \{native}
 * - \{path}
 * - \{function}
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class RegexHelper {

	/**
	 * @var string regex to match variables
	 */
	private static $variable = '[?$](\w+)';

	/**
	 * @var string regex to match IRIs
	 */
	private static $iri = '[^\s<>"{}|\\\\^`]+';

	/**
	 * @var string regex to match prefixes
	 */
	private static $prefix = '\w+';

	/**
	 * @var string regex to match names after prefixes
	 */
	private static $name = '\w+';

	/**
	 * @var string regex to match strings and numbers
	 */
	private static $native = '([0-9]+|".*")';

	/**
	 * @var string[] list of natively supported functions
	 */
	private static $functions = array(
		'COUNT', 'SUM', 'MIN', 'MAX', 'AVG', 'SAMPLE', 'GROUP_CONCAT', 'STR',
		'LANG', 'LANGMATCHES', 'DATATYPE', 'BOUND', 'IRI', 'URI', 'BNODE',
		'RAND', 'ABS', 'CEIL', 'FLOOR', 'ROUND', 'CONCAT', 'STRLEN', 'UCASE',
		'LCASE', 'ENCODE_FOR_URI', 'CONTAINS', 'STRSTARTS', 'STRENDS',
		'STRBEFORE', 'STRAFTER', 'YEAR', 'MONTH', 'DAY', 'HOURS', 'MINUTES',
		'SECONDS', 'TIMEZONE', 'TZ', 'NOW', 'UUID', 'STRUUID', 'MD5', 'SHA1',
		'SHA256', 'SHA384', 'SHA512', 'COALESCE', 'IF', 'STRLANG', 'STRDT',
		'sameTerm', 'isIRI', 'isURI', 'isBLANK', 'isLITERAL', 'isNUMERIC',
		'REGEX', 'SUBSTR', 'REPLACE'
	);

	/**
	 * Checks if the expression matches the given regex.
	 *
	 * @param string $regex
	 * @param string $expression
	 * @return bool
	 */
	public function matchesRegex( $regex, $expression ) {
		return preg_match( '/^' . $this->resolveMagic( $regex ) . '$/i', $expression ) === 1;
	}

	/**
	 * Returns all matching groups for the given regex.
	 * String and IRI equences are automatically escaped.
	 *
	 * @param string $regex
	 * @param string $expression
	 * @param int $group
	 * @return string[]
	 */
	public function getMatches( $regex, $expression, $group = 1 ) {
		if ( preg_match_all(
			'/' . $this->resolveMagic( $regex ) . '/',
			$this->escapeSequences( $expression ),
			$matches
		) ) {
			return $matches[$group];
		}

		return array();
	}

	/**
	 * Escapes all sequences (IRIs and strings) and sets the replacements.
	 *
	 * @param string $expression
	 * @param string[] $replacements
	 * @return string
	 */
	public function escapeSequences( $expression, &$replacements = null ) {
		$replacements = array();
		// @todo this is not completely safe but works in most cases
		// @todo for strings use http://stackoverflow.com/questions/171480/regex-grabbing-values-between-quotation-marks
		return preg_replace_callback(
			'/("([^\"]*)"|\<([^\>]*)\>)/',
			function( $match ) use ( &$replacements ) {
				$key = '<' . md5( $match[0] ) . '>';
				$replacements[$key] = $match[0];
				return $key;
			},
			$expression
		);
	}

	private function resolveMagic( $regex ) {
		$magics = array(
			'\{variable}' => self::$variable,
			'\{iri}' => self::$iri,
			'\{prefix}' => self::$prefix,
			'\{name}' => self::$name,
			'\{prefixed_iri}' => $this->getPrefixedIriRegex(),
			'\{native}' => self::$native,
			'\{path}' => $this->getPathRegex(),
			'\{function}' => $this->getFunctionRegex()
		);

		return strtr( $regex, $magics );
	}

	private function getPrefixedIriRegex() {
		return '(' . self::$prefix . ':' . self::$name . '|\<' . self::$iri . '\>)';
	}

	private function getPathRegex() {
		$element = '!?\^?(a|' . $this->getPrefixedIriRegex() . '|\((?1)\))(\?|\*|\+)?';
		return '(' . $element . '([\/\|]' . $element . ')*)';
	}

	private function getFunctionRegex() {
		$allowed = array_merge( self::$functions, array( '\<' . self::$iri . '\>', self::$prefix . ':', self::$variable, '!' ) );
		return '(' . implode( '|', $allowed ) . ').*';
	}

}
