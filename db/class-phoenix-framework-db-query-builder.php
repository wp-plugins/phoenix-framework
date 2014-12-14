<?php


	final class Phoenix_Framework_DB_Query_Builder extends Phoenix_Framework_Object {
		const PARAM_PREFIX = '';
		public $separator = " ";
		public $table_prefix = '';
		public $calc_found_rows = false;
		public $total;
		protected $conditionBuilders = array(
			'NOT'         => 'buildNotCondition',
			'AND'         => 'buildAndCondition',
			'OR'          => 'buildAndCondition',
			'BETWEEN'     => 'buildBetweenCondition',
			'NOT BETWEEN' => 'buildBetweenCondition',
			'IN'          => 'buildInCondition',
			'NOT IN'      => 'buildInCondition',
			'LIKE'        => 'buildLikeCondition',
			'NOT LIKE'    => 'buildLikeCondition',
			'OR LIKE'     => 'buildLikeCondition',
			'OR NOT LIKE' => 'buildLikeCondition',
			'EXISTS'      => 'buildExistsCondition',
			'NOT EXISTS'  => 'buildExistsCondition',
		);
		private $_query;

		public function add_param( $name = null, $value = null, $type = '%s' ) {

			if ( $name === null ) {
				return $this;
			}

			$this->_query[ 'params' ][ $name ] = array( $value, $type );

			return $this;
		}

		/**
		 * Appends a SQL statement using UNION operator.
		 *
		 * @param string|Phoenix_Framework_DB_Query_Builder $sql the SQL statement to be appended using UNION
		 * @param boolean                                   $all TRUE if using UNION ALL and FALSE if using UNION
		 *
		 * @return static the query object itself
		 */
		public function union( $sql, $all = false ) {
			$this->_query[ 'union' ][ ] = array( 'query' => $sql, 'all' => $all );

			return $this;
		}

		public function buildAndCondition( $operator, $operands, &$params ) {
			$parts = array();
			foreach ( $operands as $operand ) {
				if ( is_array( $operand ) ) {
					$operand = $this->buildCondition( $operand, $params );
				}
				if ( $operand !== '' ) {
					$parts[ ] = $operand;
				}
			}
			if ( ! empty( $parts ) ) {
				return '(' . implode( ") $operator (", $parts ) . ')';
			} else {
				return '';
			}
		}

		public function buildCondition( $condition, &$params ) {
			if ( ! is_array( $condition ) ) {
				return (string) $condition;
			} elseif ( empty( $condition ) ) {
				return '';
			}


			if ( isset( $condition[ 0 ] ) ) { // operator format: operator, operand 1, operand 2, ...
				$operator = strtoupper( $condition[ 0 ] );
				if ( isset( $this->conditionBuilders[ $operator ] ) ) {
					$method = $this->conditionBuilders[ $operator ];
					array_shift( $condition );

					$output = $this->$method( $operator, $condition, $params );

					return $output;
				} else {
					//throw new InvalidParamException('Found unknown operator in query: ' . $operator);
					return '';
				}
			} else { // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
				return $this->buildHashCondition( $condition, $params );
			}
		}

		public function buildHashCondition( $condition, &$params ) {
			$parts = array();
			foreach ( $condition as $column => $value ) {
				if ( is_array( $value ) || $value instanceof Phoenix_Framework_DB_Query_Builder ) {
					// IN condition
					$parts[ ] = $this->buildInCondition( 'IN', array( $column, $value ), $params );
				} else {
					if ( strpos( $column, '(' ) === false ) {
						$column = $this->quoteColumnName( $column );
					}
					if ( $value === null ) {
						$parts[ ] = "$column IS NULL";
					} elseif ( $value instanceof Phoenix_framework_DB_Expression ) {
						$parts[ ] = "$column=" . $value->expression;
						foreach ( $value->params as $n => $v ) {
							$params[ $n ] = $v;
						}
					} else {
						$phName            = self::PARAM_PREFIX . count( $params );
						$parts[ ]          = "$column=$phName";
						$params[ $phName ] = $value;
					}
				}
			}

			return count( $parts ) === 1 ? $parts[ 0 ] : '(' . implode( ') AND (', $parts ) . ')';
		}

		public function buildInCondition( $operator, $operands, &$params ) {
			if ( ! isset( $operands[ 0 ], $operands[ 1 ] ) ) {
				throw new Exception( "Operator '$operator' requires two operands." );
			}

			list( $column, $values ) = $operands;

			if ( $values === array() || $column === array() ) {
				return $operator === 'IN' ? '0=1' : '';
			}

			if ( $values instanceof Phoenix_Framework_DB_Query_Builder ) {
				// sub-query
				list( $sql, $params ) = $this->build( $values, $params );
				$column = (array) $column;
				if ( is_array( $column ) ) {
					foreach ( $column as $i => $col ) {
						if ( strpos( $col, '(' ) === false ) {
							$column[ $i ] = $this->quoteColumnName( $col );
						}
					}

					return '(' . implode( ', ', $column ) . ") $operator ($sql)";
				} else {
					if ( strpos( $column, '(' ) === false ) {
						$column = $this->quoteColumnName( $column );
					}

					return "$column $operator ($sql)";
				}
			}

			$values = (array) $values;


			if ( count( $column ) > 1 ) {
				return $this->buildCompositeInCondition( $operator, $column, $values, $params );
			}

			if ( is_array( $column ) ) {
				$column = reset( $column );
			}

			foreach ( $values as $i => $value ) {
				if ( is_array( $value ) ) {
					$value = isset( $value[ $column ] ) ? $value[ $column ] : null;
				}
				if ( $value === null ) {
					$values[ $i ] = 'NULL';
				} elseif ( $value instanceof Phoenix_Framework_DB_Expression ) {
					$values[ $i ] = $value->expression;
					foreach ( $value->params as $n => $v ) {
						$params[ $n ] = $v;
					}
				} else {
//				$phName            = self::PARAM_PREFIX . count( $params );
//				$params[ $phName ] = $value;
					$values[ $i ] = $value;
				}
			}


			if ( strpos( $column, '(' ) === false ) {
				$column = $this->quoteColumnName( $column );
			}

			if ( count( $values ) > 1 ) {
				return "$column $operator (" . implode( ', ', $values ) . ')';
			} else {
				$operator = $operator === 'IN' ? '=' : '<>';

				return $column . $operator . reset( $values );
			}
		}

		public function build( $query = null, $params = array() ) {
			if ( empty( $query ) || ! is_object( $query ) ) {
				$query = $this;
			}
			$clauses = array(
				$this->buildSelect( $this->get_query_param( $query, 'select' ), $params ),
				$this->buildFrom( $this->get_query_param( $query, 'from' ), $params ),
				$this->buildJoin( $this->get_query_param( $query, 'join' ), $params ),
				$this->buildWhere( $this->get_query_param( $query, 'where' ), $params ),
				$this->buildGroupBy( $this->get_query_param( $query, 'groupBy' ) ),
				$this->buildHaving( $this->get_query_param( $query, 'having' ), $params ),
				$this->buildOrderBy( $this->get_query_param( $query, 'orderBy' ) ),
				$this->buildLimit( $this->get_query_param( $query, 'limit' ), $this->get_query_param( $query, 'offset' ) ),
			);


			$sql = implode( $this->separator, array_filter( $clauses ) );
			$sql = $this->apply_prepare( $query, $sql );

			$union = $this->buildUnion( $this->get_query_param( $query, 'union' ), $params );
			if ( $union !== '' ) {
				$sql = "($sql){$this->separator}$union";
			}

			return array( $sql, $params );
		}

		public function buildSelect( $columns, &$params ) {

			if ( empty( $columns ) ) {
				return 'SELECT *';
			}

			$_cols = array();
			foreach ( $columns as $column ) {

				$distinct = false;
				$as       = false;
				$upper    = strtoupper( $column );


				if ( strpos( $upper, 'DISTINCT' ) === 0 ) {
					$distinct = true;
				}

				if ( strpos( $upper, ' AS ' ) !== false ) {
					$as = preg_replace( '/^.* as (.+)/i', '$1', $column );
				}

				$column_name = preg_replace( '/^DISTINCT /i', '', $column );
				if ( $as ) {
					$column_name = preg_replace( '/ as .*$/i', '', $column_name );
				}

				$item = '';
				if ( $distinct ) {
					$item .= 'DISTINCT ';
				}
				$item .= $this->quoteColumnName( $column_name );
				if ( $as !== false ) {
					$item .= " AS {$as}";
				}

				$_cols[ ] = $item;

			}

			return 'SELECT ' . ( $this->calc_found_rows ? 'SQL_CALC_FOUND_ROWS ' : '' ) . implode( ', ', $_cols );

		}

		public function quoteColumnName( $name ) {
			if ( strpos( $name, '(' ) !== false || strpos( $name, '[[' ) !== false || strpos( $name, '{{' ) !== false ) {
				return $name;
			}
			if ( ( $pos = strrpos( $name, '.' ) ) !== false ) {
				$prefix = $this->quoteIndexName( substr( $name, 0, $pos ) ) . '.';
				$name   = substr( $name, $pos + 1 );
			} else {
				$prefix = '';
			}

			return $prefix . $this->quoteSimpleColumnName( $name );
		}

		public function quoteIndexName( $name ) {
			if ( strpos( $name, '(' ) !== false || strpos( $name, '{{' ) !== false ) {
				return $name;
			}

			return $this->quoteSimpleIndexName( $name );
		}

		public function quoteSimpleIndexName( $name ) {
			return strpos( $name, "`" ) !== false ? $name : "`" . $name . "`";
		}

		public function quoteSimpleColumnName( $name ) {
			return strpos( $name, '`' ) !== false || $name === '*' ? $name : '`' . $name . '`';
		}

		function get_query_param( $query_object, $param ) {
			return isset( $query_object->_query[ $param ] ) ? $query_object->_query[ $param ] : null;
		}

		public function buildFrom( $tables, &$params ) {
			if ( empty( $tables ) ) {
				return '';
			}

			$_tables = array();
			foreach ( $tables as $table ) {

				if ( strpos( strtoupper( $table ), ' ' ) !== false ) {
					$tbl        = explode( ' ', $table );
					$_tables[ ] = $this->quoteTableName( $tbl[ 0 ] ) . " {$tbl[1]}";
				} else {
					$_tables[ ] = $this->quoteTableName( $table );
				}

			}

			return 'FROM ' . implode( ', ', $_tables );

//		$_tables = array();
//		foreach ( (array)$tables as $table ) {
//			$_tables[ ] = $this->quoteTableName( $table );
//		}
//
//		return 'FROM ' . implode( ', ', $_tables );
		}

		public function quoteTableName( $name ) {
			return $this->quoteIndexName( $this->table_prefix . $name );
		}

		public function buildJoin( $joins, &$params ) {
			if ( empty( $joins ) ) {
				return '';
			}

			foreach ( $joins as $i => $join ) {
				if ( ! is_array( $join ) || ! isset( $join[ 0 ], $join[ 1 ] ) ) {
					return '';
				}
				// 0:join type, 1:join table, 2:on-condition (optional)
				list ( $joinType, $table ) = $join;

				if ( strpos( $table, ' ' ) !== false ) {
					$_tbl  = explode( ' ', $table );
					$table = $this->quoteTableName( $_tbl[ 0 ] ) . ' ' . $_tbl[ 1 ];
				} else {
					$table = $this->quoteTableName( $table );
				}

				$joins[ $i ] = "$joinType $table";
				if ( isset( $join[ 2 ] ) ) {
					$condition = $this->buildCondition( $join[ 2 ], $params );
					if ( $condition !== '' ) {
						$joins[ $i ] .= ' ON ' . $condition;
					}
				}
			}

			return implode( $this->separator, $joins );
		}

		public function quoteTableNames( $tables ) {
			$_tables = array();
			foreach ( $tables as $table ) {
				$_tables[ ] = $this->quoteTableName( $table );
			}

			return $_tables;
		}

		public function buildWhere( $condition, &$params ) {
			$where = $this->buildCondition( $condition, $params );

			return $where === '' ? '' : 'WHERE ' . $where;
		}

		public function buildGroupBy( $columns ) {
			return empty( $columns ) ? '' : 'GROUP BY ' . $this->buildColumns( $columns );
		}

		public function buildColumns( $columns ) {
			if ( ! is_array( $columns ) ) {
				if ( strpos( $columns, '(' ) !== false ) {
					return $columns;
				} else {
					$columns = preg_split( '/\s*,\s*/', $columns, - 1, PREG_SPLIT_NO_EMPTY );
				}
			}
			foreach ( $columns as $i => $column ) {
				if ( $column instanceof Phoenix_Framework_DB_Expression ) {
					$columns[ $i ] = $column->expression;
				} elseif ( strpos( $column, '(' ) === false ) {
					$columns[ $i ] = $this->quoteColumnName( $column );
				}
			}

			return is_array( $columns ) ? implode( ', ', $columns ) : $columns;
		}

		public function buildHaving( $condition, &$params ) {
			$having = $this->buildCondition( $condition, $params );

			return $having === '' ? '' : 'HAVING ' . $having;
		}

		public function buildOrderBy( $columns ) {
			if ( empty( $columns ) ) {
				return '';
			}
			$orders = array();
			foreach ( $columns as $name => $direction ) {
				if ( $direction instanceof Phoenix_Framework_DB_Expression ) {
					$orders[ ] = $direction->expression;
				} else {
					$orders[ ] = $this->quoteColumnName( $name ) . ( $direction === SORT_DESC ? ' DESC' : '' );
				}
			}

			return 'ORDER BY ' . implode( ', ', $orders );
		}

		public function buildLimit( $limit, $offset ) {
			$sql = '';
			if ( $this->hasLimit( $limit ) ) {
				$sql = 'LIMIT ' . $limit;
			}
			if ( $this->hasOffset( $offset ) ) {
				$sql .= ' OFFSET ' . $offset;
			}

			return ltrim( $sql );
		}

		protected function hasLimit( $limit ) {
			return is_string( $limit ) && ctype_digit( $limit ) || is_integer( $limit ) && $limit >= 0;
		}

		protected function hasOffset( $offset ) {
			return is_integer( $offset ) && $offset > 0 || is_string( $offset ) && ctype_digit( $offset ) && $offset !== '0';
		}

		protected function apply_prepare( $query, $sql ) {
			$params = (array) $this->get_query_param( $query, 'params' );
			global $wpdb;
			foreach ( $params as $param_name => $param ) {
				if ( strpos( $sql, $param_name ) === false ) {
					continue;
				}
				$to_prepare = str_replace( $param_name, $param[ 1 ], $sql );
				$sql        = $wpdb->prepare( $to_prepare, $param[ 0 ] );
			}

			return $sql;
		}

		public function buildUnion( $unions, &$params ) {
			if ( empty( $unions ) ) {
				return '';
			}

			$result = '';


			foreach ( $unions as $i => $union ) {

				$query = $union[ 'query' ];
				if ( $query instanceof Phoenix_Framework_DB_Query_Builder ) {
					list( $unions[ $i ][ 'query' ], $params ) = $this->build( $query, $params );
				}

				$result .= 'UNION ' . ( $union[ 'all' ] ? 'ALL ' : '' ) . '( ' . $unions[ $i ][ 'query' ] . ' ) ';
			}

			return trim( $result );
		}

		protected function buildCompositeInCondition( $operator, $columns, $values, &$params ) {
			$vss = array();
			foreach ( $values as $value ) {
				$vs = array();
				foreach ( $columns as $column ) {
					if ( isset( $value[ $column ] ) ) {
						$phName            = self::PARAM_PREFIX . count( $params );
						$params[ $phName ] = $value[ $column ];
						$vs[ ]             = $phName;
					} else {
						$vs[ ] = 'NULL';
					}
				}
				$vss[ ] = '(' . implode( ', ', $vs ) . ')';
			}
			foreach ( $columns as $i => $column ) {
				if ( strpos( $column, '(' ) === false ) {
					$columns[ $i ] = $this->quoteColumnName( $column );
				}
			}

			return '(' . implode( ', ', $columns ) . ") $operator (" . implode( ', ', $vss ) . ')';
		}

		public function buildNotCondition( $operator, $operands, &$params ) {
			if ( count( $operands ) != 1 ) {
				return '';
				//throw new InvalidParamException("Operator '$operator' requires exactly one operand.");
			}

			$operand = reset( $operands );
			if ( is_array( $operand ) ) {
				$operand = $this->buildCondition( $operand, $params );
			}
			if ( $operand === '' ) {
				return '';
			}

			return "$operator ($operand)";
		}

		public function buildBetweenCondition( $operator, $operands, &$params ) {
			if ( ! isset( $operands[ 0 ], $operands[ 1 ], $operands[ 2 ] ) ) {
				return '';
				//throw new InvalidParamException("Operator '$operator' requires three operands.");
			}

			list( $column, $value1, $value2 ) = $operands;

			if ( strpos( $column, '(' ) === false ) {
				$column = $this->quoteColumnName( $column );
			}
			$phName1            = self::PARAM_PREFIX . count( $params );
			$params[ $phName1 ] = $value1;
			$phName2            = self::PARAM_PREFIX . count( $params );
			$params[ $phName2 ] = $value2;

			return "$column $operator $phName1 AND $phName2";
		}

		public function buildLikeCondition( $operator, $operands, &$params ) {
			if ( ! isset( $operands[ 0 ], $operands[ 1 ] ) ) {
				//throw new InvalidParamException("Operator '$operator' requires two operands.");
				return '';
			}

			$escape = isset( $operands[ 2 ] ) ? $operands[ 2 ] : array( '%' => '\%', '_' => '\_', '\\' => '\\\\' );
			unset( $operands[ 2 ] );

			if ( ! preg_match( '/^(AND |OR |)(((NOT |))I?LIKE)/', $operator, $matches ) ) {
				//throw new InvalidParamException("Invalid operator '$operator'.");
				return '';
			}
			$andor    = ' ' . ( ! empty( $matches[ 1 ] ) ? $matches[ 1 ] : 'AND ' );
			$not      = ! empty( $matches[ 3 ] );
			$operator = $matches[ 2 ];

			list( $column, $values ) = $operands;

			$values = (array) $values;

			if ( empty( $values ) ) {
				return $not ? '' : '0=1';
			}

			if ( strpos( $column, '(' ) === false ) {
				$column = $this->quoteColumnName( $column );
			}

			$parts = array();
			foreach ( $values as $value ) {
				$phName            = self::PARAM_PREFIX . count( $params );
				$params[ $phName ] = empty( $escape ) ? $value : ( '%' . strtr( $value, $escape ) . '%' );
				$parts[ ]          = "$column $operator $phName";
			}

			return implode( $andor, $parts );
		}

		public function buildExistsCondition( $operator, $operands, &$params ) {
			if ( $operands[ 0 ] instanceof Phoenix_Framework_DB_Query_Builder ) {
				list( $sql, $params ) = $this->build( $operands[ 0 ], $params );

				return "$operator ($sql)";
			} else {
				//throw new InvalidParamException('Subquery for EXISTS operator must be a Phoenix_Framework_DB_Query_Builder object.');
				return '';
			}
		}

		/**
		 * Sets the WHERE part of the query but ignores [[isEmpty()|empty operands]].
		 *
		 * This method is similar to [[where()]]. The main difference is that this method will
		 * remove [[isEmpty()|empty query operands]]. As a result, this method is best suited
		 * for building query conditions based on filter values entered by users.
		 *
		 * The following code shows the difference between this method and [[where()]]:
		 *
		 * ```php
		 * // WHERE `age`=:age
		 * $query->filterWhere(['name' => null, 'age' => 20]);
		 * // WHERE `age`=:age
		 * $query->where(['age' => 20]);
		 * // WHERE `name` IS NULL AND `age`=:age
		 * $query->where(['name' => null, 'age' => 20]);
		 * ```
		 *
		 * Note that unlike [[where()]], you cannot pass binding parameters to this method.
		 *
		 * @param array $condition the conditions that should be put in the WHERE part.
		 *                         See [[where()]] on how to specify this parameter.
		 *
		 * @param array $params
		 *
		 * @return static the query object itself.
		 * @see where()
		 * @see andFilterWhere()
		 * @see orFilterWhere()
		 */
		public function filterWhere( array $condition, $params = array() ) {
			$condition2 = $this->filterCondition( $condition );
			if ( $condition2 !== array() ) {
				$this->where( $condition2, $params );
			}

			return $this;
		}

		/**
		 * Removes [[isEmpty()|empty operands]] from the given query condition.
		 *
		 * @param array $condition the original condition
		 *
		 * @return array the condition with [[isEmpty()|empty operands]] removed.
		 * @throws Exception if the condition operator is not supported
		 */
		protected function filterCondition( $condition ) {
			if ( ! is_array( $condition ) ) {
				return $condition;
			}

			if ( ! isset( $condition[ 0 ] ) ) {
				// hash format: 'column1' => 'value1', 'column2' => 'value2', ...
				foreach ( $condition as $name => $value ) {
					if ( $this->isEmpty( $value ) ) {
						unset( $condition[ $name ] );
					}
				}

				return $condition;
			}

			// operator format: operator, operand 1, operand 2, ...

			$operator = array_shift( $condition );

			switch ( strtoupper( $operator ) ) {
				case 'NOT':
				case 'AND':
				case 'OR':
					foreach ( $condition as $i => $operand ) {
						$subCondition = $this->filterCondition( $operand );
						if ( $this->isEmpty( $subCondition ) ) {
							unset( $condition[ $i ] );
						} else {
							$condition[ $i ] = $subCondition;
						}
					}

					if ( empty( $condition ) ) {
						return array();
					}
					break;
				case 'IN':
				case 'NOT IN':
				case 'LIKE':
				case 'OR LIKE':
				case 'NOT LIKE':
				case 'OR NOT LIKE':
				case 'ILIKE': // PostgreSQL operator for case insensitive LIKE
				case 'OR ILIKE':
				case 'NOT ILIKE':
				case 'OR NOT ILIKE':
					if ( array_key_exists( 1, $condition ) && $this->isEmpty( $condition[ 1 ] ) ) {
						return array();
					}
					break;
				case 'BETWEEN':
				case 'NOT BETWEEN':
					if ( array_key_exists( 1, $condition ) && array_key_exists( 2, $condition ) ) {
						if ( $this->isEmpty( $condition[ 1 ] ) || $this->isEmpty( $condition[ 2 ] ) ) {
							return array();
						}
					}
					break;
				default:
					throw new Exception( "Operator not supported: $operator" );
			}

			array_unshift( $condition, $operator );

			return $condition;
		}

		/**
		 * Returns a value indicating whether the give value is "empty".
		 *
		 * The value is considered "empty", if one of the following conditions is satisfied:
		 *
		 * - it is `null`,
		 * - an empty string (`''`),
		 * - a string containing only whitespace characters,
		 * - or an empty array.
		 *
		 * @param mixed $value
		 *
		 * @return boolean if the value is empty
		 */
		protected function isEmpty( $value ) {
			return $value === '' || $value === array() || $value === null || is_string( $value ) && trim( $value ) === '';
		}

		/**
		 * Sets the WHERE part of the query.
		 *
		 * The method requires a $condition parameter, and optionally a $params parameter
		 * specifying the values to be bound to the query.
		 *
		 * The $condition parameter should be either a string (e.g. 'id=1') or an array.
		 * If the latter, it must be in one of the following two formats:
		 *
		 * - hash format: `['column1' => value1, 'column2' => value2, ...]`
		 * - operator format: `[operator, operand1, operand2, ...]`
		 *
		 * A condition in hash format represents the following SQL expression in general:
		 * `column1=value1 AND column2=value2 AND ...`. In case when a value is an array or a
		 * Phoenix_Framework_DB_Query_Builder object, an `IN` expression will be generated. And if a value is null, `IS
		 * NULL` will be used in the generated expression. Below are some examples:
		 *
		 * - `['type' => 1, 'status' => 2]` generates `(type = 1) AND (status = 2)`.
		 * - `['id' => [1, 2, 3], 'status' => 2]` generates `(id IN (1, 2, 3)) AND (status = 2)`.
		 * - `['status' => null] generates `status IS NULL`.
		 * - `['id' => $query]` generates `id IN (...sub-query...)`
		 *
		 * A condition in operator format generates the SQL expression according to the specified operator, which
		 * can be one of the followings:
		 *
		 * - `and`: the operands should be concatenated together using `AND`. For example,
		 *   `['and', 'id=1', 'id=2']` will generate `id=1 AND id=2`. If an operand is an array,
		 *   it will be converted into a string using the rules described here. For example,
		 *   `['and', 'type=1', ['or', 'id=1', 'id=2']]` will generate `type=1 AND (id=1 OR id=2)`.
		 *   The method will NOT do any quoting or escaping.
		 *
		 * - `or`: similar to the `and` operator except that the operands are concatenated using `OR`.
		 *
		 * - `between`: operand 1 should be the column name, and operand 2 and 3 should be the
		 *   starting and ending values of the range that the column is in.
		 *   For example, `['between', 'id', 1, 10]` will generate `id BETWEEN 1 AND 10`.
		 *
		 * - `not between`: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
		 *   in the generated condition.
		 *
		 * - `in`: operand 1 should be a column or DB expression with parenthesis. Operand 2 can be an array
		 *   or a Phoenix_Framework_DB_Query_Builder object. If the former, the array represents the range of the
		 *   values that the column or DB expression should be in. If the latter, a sub-query will be generated to
		 *   represent the range. For example, `['in', 'id', [1, 2, 3]]` will generate `id IN (1, 2, 3)`;
		 *   `['in', 'id', (new Phoenix_Framework_DB_Query_Builder)->select('id')->from('user'))]` will generate
		 *   `id IN (SELECT id FROM user)`. The method will properly quote the column name and escape values in the
		 *   range.
		 *
		 * - `not in`: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated
		 * condition.
		 *
		 * - `like`: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
		 *   the values that the column or DB expression should be like.
		 *   For example, `['like', 'name', 'tester']` will generate `name LIKE '%tester%'`.
		 *   When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
		 *   using `AND`. For example, `['like', 'name', ['test', 'sample']]` will generate
		 *   `name LIKE '%test%' AND name LIKE '%sample%'`.
		 *   The method will properly quote the column name and escape special characters in the values.
		 *   Sometimes, you may want to add the percentage characters to the matching value by yourself, you may supply
		 *   a third operand `false` to do so. For example, `['like', 'name', '%tester', false]` will generate `name
		 *   LIKE '%tester'`.
		 *
		 * - `or like`: similar to the `like` operator except that `OR` is used to concatenate the `LIKE`
		 *   predicates when operand 2 is an array.
		 *
		 * - `not like`: similar to the `like` operator except that `LIKE` is replaced with `NOT LIKE`
		 *   in the generated condition.
		 *
		 * - `or not like`: similar to the `not like` operator except that `OR` is used to concatenate
		 *   the `NOT LIKE` predicates.
		 *
		 * - `exists`: requires one operand which must be an instance of [[Phoenix_Framework_DB_Query_Builder]]
		 * representing the sub-query. It will build a `EXISTS (sub-query)` expression.
		 *
		 * - `not exists`: similar to the `exists` operator and builds a `NOT EXISTS (sub-query)` expression.
		 *
		 * @param string|array $condition the conditions that should be put in the WHERE part.
		 * @param array        $params    the parameters (name => value) to be bound to the query.
		 *
		 * @return static the query object itself
		 * @see andWhere()
		 * @see orWhere()
		 */
		public function where( $condition, $params = array() ) {
			$this->_query[ 'where' ] = $condition;
			call_user_func_array( array( $this, 'add_param' ), $params );

			return $this;
		}

		/**
		 * Adds an additional WHERE condition to the existing one but ignores [[isEmpty()|empty operands]].
		 * The new condition and the existing one will be joined using the 'AND' operator.
		 *
		 * This method is similar to [[andWhere()]]. The main difference is that this method will
		 * remove [[isEmpty()|empty query operands]]. As a result, this method is best suited
		 * for building query conditions based on filter values entered by users.
		 *
		 * @param array $condition the new WHERE condition. Please refer to [[where()]]
		 *                         on how to specify this parameter.
		 *
		 * @return static the query object itself.
		 * @see filterWhere()
		 * @see orFilterWhere()
		 */
		public function andFilterWhere( array $condition ) {
			$condition = $this->filterCondition( $condition );
			if ( $condition !== array() ) {
				$this->andWhere( $condition );
			}

			return $this;
		}

		/**
		 * Adds an additional WHERE condition to the existing one.
		 * The new condition and the existing one will be joined using the 'AND' operator.
		 *
		 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
		 *                                on how to specify this parameter.
		 * @param array        $params    the parameters (name => value) to be bound to the query.
		 *
		 * @return static the query object itself
		 * @see where()
		 * @see orWhere()
		 */
		public function andWhere( $condition, $params = array() ) {
			if ( $this->_query[ 'where' ] === null ) {
				$this->_query[ 'where' ] = $condition;
			} else {
				$this->_query[ 'where' ] = array( 'and', $this->_query[ 'where' ], $condition );
			}
			call_user_func_array( array( $this, 'add_param' ), $params );

			return $this;
		}

		/**
		 * Adds an additional WHERE condition to the existing one but ignores [[isEmpty()|empty operands]].
		 * The new condition and the existing one will be joined using the 'OR' operator.
		 *
		 * This method is similar to [[orWhere()]]. The main difference is that this method will
		 * remove [[isEmpty()|empty query operands]]. As a result, this method is best suited
		 * for building query conditions based on filter values entered by users.
		 *
		 * @param array $condition the new WHERE condition. Please refer to [[where()]]
		 *                         on how to specify this parameter.
		 *
		 * @return static the query object itself.
		 * @see filterWhere()
		 * @see andFilterWhere()
		 */
		public function orFilterWhere( array $condition ) {
			$condition = $this->filterCondition( $condition );
			if ( $condition !== array() ) {
				$this->orWhere( $condition );
			}

			return $this;
		}

		/**
		 * Adds an additional WHERE condition to the existing one.
		 * The new condition and the existing one will be joined using the 'OR' operator.
		 *
		 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
		 *                                on how to specify this parameter.
		 * @param array        $params    the parameters (name => value) to be bound to the query.
		 *
		 * @return static the query object itself
		 * @see where()
		 * @see andWhere()
		 */
		public function orWhere( $condition, $params = array() ) {
			if ( $this->_query[ 'where' ] === null ) {
				$this->_query[ 'where' ] = $condition;
			} else {
				$this->_query[ 'where' ] = array( 'or', $this->_query[ 'where' ], $condition );
			}
			call_user_func_array( array( $this, 'add_param' ), $params );

			return $this;
		}

		public function select( $columns, $option = null ) {

			$cols                     = func_get_args();
			$this->_query[ 'select' ] = $cols;

			return $this;

		}

		public function addSelect() {

			$cols = func_get_args();
			if ( empty( $this->_query[ 'select' ] ) ) {
				$this->_query[ 'select' ] = $cols;
			} else {
				$this->_query[ 'select' ] = array_merge( $this->_query[ 'select' ], $cols );
			}

			return $this;

		}

		public function from( $table ) {


			$tables = func_get_args();

			$this->_query[ 'from' ] = $tables;

			return $this;

		}

		/**
		 * Appends a JOIN part to the query.
		 * The first parameter specifies what type of join it is.
		 *
		 * @param string       $type  the type of join, such as INNER JOIN, LEFT JOIN.
		 * @param string|array $table the table to be joined.
		 *
		 * Use string to represent the name of the table to be joined.
		 * Table name can contain schema prefix (e.g. 'public.user') and/or table alias (e.g. 'user u').
		 * The method will automatically quote the table name unless it contains some parenthesis
		 * (which means the table is given as a sub-query or DB expression).
		 *
		 * Use array to represent joining with a sub-query. The array must contain only one element.
		 * The value must be a Phoenix_Framework_DB_Query_Builder object representing the sub-query while the
		 * corresponding key represents the alias for the sub-query.
		 *
		 * @param string|array $on    the join condition that should appear in the ON part.
		 *                            Please refer to [[where()]] on how to specify this parameter.
		 *
		 * @internal param array $params the parameters (name => value) to be bound to the query.
		 * @return Phoenix_Framework_DB_Query_Builder the query object itself
		 */
		public function join( $type, $table, $on = '' ) {
			$this->_query[ 'join' ][ ] = array( $type, $table, $on );

			return $this;
		}

		/**
		 * Appends an INNER JOIN part to the query.
		 *
		 * @param string|array $table the table to be joined.
		 *
		 * Use string to represent the name of the table to be joined.
		 * Table name can contain schema prefix (e.g. 'public.user') and/or table alias (e.g. 'user u').
		 * The method will automatically quote the table name unless it contains some parenthesis
		 * (which means the table is given as a sub-query or DB expression).
		 *
		 * Use array to represent joining with a sub-query. The array must contain only one element.
		 * The value must be a Phoenix_Framework_DB_Query_Builder object representing the sub-query while the
		 * corresponding key represents the alias for the sub-query.
		 *
		 * @param string|array $on    the join condition that should appear in the ON part.
		 *                            Please refer to [[where()]] on how to specify this parameter.
		 *
		 * @internal param array $params the parameters (name => value) to be bound to the query.
		 * @return Phoenix_Framework_DB_Query_Builder the query object itself
		 */
		public function inner_join( $table, $on = '' ) {
			$this->_query[ 'join' ][ ] = array( 'INNER JOIN', $table, $on );

			return $this;
		}

		/**
		 * Appends a LEFT OUTER JOIN part to the query.
		 *
		 * @param string|array $table the table to be joined.
		 *
		 * Use string to represent the name of the table to be joined.
		 * Table name can contain schema prefix (e.g. 'public.user') and/or table alias (e.g. 'user u').
		 * The method will automatically quote the table name unless it contains some parenthesis
		 * (which means the table is given as a sub-query or DB expression).
		 *
		 * Use array to represent joining with a sub-query. The array must contain only one element.
		 * The value must be a Phoenix_Framework_DB_Query_Builder object representing the sub-query while the
		 * corresponding key represents the alias for the sub-query.
		 *
		 * @param string|array $on    the join condition that should appear in the ON part.
		 *                            Please refer to [[where()]] on how to specify this parameter.
		 *
		 * @internal param array $params the parameters (name => value) to be bound to the query
		 * @return Phoenix_Framework_DB_Query_Builder the query object itself
		 */
		public function left_join( $table, $on = '' ) {
			$this->_query[ 'join' ][ ] = array( 'LEFT JOIN', $table, $on );

			return $this;
		}

		/**
		 * Appends a RIGHT OUTER JOIN part to the query.
		 *
		 * @param string|array $table the table to be joined.
		 *
		 * Use string to represent the name of the table to be joined.
		 * Table name can contain schema prefix (e.g. 'public.user') and/or table alias (e.g. 'user u').
		 * The method will automatically quote the table name unless it contains some parenthesis
		 * (which means the table is given as a sub-query or DB expression).
		 *
		 * Use array to represent joining with a sub-query. The array must contain only one element.
		 * The value must be a Phoenix_Framework_DB_Query_Builder object representing the sub-query while the
		 * corresponding key represents the alias for the sub-query.
		 *
		 * @param string|array $on    the join condition that should appear in the ON part.
		 *                            Please refer to [[where()]] on how to specify this parameter.
		 *
		 * @internal param array $params the parameters (name => value) to be bound to the query
		 * @return Phoenix_Framework_DB_Query_Builder the query object itself
		 */
		public function right_join( $table, $on = '' ) {
			$this->_query[ 'join' ] = array( 'RIGHT JOIN', $table, $on );

			return $this;
		}

		/**
		 * Sets the ORDER BY part of the query.
		 *
		 * @param string|array $columns the columns (and the directions) to be ordered by.
		 *                              Columns can be specified in either a string (e.g. `"id ASC, name DESC"`) or an
		 *                              array
		 *                              (e.g. `['id' => SORT_ASC, 'name' => SORT_DESC]`).
		 *                              The method will automatically quote the column names unless a column contains
		 *                              some parenthesis
		 *                              (which means the column contains a DB expression).
		 *                              Note that if your order-by is an expression containing commas, you should
		 *                              always use an array to represent the order-by information. Otherwise, the
		 *                              method will not be able to correctly determine the order-by columns.
		 *
		 * @return static the query object itself.
		 * @see add_order_by()
		 */
		public function order_by( $columns ) {
			$this->_query[ 'orderBy' ] = $this->normalizeOrderBy( $columns );

			return $this;
		}

		protected function normalizeOrderBy( $columns ) {
			if ( is_array( $columns ) ) {
				return $columns;
			} else {
				$columns = preg_split( '/\s*,\s*/', trim( $columns ), - 1, PREG_SPLIT_NO_EMPTY );
				$result  = array();
				foreach ( $columns as $column ) {
					if ( preg_match( '/^(.*?)\s+(asc|desc)$/i', $column, $matches ) ) {
						$result[ $matches[ 1 ] ] = strcasecmp( $matches[ 2 ], 'desc' ) ? SORT_ASC : SORT_DESC;
					} else {
						$result[ $column ] = SORT_ASC;
					}
				}

				return $result;
			}
		}

		/**
		 * Adds additional ORDER BY columns to the query.
		 *
		 * @param string|array $columns the columns (and the directions) to be ordered by.
		 *                              Columns can be specified in either a string (e.g. "id ASC, name DESC") or an
		 *                              array
		 *                              (e.g. `['id' => SORT_ASC, 'name' => SORT_DESC]`).
		 *                              The method will automatically quote the column names unless a column contains
		 *                              some parenthesis
		 *                              (which means the column contains a DB expression).
		 *
		 * @return static the query object itself.
		 * @see order_by()
		 */
		public function add_order_by( $columns ) {
			$columns = $this->normalizeOrderBy( $columns );
			if ( $this->_query[ 'orderBy' ] === null ) {
				$this->_query[ 'orderBy' ] = $columns;
			} else {
				$this->_query[ 'orderBy' ] = array_merge( $this->_query[ 'orderBy' ], $columns );
			}

			return $this;
		}

		/**
		 * Sets the LIMIT part of the query.
		 *
		 * @param integer $limit the limit. Use null or negative value to disable limit.
		 *
		 * @return static the query object itself.
		 */
		public function limit( $limit ) {
			$this->_query[ 'limit' ] = $limit;

			return $this;
		}

		/**
		 * Sets the OFFSET part of the query.
		 *
		 * @param integer $offset the offset. Use null or negative value to disable offset.
		 *
		 * @return static the query object itself.
		 */
		public function offset( $offset ) {
			$this->_query[ 'offset' ] = $offset;

			return $this;
		}

		public function index_by( $column ) {
			$this->_query[ 'indexBy' ] = $column;

			return $this;
		}

		public function all() {
			global $wpdb;
			$sql    = $this->build();
			$result = $this->wpdb_call(
				'get_results',
				array(
					$sql[ 0 ],
					'ARRAY_A'
				)
			);
			if ( $this->calc_found_rows ) {
				$this->total = $wpdb->get_var( "SELECT FOUND_ROWS()" );
			}

			return $result;
		}

		protected function wpdb_call( $method, $params ) {
			global $wpdb;
			if ( ! is_callable( array( $wpdb, $method ) ) ) {
				throw new Exception( 'Uncallable method!' );
			}
			$result  = call_user_func_array( array( $wpdb, $method ), $params );
			$indexBy = $this->get_query_param( $this, 'indexBy' );
			if ( is_array( $result ) && $indexBy && isset( $result[ 0 ][ $indexBy ] ) ) {
				$new_array = array();
				foreach ( $result as $i => $item ) {
					$new_array[ $item[ $indexBy ] ] = $item;
				}

				return $new_array;
			}

			return $result;
		}

		public function one() {
			$sql    = $this->build();
			$result = $this->wpdb_call(
				'get_row',
				array(
					$sql[ 0 ],
					'ARRAY_A'
				)
			);

			return $result;
		}
	}
