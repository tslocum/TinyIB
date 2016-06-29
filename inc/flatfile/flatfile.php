<?php
/*
Copyright (c) 2005 Luke Plant <L.Plant.98@cantab.net>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and 
associated documentation files (the "Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or 
sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject 
to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial
 portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN 
NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * Simple but powerful flatfile database
 * See http://lukeplant.me.uk/resources/flatfile/ for documentation and examples
 *
 * @tutorial flatfile.pkg
 * @package flatfile
 * @license http://www.opensource.org/licenses/mit-license.php
 */

require_once('flatfile_utils.php');

/** Used to indicate the default comparison should be done, which is STRING_COMPARISON in the absence of a schema, or whatever the schema specifies if one has been added */
define('DEFAULT_COMPARISON', '');
/** Used to indicate a comparison should be done as a string comparison */
define('STRING_COMPARISON', 'strcmp');
/** Used to indicate a comparison should be done as an integer comparison */
define('INTEGER_COMPARISON', 'intcmp');
/** Used to indicate a comparison should be done as a numeric (float) comparison */
define('NUMERIC_COMPARISON', 'numcmp');

/** Indicates ascending order */
define('ASCENDING', 1);
/** Indicates descending order */
define('DESCENDING', -1);

$comparison_type_for_col_type = array(
	INT_COL => INTEGER_COMPARISON,
	DATE_COL => INTEGER_COMPARISON, // assume Unix timestamps
	STRING_COL => STRING_COMPARISON,
	FLOAT_COL => NUMERIC_COMPARISON
);

function get_comparison_type_for_col_type($coltype) {
	global $comparison_type_for_col_type;
	return $comparison_type_for_col_type[$coltype];
}

/**
 * Provides simple but powerful flatfile database storage and retrieval
 *
 * Includes equivalents to SELECT * FROM table WHERE..., DELETE WHERE ...
 * UPDATE and more.  All files are stored in the {@link Flatfile::$datadir $datadir} directory,
 * and table names are just filenames in that directory.  Subdirectories
 * can be used just by specifying a table name that includes the directory name.
 * @package flatfile
 */
class Flatfile {
	/** @access private */
	var $tables;

	/** @access private */
	var $schemata;

	/** The directory to store files in.
	 * @var string
	 */
	var $datadir;

	function __construct() {
		$this->schemata = array();
	}

	function Flatfile() {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct();
		}
	}

	/**
	 * Get all rows from a table
	 * @param string $tablename The table to get rows from
	 * @return array The table as an array of rows, where each row is an array of columns
	 */
	function selectAll($tablename) {
		if (!isset($this->tables[$tablename]))
			$this->loadTable($tablename);
		return $this->tables[$tablename];
	}

	/**
	 * Selects rows from a table that match the specified criteria
	 *
	 * This simulates the following SQL query:
	 * <pre>
	 *   SELECT LIMIT $limit * FROM  $tablename
	 *   WHERE $whereclause
	 *   ORDER BY $orderBy [ASC | DESC] [, $orderBy2 ...]
	 * </pre>
	 *
	 * @param string $tablename The table (file) to get the data from
	 * @param object $whereClause Either a {@link WhereClause WhereClause} object to do selection of rows, or NULL to select all
	 * @param mixed $limit Specifies limits for the rows returned:
	 * - use -1 or omitted to return all rows
	 * - use an integer n to return the first n rows
	 * - use a two item array ($startrow, $endrow) to return rows $startrow to $endrow - 1 (zero indexed)
	 * - use a two item array ($startrow, -1) to return rows $startrow to the end (zero indexed)
	 * @param mixed $orderBy Either an {@link OrderBy} object or an array of them, defining the sorting that should be applied (if an array, then the first object in the array is the first key to sort on etc).  Use NULL for no sorting.
	 * @return array The matching data, as an array of rows, where each row is an array of columns
	 */
	function selectWhere($tablename, $whereClause, $limit = -1, $orderBy = NULL) {
		if (!isset($this->tables[$tablename]))
			$this->loadTable($tablename);

		$table = $this->selectAll($tablename); // Get a copy

		$schema = $this->getSchema($tablename);
		if ($orderBy !== NULL)
			usort($table, $this->getOrderByFunction($orderBy, $schema));

		$results = array();
		$count = 0;

		if ($limit == -1)
			$limit = array(0, -1);
		else if (!is_array($limit))
			$limit = array(0, $limit);

		foreach ($table as $row) {
			if ($whereClause === NULL || $whereClause->testRow($row, $schema)) {
				if ($count >= $limit[0])
					$results[] = $row;
				++$count;
				if (($count >= $limit[1]) && ($limit[1] != -1))
					break;
			}
		}
		return $results;
	}

	/**
	 * Select a row using a unique ID
	 * @param string $tablename The table to get data from
	 * @param string $idField The index of the field containing the ID
	 * @param string $id The ID to search for
	 * @return array    The row of the table as an array
	 */
	function selectUnique($tablename, $idField, $id) {
		$result = $this->selectWhere($tablename, new SimpleWhereClause($idField, '=', $id));
		if (count($result) > 0)
			return $result[0];
		else
			return array();
	}

	/*
	 * To correctly write a file, and not overwrite the changes
	 * another process is making, we need to:
	 *  - get a lock for writing
	 *  - read its contents from disc
	 *  - modify the contents in memory
	 *  - write the contents
	 *  - release lock
	 * Because opening for writing truncates the file, we must get
	 * the lock on a different file.  getLock and releaseLock
	 * are helper functions to allow us to do this with little fuss
	 */

	/** Get a lock for writing a file
	 * @access private
	 */
	function getLock($tablename) {
		ignore_user_abort(true);
		$fp = fopen($this->datadir . $tablename . '.lock', 'w');
		if (!flock($fp, LOCK_EX)) {
			// log error?
		}
		$this->loadTable($tablename);
		return $fp;
	}

	/** Release a lock
	 * @access private
	 */
	function releaseLock($lockfp) {
		flock($lockfp, LOCK_UN);
		ignore_user_abort(false);
	}

	/**
	 * Inserts a row with an automatically generated ID
	 *
	 * The autogenerated ID will be the highest ID in the column so far plus one. The
	 * supplied row should include all fields required for the table, and the
	 * ID field it contains will just be ignored
	 *
	 * @param string $tablename The table to insert data into
	 * @param int $idField The index of the field which is the ID field
	 * @param array $newRow The new row to add to the table
	 * @return int        The newly assigned ID
	 */
	function insertWithAutoId($tablename, $idField, $newRow) {
		$lockfp = $this->getLock($tablename);
		$rows = $this->selectWhere($tablename, null, 1,
			new OrderBy($idField, DESCENDING, INTEGER_COMPARISON));
		if ($rows) {
			$newId = $rows[0][$idField] + 1;
		} else {
			$newId = 1;
		}
		$newRow[$idField] = $newId;
		$this->tables[$tablename][] = $newRow;
		$this->writeTable($tablename);
		$this->releaseLock($lockfp);
		return $newId;
	}

	/**
	 * Inserts a row in a table
	 *
	 * @param string $tablename The table to insert data into
	 * @param array $newRow The new row to add to the table
	 */
	function insert($tablename, $newRow) {
		$lockfp = $this->getLock($tablename);
		$this->tables[$tablename][] = $newRow;
		$this->writeTable($tablename);
		$this->releaseLock($lockfp);
	}

	/**
	 * Updates an existing row using a unique ID
	 *
	 * @param string $tablename The table to update
	 * @param int $idField The index of the field which is the ID field
	 * @param array $updatedRow The updated row to add to the table
	 */
	function updateRowById($tablename, $idField, $updatedRow) {
		$this->updateSetWhere($tablename, $updatedRow,
			new SimpleWhereClause($idField, '=', $updatedRow[$idField]));
	}

	/**
	 * Updates fields in a table for rows that match the provided criteria
	 *
	 * $newFields can be a complete row or it can be a sparsely populated
	 * hashtable of values (where the keys are integers which are the column
	 * indexes to update)
	 *
	 * @param string $tablename The table to update
	 * @param array $newFields A hashtable (with integer keys) of fields to update
	 * @param WhereClause $whereClause The criteria or NULL to update all rows
	 */
	function updateSetWhere($tablename, $newFields, $whereClause) {
		$schema = $this->getSchema($tablename);
		$lockfp = $this->getLock($tablename);
		for ($i = 0; $i < count($this->tables[$tablename]); ++$i) {
			if ($whereClause === NULL ||
				$whereClause->testRow($this->tables[$tablename][$i], $schema)
			) {
				foreach ($newFields as $k => $v) {
					$this->tables[$tablename][$i][$k] = $v;
				}
			}
		}
		$this->writeTable($tablename);
		$this->releaseLock($lockfp);
		$this->loadTable($tablename);
	}

	/**
	 * Deletes all rows in a table that match specified criteria
	 *
	 * @param string $tablename The table to alter
	 * @param object $whereClause .  {@link WhereClause WhereClause} object that will select
	 * rows to be deleted.  All rows are deleted if $whereClause === NULL
	 */
	function deleteWhere($tablename, $whereClause) {
		$schema = $this->getSchema($tablename);
		$lockfp = $this->getLock($tablename);
		for ($i = count($this->tables[$tablename]) - 1; $i >= 0; --$i) {
			if ($whereClause === NULL ||
				$whereClause->testRow($this->tables[$tablename][$i], $schema)
			) {
				unset($this->tables[$tablename][$i]);
			}
		}
		$this->writeTable($tablename);
		$this->releaseLock($lockfp);
		$this->loadTable($tablename); // reset array indexes
	}

	/**
	 * Delete all rows in a table
	 *
	 * @param string $tablename The table to alter
	 */
	function deleteAll($tablename) {
		$this->deleteWhere($tablename, NULL);
	}

	/**#@+
	 * @access private
	 */

	/** Gets a function that can be passed to usort to do the ORDER BY clause
	 * @param mixed $orderBy Either an OrderBy object or an array of them
	 * @return string function name
	 */
	function getOrderByFunction($orderBy, $rowSchema = null) {
		$orderer = new Orderer($orderBy, $rowSchema);
		return array(&$orderer, 'compare');
	}

	function loadTable($tablename) {
		$filedata = @file($this->datadir . $tablename);
		$table = array();
		if (is_array($filedata)) {
			foreach ($filedata as $line) {
				$line = rtrim($line, "\n");
				$table[] = explode("\t", $line);
			}
		}
		$this->tables[$tablename] = $table;
	}

	function writeTable($tablename) {
		$output = '';

		foreach ($this->tables[$tablename] as $row) {
			$keys = array_keys($row);
			rsort($keys, SORT_NUMERIC);
			$max = $keys[0];
			for ($i = 0; $i <= $max; ++$i) {
				if ($i > 0) $output .= "\t";
				$data = (!isset($row[$i]) ? '' : $row[$i]);
				$output .= str_replace(array("\t", "\r", "\n"), array(''), $data);
			}
			$output .= "\n";
		}
		$fp = @fopen($this->datadir . $tablename, "w");
		fwrite($fp, $output, strlen($output));
		fclose($fp);
	}

	/**#@-*/
	/**
	 * Adds a schema definition to the DB for a specified regular expression
	 *
	 * Schemas are optional, and are only used for automatically determining
	 * the comparison types that should be used when sorting and selecting.
	 *
	 * @param string $fileregex A regular expression used to match filenames
	 * @param string $rowSchema An array specifying the column types for data
	 *                           files that match the regex, using constants defined in flatfile_utils.php
	 */
	function addSchema($fileregex, $rowSchema) {
		array_push($this->schemata, array($fileregex, $rowSchema));
	}

	/** Retrieves the schema for a given filename */
	function getSchema($filename) {
		foreach ($this->schemata as $rowSchemaPair) {
			$fileregex = $rowSchemaPair[0];
			if (preg_match($fileregex, $filename)) {
				return $rowSchemaPair[1];
			}
		}
		return null;
	}


}

/////////////////////////// UTILITY FUNCTIONS ////////////////////////////////////

/**
 * equivalent of strcmp for comparing integers, used internally for sorting and comparing
 */
function intcmp($a, $b) {
	return (int)$a - (int)$b;
}

/**
 * equivalent of strcmp for comparing floats, used internally for sorting and comparing
 */
function numcmp($a, $b) {
	return (float)$a - (float)$b;
}

/////////////////////////// WHERE CLAUSE CLASSES ////////////////////////////////////

/**
 * Used to test rows in a database table, like the WHERE clause in an SQL statement.
 *
 * @abstract
 * @package flatfile
 */
class WhereClause {
	/**
	 * Tests a table row object
	 * @abstract
	 * @param array $row The row to test
	 * @param array $rowSchema An optional array specifying the schema of the table, using the INT_COL, STRING_COL etc constants
	 * @return bool True if the $row passes the WhereClause
	 * selection criteria, false otherwise
	 */
	function testRow($row, $rowSchema = null) {
	}
}

/**
 * Negates a where clause
 * @package flatfile
 */
class NotWhere extends WhereClause {
	/** @access private */
	var $clause;

	/**
	 * Contructs a new NotWhere object
	 *
	 * The constructed WhereClause will return the negation
	 * of the WhereClause object passed in when testing rows.
	 * @param WhereClause $whereclause The WhereClause object to negate
	 */
	function __construct($whereclause) {
		$this->clause = $whereclause;
	}

	function NotWhere($whereclause) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($whereclause);
		}
	}

	function testRow($row, $rowSchema = null) {
		return !$this->clause->testRow($row, $rowSchema);
	}
}

/**
 * Implements a single WHERE clause that does simple comparisons of a field
 * with a value.
 *
 * @package flatfile
 */
class SimpleWhereClause extends WhereClause {
	/**#@+
	 * @access private
	 */
	var $field;
	var $operator;
	var $value;
	var $compare_type;

	/**#@-*/

	/**
	 * Creates a new {@link WhereClause WhereClause} object that does a comparison
	 * of a field and a value.
	 *
	 * This will be the most commonly used type of WHERE clause.  It can do comparisons
	 * of the sort "$tablerow[$field] operator $value"
	 * where 'operator' is one of:<br>
	 * - = (equals)
	 * - != (not equals)
	 * - > (greater than)
	 * - < (less than)
	 * - >= (greater than or equal to)
	 * - <= (less than or equal to)
	 * There are 3 pre-defined constants (STRING_COMPARISON, NUMERIC COMPARISON and
	 * INTEGER_COMPARISON) that modify the behaviour of these operators to do the comparison
	 * as strings, floats and integers respectively.  Howevers, these constants are
	 * just the names of functions that do the comparison (the first being the builtin
	 * function {@link strcmp strcmp()}, so you can supply your own function here to customise the
	 * behaviour of this class.
	 *
	 * @param int $field The index (in the table row) of the field to test
	 * @param string $operator The comparison operator, one of "=", "!=", "<", ">", "<=", ">="
	 * @param mixed $value The value to compare to.
	 * @param string $compare_type The comparison method to use - either
	 * STRING_COMPARISON (default), NUMERIC COMPARISON or INTEGER_COMPARISON
	 *
	 */
	function __construct($field, $operator, $value, $compare_type = DEFAULT_COMPARISON) {
		$this->field = $field;
		$this->operator = $operator;
		$this->value = $value;
		$this->compare_type = $compare_type;
	}

	function SimpleWhereClause($field, $operator, $value, $compare_type = DEFAULT_COMPARISON) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($field, $operator, $value, $compare_type);
		}
	}

	function testRow($tablerow, $rowSchema = null) {
		if ($this->field < 0)
			return TRUE;

		$cmpfunc = $this->compare_type;
		if ($cmpfunc == DEFAULT_COMPARISON) {
			if ($rowSchema != null) {
				$cmpfunc = get_comparison_type_for_col_type($rowSchema[$this->field]);
			} else {
				$cmpfunc = STRING_COMPARISON;
			}
		}

		if ($this->field >= count($tablerow)) {
			$dbval = "";
		} else {
			$dbval = $tablerow[$this->field];
		}
		$cmp = $cmpfunc($dbval, $this->value);
		if ($this->operator == '=')
			return ($cmp == 0);
		else if ($this->operator == '!=')
			return ($cmp != 0);
		else if ($this->operator == '>')
			return ($cmp > 0);
		else if ($this->operator == '<')
			return ($cmp < 0);
		else if ($this->operator == '<=')
			return ($cmp <= 0);
		else if ($this->operator == '>=')
			return ($cmp >= 0);

		return FALSE;
	}
}

/**
 * {@link WhereClause WhereClause} class to work like a SQL 'LIKE' clause
 * @package flatfile
 */
class LikeWhereClause extends WhereClause {
	/**
	 * Creates a new LikeWhereClause
	 *
	 * @param int $field Index of the field to look at
	 * @param string $value Value to look for.  Supports using '%' as a
	 *                       wildcard, and is case insensitve.  e.g. 'test%' will match 'TESTS' and 'Testing'
	 */

	function __construct($field, $value) {
		$this->field = $field;
		$this->regexp = '/^' . str_replace('%', '.*', preg_quote($value)) . '$/i';
	}

	function LikeWhereClause($field, $value) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($field, $value);
		}
	}

	function testRow($tablerow, $rowSchema = NULL) {
		return preg_match($this->regexp, $tablerow[$this->field]);
	}
}


/**
 * {@link WhereClause WhereClause} class to match a value from a list of items
 * @package flatfile
 */
class ListWhereClause extends WhereClause {

	/** @access private */
	var $field;
	/** @access private */
	var $list;
	/** @access private */
	var $compareAs;

	/**
	 * Creates a new ListWhereClause object
	 *
	 * The resulting WhereClause will pass rows (return true) if the value of the specified
	 * field is in the array.
	 *
	 * @param int $field Field to match
	 * @param array $list List of items
	 * @param string $compare_type Comparison type, string by default.
	 */
	function __construct($field, $list, $compare_type = DEFAULT_COMPARISON) {
		$this->list = $list;
		$this->field = (int)$field;
		$this->compareAs = $compare_type;
	}

	function ListWhereClause($field, $list, $compare_type = DEFAULT_COMPARISON) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($field, $list, $compare_type);
		}
	}

	function testRow($tablerow, $rowSchema = null) {
		$func = $this->compareAs;
		if ($func == DEFAULT_COMPARISON) {
			if ($rowSchema) {
				$func = get_comparison_type_for_col_type($rowSchema[$this->field]);
			} else {
				$func = STRING_COMPARISON;
			}
		}

		foreach ($this->list as $item) {
			if ($func($tablerow[$this->field], $item) == 0)
				return true;
		}
		return false;
	}
}

/**
 * Abstract class that combines zero or more {@link WhereClause WhereClause} objects
 * together.
 * @package flatfile
 */
class CompositeWhereClause extends WhereClause {
	/**
	 * @var array Stores the child clauses
	 * @access protected
	 */
	var $clauses = array();

	/**
	 * Add a {@link WhereClause WhereClause} to the list of clauses to be used for testing
	 * @param WhereClause $whereClause The WhereClause object to add
	 */
	function add($whereClause) {
		$this->clauses[] = $whereClause;
	}
}

/**
 * {@link CompositeWhereClause CompositeWhereClause} that does an OR on all its
 * child WhereClauses.
 *
 * Use the {@link CompositeWhereClause::add() add()} method and/or the constructor
 * to add WhereClause objects
 * to the list of clauses to check.  The testRow function of the resulting object
 * will then return true if any of its child clauses return true (and returns
 * false if no clauses have been added for consistency).
 * @package flatfile
 */
class OrWhereClause extends CompositeWhereClause {
	function testRow($tablerow, $rowSchema = null) {
		foreach ($this->clauses as $clause) {
			if ($clause->testRow($tablerow, $rowSchema))
				return true;
		}
		return false;
	}

	/**
	 * Creates a new OrWhereClause
	 * @param WhereClause $whereClause,... optional unlimited list of WhereClause objects to be added
	 */
	function __construct() {
		$this->clauses = func_get_args();
	}

	function OrWhereClause() {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct();
		}
	}
}

/**
 * {@link CompositeWhereClause CompositeWhereClause} that does an AND on all its
 * child WhereClauses.
 *
 * Use the {@link CompositeWhereClause::add() add()} method to add WhereClause objects
 * to the list of clauses to check.  The testRow function of the resulting object
 * will then return false if any of its child clauses return false (and returns
 * true if no clauses have been added for consistency).
 * @package flatfile
 */
class AndWhereClause extends CompositeWhereClause {
	function testRow($tablerow, $rowSchema = null) {
		foreach ($this->clauses as $clause) {
			if (!$clause->testRow($tablerow, $rowSchema))
				return false;
		}
		return true;
	}

	/**
	 * Creates a new AndWhereClause
	 * @param WhereClause $whereClause,... optional unlimited list of WhereClause objects to be added
	 */
	function __construct() {
		$this->clauses = func_get_args();
	}

	function AndWhereClause() {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct();
		}
	}
}


/////////////////////////// ORDER BY CLASSES ////////////////////////////////////

/**
 * Stores information about an ORDER BY clause
 *
 * Can be passed to selectWhere to order the output.  It is easiest to use
 * the constructor to set the fields, rather than setting each individually
 * @package flatfile
 */
class OrderBy {
	/** @var int Index of field to order by */
	var $field;
	/** @var int Order type - ASCENDING or DESCENDING */
	var $orderType;
	/** @var string Comparison type  - usually either DEFAULT_COMPARISON, STRING_COMPARISON, INTEGER_COMPARISION, or NUMERIC_COMPARISON */
	var $compareAs;

	/** Creates a new OrderBy structure
	 *
	 * The $compareAs parameter can be supplied using one of the pre-defined constants, but
	 * this is actually implemented by defining the constants as names of functions to do the
	 *  comparison.  You can therefore supply the name of any function that works like
	 * {@link strcmp strcmp()} to implement custom ordering.
	 * @param int $field The index of the field to order by
	 * @param int $orderType ASCENDING or DESCENDING
	 * @param int $compareAs Comparison type: DEFAULT_COMPARISON, STRING_COMPARISON, INTEGER_COMPARISION,
	 * or NUMERIC_COMPARISON, or the name of a user defined function that you want to use for doing the comparison.
	 */
	function __construct($field, $orderType, $compareAs = DEFAULT_COMPARISON) {
		$this->field = $field;
		$this->orderType = $orderType;
		$this->compareAs = $compareAs;
	}

	function OrderBy($field, $orderType, $compareAs) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct();
		}
	}
}

/**
 * Implements the sorting defined by an array of OrderBy objects.  This class
 * is used by {@link Flatfile::selectWhere()}
 * @access private
 * @package flatfile
 */
class Orderer {
	/**
	 * @var array Stores the OrderBy objects
	 * @access private
	 */
	var $orderByList;

	/**
	 * Creates new Orderer that will provide a sort function
	 * @param mixed $orderBy An OrderBy object or an array of them
	 * @param array $rowSchema Option row schema
	 */
	function __construct($orderBy, $rowSchema = null) {
		if (!is_array($orderBy))
			$orderBy = array($orderBy);
		if ($rowSchema) {
			// Fix the comparison types
			foreach ($orderBy as $index => $discard) {
				$item =& $orderBy[$index]; // PHP4
				if ($item->compareAs == DEFAULT_COMPARISON) {
					$item->compareAs = get_comparison_type_for_col_type($rowSchema[$item->field]);
				}
			}
		}
		$this->orderByList = $orderBy;
	}

	function Orderer($orderBy, $rowSchema = null) {
		if (version_compare(PHP_VERSION, '5.0.0', '<')) {
			$this->__construct($orderBy, $rowSchema);
		}
	}

	/**
	 * Compares two table rows using the comparisons defined by the OrderBy
	 * objects.  This function is of the type that can be used passed to usort().
	 */
	function compare($row1, $row2) {
		return $this->compare_priv($row1, $row2, 0);
	}

	/**
	 * @access private
	 */
	function compare_priv($row1, $row2, $index) {
		$orderBy = $this->orderByList[$index];
		$cmpfunc = $orderBy->compareAs;
		if ($cmpfunc == DEFAULT_COMPARISON) {
			$cmpfunc = STRING_COMPARISON;
		}
		$cmp = $orderBy->orderType * $cmpfunc(isset($row1[$orderBy->field]) ? $row1[$orderBy->field] : false, isset($row2[$orderBy->field]) ? $row2[$orderBy->field] : false);
		if ($cmp == 0) {
			if ($index == (count($this->orderByList) - 1))
				return 0;
			else
				return $this->compare_priv($row1, $row2, $index + 1);
		} else
			return $cmp;
	}
}
