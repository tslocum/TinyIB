<?php

// Utilities for flatfile functions

/** Constant to indicating a column holding floating point numbers */
define('FLOAT_COL', 'float');
/** Constant to indicating a column holding integers */
define('INT_COL', 'int');
/** Constant to indicating a column holding strings */
define('STRING_COL', 'string');
/** Constant to indicating a column holding unix timestamps */
define('DATE_COL', 'date');


/** EXPERIMENTAL: Encapsulates info about a column in a flatfile DB */
class Column {
	/**
	 * Create a new column object
	 */
	function __construct($index, $type) {
		$this->index = $index;
		$this->type = $type;
	}

	function Column($index, $type) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($index, $type);
		}
	}
}

/** EXPERIMENTAL: Represent a column that is a foreign key.  Used for temporarily building tables array */
class JoinColumn {
	function __construct($index, $tablename, $columnname) {
		$this->index = $index;
		$this->tablename = $tablename;
		$this->columnname = $columnname;
	}

	function JoinColumn($index, $tablename, $columnname) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($index, $tablename, $columnname);
		}
	}
}

/**
 * EXPERIMENTAL: Utilities for handling definitions of tables.
 */
class TableUtils {
	/**
	 * Finds JoinColumns in an array of tables, and adds 'type' fields by looking up the columns
	 *
	 * @param tables This should be an associative array containing 'tablename' => tabledefinition
	 * tabledefinition is itself an associativive array of 'COLUMN_NAME_CONSTANT' => columndefintion
	 * COLUMN_NAME_CONSTANT should be a unique constant within the table, and
	 * column definition should be a Column object or JoinColumn object
	 */
	function resolveJoins(&$tables) {
		foreach ($tables as $tablename => $discard) {
			// PHP4 compatible: can't do :  foreach ($tables as $tablename => &$tabledef)
			// and strangely, if we do
			// foreach ($tables as $tablename => &$tabledef)
			// 	$tabledef =& $tables[$tablename];
			// then we get bugs
			$tabledef =& $tables[$tablename];
			foreach ($tabledef as $colname => $discard) {
				$coldef =& $tabledef[$colname]; // PHP4 compatible
				if (is_a($coldef, 'JoinColumn') or is_subclass_of($coldef, 'JoinColumn')) {
					TableUtils::resolveColumnJoin($coldef, $tables);
				}
			}
		}
	}

	/** @access private */
	function resolveColumnJoin(&$columndef, &$tables) {
		// Doesn't work if the column it is joined to is also
		// a JoinColumn, but I can't think of ever wanting to do that
		$columndef->type = $tables[$columndef->tablename][$columndef->columnname]->type;
	}

	/** Uses 'define' to create global constants for all the column names */
	function createDefines(&$tables) {
		foreach ($tables as $tablename => $discard) {
			$tabledef = &$tables[$tablename]; // PHP4 compatible
			foreach ($tabledef as $colname => $discard) {
				$coldef = &$tabledef[$colname];
				define(strtoupper($tablename) . '_' . $colname, $coldef->index);
			}
		}
	}

	/**
	 * Creates a 'row schema' for a given table definition.
	 *
	 * A row schema is just an array of the column types for a table,
	 * using the constants defined above.
	 */
	function createRowSchema(&$tabledef) {
		$row_schema = array();
		foreach ($tabledef as $colname => $coldef) {
			$row_schema[$coldef->index] = $coldef->type;
		}
		return $row_schema;
	}
}
