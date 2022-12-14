<?php
/**
 * Freeform Classic for ExpressionEngine
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2018, Solspace, Inc.
 * @link          https://solspace.com/expressionengine/freeform
 * @license       https://solspace.com/software/license-agreement
 */

if ( ! class_exists('Freeform_Model'))
{
	require_once 'freeform_model.php';
}

class Freeform_entry_model extends Freeform_Model
{
	protected	$fmh_table				= 'freeform_multipage_hashes';

	//intentional because we will need to set every incoming table
	public		$_table					= '';
	public		$inclusive				= TRUE;
	public		$include_columns		= TRUE;
	private		$form_ids				= array();

	public		$after_get				= array('add_form_data');

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access public
	 * @return object  class object instance
	 */

	public function __construct ()
	{
		parent::__construct();

		ee()->load->model('freeform_form_model');
	}

	// --------------------------------------------------------------------
	//	where's
	// --------------------------------------------------------------------


	// --------------------------------------------------------------------

	/**
	 * Set form id or ids before calling crud
	 * Setting this to _table helps the cacher work normally
	 *
	 * @access public
	 * @param  mixed  $form_ids single INT form_id or array of form_ids
	 * @return object           $this for method chaining
	 */

	public function id ($form_ids)
	{
		$ids = array();

		if (is_array($form_ids))
		{
			$this->_table = array();

			foreach ($form_ids as $id)
			{
				//only if its clean!
				if ($this->is_positive_intlike($id))
				{
					$this->_table[$id] = $this->table_name($id);
					$ids[] = $id;
				}
			}
		}
		else if ($this->is_positive_intlike($form_ids))
		{
			$this->_table = $this->table_name($form_ids);

			$ids[] = $form_ids;
		}

		$this->form_ids = $ids;

		return $this;
	}
	//END id


	// --------------------------------------------------------------------
	//	CRUD
	// --------------------------------------------------------------------

	// --------------------------------------------------------------------
	//	Due to the way building from multiple tables has to happen here,
	//	we have to have custom crud for everything that manually builds
	//	strings much in the same way CodeIgniter's ActiveRecord does.
	//	We can remove this when ActiveRecord for EE has been updated,
	//	as currently, you are not able to get the sql strings from AR
	//	inside of EE, but the public version of CI allows this.
	//
	//	Given that, all calls to AR like functions are stored and
	//	build_query() will put them all together and return a sql string.
	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * count
	 *
	 * @access public
	 * @param  mixed  	$where 		mixed. if single, id =>, if array, where =>
	 * @param  boolean 	$cleanup   	cleanup and deisolate?
	 * @return int         			count of all results
	 */

	public function count ($where = array(), $cleanup = TRUE)
	{
		// -------------------------------------
		//	validate
		// -------------------------------------

		if ( ! is_array($where))
		{
			//if its not an array and not an INT we cannot use it :p
			if ( ! $this->is_positive_intlike($where)){	return 0; }

			$where = array($this->id => $where);
		}

		// -------------------------------------
		//	wheres
		// -------------------------------------

		$where = $this->observe('before_count', $where);

		// -------------------------------------
		//	cache
		// -------------------------------------

		$stash = ($this->isolated) ? 'db_isolated_stash' : 'db_stash';

		//cacher already applies the tables for us, so we are good here
		$cache = $this->cacher(array(func_get_args(), $this->{$stash}, $where), __FUNCTION__);

		if ($cache->is_set())
		{
			if ($cleanup){ $this->reset()->deisolate();	}

			return $cache->get();
		}

		// -------------------------------------
		//	get
		// -------------------------------------

		$this->wheres($where);

		$sql = $this->build_query(TRUE);

		$query = $this->db->query($sql);
		$count = $query->row('count');

		if ($cleanup)
		{
			//cleanup
			$this->reset()->deisolate();
		}

		return $cache->set($count);
	}
	//END count


	// --------------------------------------------------------------------

	/**
	 * Count all results
	 *
	 * @access public
	 * @return int  	total count of all results in table
	 */

	public function count_all ($cleanup = TRUE)
	{
		//	cache
		//cacher already applies the tables for us, so we are good here
		$cache = $this->cacher(func_get_args(), __FUNCTION__);
		if ($cache->is_set()){ return $cache->get();}

		//we don't want any wheres
		//the local reset undones _table and we don't want that
		parent::reset();
		$sql = $this->build_query(TRUE);

		$query = $this->db->query($sql);
		$count = $query->row('count');

		if ($cleanup)
		{
			//cleanup
			$this->reset()->deisolate();
		}

		return $cache->set($count);
	}
	//END count_all


	// --------------------------------------------------------------------

	/**
	 * Get
	 *
	 * @access public
	 * @param  mixed  	$where 		mixed. if single, id =>, if array, where =>
	 * @param  boolean 	$cleanup   	cleanup and deisolate?
	 * @param  boolean 	$all 		return all results? (mainly used for)
	 * @return array    			array of data or array of rows if $all
	 */

	public function get ($where = array(), $cleanup = TRUE, $all = TRUE)
	{
		// -------------------------------------
		//	validate
		// -------------------------------------

		if ( ! is_array($where))
		{
			//if its not an array and not an INT we cannot use it :p
			if ( ! $this->is_positive_intlike($where)){	return FALSE; }

			$where = array($this->id => $where);
		}

		// -------------------------------------
		//	wheres
		// -------------------------------------

		$where = $this->observe('before_get', $where, $all);

		// -------------------------------------
		//	cache
		// -------------------------------------

		$stash = ($this->isolated) ? 'db_isolated_stash' : 'db_stash';

		//we are not caching here due to potential memory issues
		//and given that its more likely that entries wont be called
		//multiple times anyway, the memory performance is more important here

		/*$cache = $this->cacher(array(func_get_args(), $this->{$stash}, $where), __FUNCTION__);

		if ($cache->is_set())
		{
			if ($cleanup){ $this->reset()->deisolate();	}

			return $cache->get();
		}	*/

		// -------------------------------------
		//	get
		// -------------------------------------

		if ( ! $all)
		{
			$this->limit(1);
		}

		$this->wheres($where);

		$sql = $this->build_query();

		$query = $this->db->query($sql);

		//so at the bottom, getting the first result is also empty
		$rows = array(array());

		// -------------------------------------
		//	clean?
		// -------------------------------------

		if ($cleanup)
		{
			$this->reset()->deisolate();
		}

		// -------------------------------------
		//	cache and go
		// -------------------------------------

		if ($query->num_rows() > 0 )
		{
			$rows = $this->observe('after_get', $query->result_array(), $all);
			return ( ! $all) ? $rows[0] : $rows;
		}
		else
		{
			return FALSE;
		}
	}
	//END get


	// --------------------------------------------------------------------

	/**
	 * get_entry_data
	 *
	 * @access	public
	 * @param 	int 	entry id number
	 * @param 	int 	form id to check
	 * @return	mixed 	false if fail, array data if found
	 */

	public function get_entry_data ($entry_id, $form_id)
	{
		$return = FALSE;

		$query = $this->db->get_where(
			$this->table_name($form_id),
			array('entry_id' => $entry_id)
		);

		if ($query->num_rows() > 0)
		{
			$return = $query->row_array();
		}

		return $return;
	}
	//END get_entry_data


	// --------------------------------------------------------------------

	/**
	 * get_multipage
	 *
	 * @access	public
	 * @param 	int 	form_id number
	 * @param 	string 	hash id to check
	 * @return	mixed 	false if fail, array data if found
	 */

	public function get_multipage ($form_id, $hash)
	{
		//cache?
		$cache = $this->cacher(func_get_args(), __FUNCTION__);
		if ($cache->is_set()){ return $cache->get();}

		$return = FALSE;

		//int and hash?
		if ( ! $this->is_positive_intlike($form_id) OR strlen($hash) !== 32)
		{
			return $return;
		}

		$table_name = $this->table_name($form_id);

		$this->db->select(
			$table_name . '.*,' .
			$this->fmh_table . '.data as hash_stored_data'
		);
		$this->db->from($this->fmh_table);
		$this->db->join(
			$table_name,
			$table_name . '.entry_id = ' . $this->fmh_table . '.entry_id',
			'left'
		);
		$this->db->where($this->fmh_table . '.hash', $hash);

		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$return = $query->row_array();
		}

		return $cache->set($return);
	}
	//END get_multipage


	// --------------------------------------------------------------------
	// 	Utilities
	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * Adds form data to each row for parsing and conditionals
	 *
	 * @access public
	 * @param  array   $rows array of incoming row data
	 * @param  boolean $all  [description]
	 */

	public function add_form_data ($rows, $all = TRUE)
	{
		$forms_data = ee()->freeform_form_model->get(array(
			'form_id' => $this->form_ids
		));

		if ( ! $forms_data OR empty($forms_data)){ return $data; }

		$forms = array();

		foreach ($forms_data as $form)
		{
			$forms[$form['form_id']] = $form;
		}

		foreach ($rows as $row)
		{
			if (isset($rows['form_id']))
			{
				$row_form					= $forms[$row['form_id']];
				$row['form_name']			= $row_form['form_name'];
				$row['form_description']	= $row_form['form_description'];
			}
		}

		return $rows;
	}
	//END add_form_data


	// --------------------------------------------------------------------

	/**
	 * Inclusive search?
	 *
	 * @access public
	 * @param  boolean $inclusive
	 * @return object          	  $this for chaining
	 */

	public function inclusive_search ($inclusive = TRUE)
	{
		$this->inclusive = (bool) $inclusive;
		return $this;
	}
	//END inclusive_search


	// --------------------------------------------------------------------

	/**
	 * Preg Clean Array of strings for regex using forward slash.
	 * Mainly use for array map/walk since we support below PHP 5.3.
	 *
	 * @access	public
	 * @param	string	$value	incoming array value
	 * @return	string			preg_quoted string
	 */

	public function preg_clean_array($value)
	{
		return preg_quote($value, '/');
	}
	//END preg_clean_array


	// --------------------------------------------------------------------

	/**
	 * Builds multi table query
	 *
	 * Having multiple table output is VERY complicated, so this allows
	 * limited AR like calls but we have to manually parse so we can group
	 * them for each table and do the union
	 *
	 * Disclaimer: This is big and scary, but it works. - gf
	 *
	 * @access  public
	 * @param   boolean $count return the sql string instead of $this?
	 * @return 	mixed   returns sql string or false if it cannot be built
	 */

	public function build_query ($count = FALSE)
	{
		if (empty($this->form_ids))
		{
			return FALSE;
		}

		// -------------------------------------
		//	get form data
		// -------------------------------------

		$forms_data = ee()->freeform_form_model->get(array(
			'form_id' => $this->form_ids
		));

		if ( ! $forms_data OR empty($forms_data))
		{
			return FALSE;
		}

		// -------------------------------------
		//	group all field ids,
		//	and get missing for each form
		// -------------------------------------

		$all_field_ids = array();

		foreach ($forms_data as $form_data)
		{
			//this should always be true, but NEVER TRUST AN ELF
			if (is_array($form_data['field_ids']))
			{
				$all_field_ids = array_merge($all_field_ids, $form_data['field_ids']);
			}
		}

		$all_field_ids = array_unique($all_field_ids);

		//we want them in order so we can make blanks properly
		sort($all_field_ids);

		// -------------------------------------
		//	get field data
		// -------------------------------------

		ee()->load->model('freeform_field_model');

		$field_data = FALSE;

		if ( ! empty($all_field_ids))
		{
			$field_data = ee()->freeform_field_model
								->key('field_id')
								->where_in('field_id', $all_field_ids)
								->get();
		}

		$field_names 	= array();
		$column_names 	= array();

		if ($field_data)
		{
			// -------------------------------------
			//	lets make sure there are no fields presented
			//	that don't exist
			// -------------------------------------
			//
			$all_field_ids = array();

			foreach ($field_data as $row)
			{
				$all_field_ids[] = $row['field_id'];
				$field_names[$this->form_field_prefix . $row['field_id']] = $row['field_name'];
			}

			ksort($field_names);

			//so we can reverse search
			$column_names = array_flip($field_names);
		}

		sort($all_field_ids);

		// -------------------------------------
		//	vars
		// -------------------------------------

		$stash			= ($this->isolated) ? 'db_isolated_stash' : 'db_stash';
		$where			= '';
		$order_by		= array();
		$selects		= array();
		$distinct		= '';
		$limit			= 0;
		$offset			= 0;
		$and			= "\nAND ";
		$or				= "\nOR ";

		//this will prefix all columns for the entry tables so
		//sql for it doesn't get flagged as ambiguous
		$entry_prefix	= 'f.';

		//field_name finder regex
		$fn_find		= 	"/(?:\b|\s+)(" .
								implode("|", array_map(
									array($this, 'preg_clean_array'),
									array_values($field_names)
								)) .
							")\b/";

		//column_name finder regex
		$cn_find		= 	"/^(?:\s+)?(" .
								implode("|", array_map(
									array($this, 'preg_clean_array'),
									array_values($column_names)
								)) .
							")\b/";

		//for joining the member data table
		$author_find	= '/m\.member_id|m\.screen_name|m\.username/';
		$author_str		= "IF (m.screen_name = '', m.username, m.screen_name)";
		$author_select	= $author_str . ' as author';

		// -------------------------------------
		//	build where strings from stored stash
		// -------------------------------------

		foreach ($this->{$stash} as $call)
		{
			$method = $call[0];
			$args 	= $call[1];

			//this will not work for select because we need to
			//treat them differently
			if ( ! empty($field_names) AND $method != 'select')
			{
				// -------------------------------------
				//	fix arguments if they are the short_name
				//	instead of the column name
				//	makes "first_name >=" into "field_name_1 >="
				// -------------------------------------

				//if this is an array of wheres, lets adjust the key
				if (is_array($args[0]))
				{
					//to preserve array order
					//otherwise there is fruitless array searching for placement
					$new_key_array = array();

					foreach ($args[0] as $key => $value)
					{
						if (preg_match($fn_find, $key, $matches))
						{
							//fix the match
							$replace = str_replace(
								$matches[1],
								$column_names[$matches[1]],
								$matches[0]
							);

							//the replace the entire match
							$new_key = str_replace($matches[0], $replace, $key);
							$new_key_array[$new_key] = $value;
						}
						else
						{
							$new_key_array[$key] = $value;
						}
					}

					$args[0] = $new_key_array;
				}
				else
				{
					if (preg_match($fn_find, $args[0], $matches))
					{
						$sub_matches = array();
						$sub_store = array();

						//since this could be an raw sql statement
						//we need to protect values from being replaced
						//by this.
						//so if the field is form_field_3 and its result is
						//'email' and there is a field name 'email'.
						//(form_field_3 LIKE "%email%") would erronously
						//become (form_field_3 LIKE "%form_field_3%")
						//without this hash protection
						if (preg_match_all(
							'/([\'\"])([^\\1]*?)(\\1)/ims',
							$args[0],
							$sub_matches,
							PREG_SET_ORDER
						))
						{
							foreach ($sub_matches as $sub_match_set)
							{
								$hash = base64_encode($sub_match_set[0]);

								$sub_store[$hash] = $sub_match_set[0];

								$args[0] = str_replace($sub_store[$hash], $hash, $args[0]);
							}
						}

						$replace = str_replace($matches[1], $column_names[$matches[1]], $matches[0]);
						$args[0] = str_replace($matches[0], $replace, $args[0]);

						if ( ! empty($sub_store))
						{
							$args[0] = str_replace(
								array_keys($sub_store),
								array_values($sub_store),
								$args[0]
							);
						}
					}
				}
			}

			// -------------------------------------
			//	build stashed wheres
			//	we do these in order of stash instead
			//	of per method because AR would do the
			//	same thing
			// -------------------------------------

			if ($method == 'select')
			{
				//no selects on joining tables. Too hard for now
				if (is_array($args[0]))
				{
					$selects = array_merge($selects, $args[0]);
				}
				else
				{
					foreach ($args as $arg)
					{
						$selects = array_merge($selects, explode(',' , $arg));
					}
				}
			}
			//distinct?
			if ($method == 'distinct')
			{
				$distinct = 'DISTINCT';
			}
			else if ($method == '_group_like')
			{
				array_push($args, TRUE);
				$where .= $and . call_user_func_array(array($this, '_group_like'), $args);
			}
			//where
			else if ($method == 'where')
			{
				$where .= $and . implode($and, call_user_func_array(array($this, '_where'), $args));
			}
			//where
			else if ($method == 'or_where')
			{
				$where .= $or . implode($or, call_user_func_array(array($this, '_where'), $args));
			}
			//where_in
			else if ($method == 'where_in')
			{
				$where .= $and . call_user_func_array(array($this, '_where_in'), $args);
			}
			//where_in
			else if ($method == 'or_where_in')
			{
				$where .= $or . call_user_func_array(array($this, '_where_in'), $args);
			}
			//where_not_in
			else if ($method == 'where_not_in')
			{
				array_push($args, TRUE);
				$where .= $and . call_user_func_array(array($this, '_where_in'), $args);
			}
			//where_not_in
			else if ($method == 'or_where_not_in')
			{
				array_push($args, TRUE);
				$where .= $or . call_user_func_array(array($this, '_where_in'), $args);
			}
			//like
			else if ($method == 'like')
			{
				$where .= $and . implode($and, call_user_func_array(array($this, '_like'), $args));
			}
			//like
			else if ($method == 'or_like')
			{
				$where .= $or . implode($or, call_user_func_array(array($this, '_like'), $args));
			}
			//like
			else if ($method == 'not_like')
			{
				array_push($args, TRUE);
				$where .= $and . implode($and, call_user_func_array(array($this, '_like'), $args));
			}
			//like
			else if ($method == 'or_not_like')
			{
				array_push($args, TRUE);
				$where .= $or . implode($or, call_user_func_array(array($this, '_like'), $args));
			}
			//search
			else if ($method == 'add_search' AND $this->inclusive)
			{
				array_push($args, '', TRUE);
				$where .= $and . call_user_func_array(array($this, 'add_search'), $args);
			}
			else if ($method == 'add_search')
			{
				array_push($args, '', TRUE);
				$where .= $or . call_user_func_array(array($this, 'add_search'), $args);
			}
			else if ($method == 'order_by')
			{
				array_push($args, '', TRUE);
				$order_by[] = call_user_func_array(array($this, '_order_by'), $args);
			}
			else if ($method == 'limit')
			{
				if (isset($args[0]) AND $this->is_positive_intlike($args[0]))
				{
					$limit = $args[0];
				}
				if (isset($args[1]) AND $this->is_positive_intlike($args[1]))
				{
					$offset = $args[1];
				}
			}
		}
		//END foreach ($this->{$stash} as $call)

		// -------------------------------------
		//	fix where keyword
		// -------------------------------------

		if ( ! empty($where))
		{
			$where = "WHERE " . preg_replace('/^[WHERE|AND|OR]+[\s]*/', '', trim($where));
		}

		//searching on author needs to be replaced with an if
		//because otherwise we would need to use 'having' which is slower
		//and more difficult to use in this situation
		if (preg_match('/\bauthor\b/', $where))
		{
			$where = preg_replace(
				'/(\s+(`?)|(`?)\b)author(\2\s+|\b\3)/',
				' (' . $author_str . ') ',
				$where
			);
		}

		// -------------------------------------
		//	fix selects
		// -------------------------------------
		//	Of course, this screws any sub queries
		//	but uh.. yeah.
		// -------------------------------------

		if ( ! empty($selects))
		{
			$selects = preg_split(
				"/,(\s+)?/",
				implode(",", $selects),
				-1,
				PREG_SPLIT_NO_EMPTY
			);
		}

		// -------------------------------------
		//	Build unions
		// -------------------------------------

		$sqls = array();

		$default_fields = array_keys(
			ee()->freeform_form_model->default_form_table_columns
		);

		$starting_where = $where;

		foreach ($forms_data as $form)
		{
			//need to restore this each time
			//because we are affecting it with each loop
			//and each set is possibly different.
			$where = $starting_where;

			// -------------------------------------
			//	table name
			// -------------------------------------

			if (is_array($this->_table))
			{
				$table_name = $this->_table[$form['form_id']];
			}
			else
			{
				$table_name = $this->_table;
			}

			if (substr($table_name, 0, strlen($this->dbprefix)) != $this->dbprefix)
			{
				$table_name = $this->dbprefix . $table_name;
			}

			// -------------------------------------
			//	we need to find all fields in where
			//	clauses that don't exist in this form
			//	and replace them with NULL so the query
			//	will return the proper result for each
			//	form
			// -------------------------------------

			$where_replace = array();

			//this ensures all selects are in the same order
			foreach ($all_field_ids as $id)
			{
				$col_name = $this->form_field_prefix . $id;

				//missing field?
				if ( ! in_array($id, $form['field_ids']))
				{
					$where_replace[] = $col_name;
				}
			}

			// -------------------------------------
			//	selects
			// -------------------------------------

			//entire sql has to be wrapped for unions
			$form_sql = '(';

			if ($count)
			{
				$form_sql .= $this->_count_string . ' count' . "\n";
			}
			else if ( ! empty($field_names) AND ! empty($selects))
			{
				// -------------------------------------
				//	find the select fields we need
				// -------------------------------------
				// 	If this form doesn't have the columns
				// 	we are asking for we need to replace
				// 	them with NULL as form_field_1 etc
				// 	so the columns match on the unions
				// -------------------------------------

				$form_sql .= 'SELECT ' . $distinct . ' ' . "\n";

				$select_fields = array();

				foreach ($selects as $select)
				{
					//if this is a field column name, we need to see if this
					//form has that column, if not we need to replace with
					//a 'null as'
					if (preg_match($cn_find, $select, $matches))
					{
						$id = str_replace($this->form_field_prefix, '', $matches[1]);

						//missing field?
						if ( ! in_array($id, $form['field_ids']))
						{
							$select_fields[] = 'NULL as `' . $this->db->escape_str($matches[1]) . '`';
						}
						else
						{
							$select_fields[] = $select;
						}
					}
					//is this a regular field name?
					//we need to first match it to its real column name,
					//then test, etc etc
					else if (preg_match($fn_find, $select, $matches))
					{
						$id = str_replace(
							$this->form_field_prefix,
							'',
							$column_names[$matches[1]]
						);

						//missing field?
						if ( ! in_array($id, $form['field_ids']))
						{
							$select_fields[] = 'NULL as `' . $this->db->escape_str($matches[1]) . '`';
						}
						// if it is present, we still need to select
						// the column as field name
						else
						{
							$select_fields[] = $column_names[$matches[1]] .
												' as `' . $this->db->escape_str($matches[1]) . '`';
						}
					}
					//select author from member table items
					else if (trim($select) == 'author')
					{
						$select_fields[] = $author_select;
					}
					else
					{
						$select_fields[] = $select;
					}
				}
				//END foreach ($selects as $select)

				$form_sql .= implode(",\n", $select_fields) . ",\n";
			}
			//all fields
			else
			{
				$form_sql .= 'SELECT ' . $distinct . ' ' . "\n";

				// -------------------------------------
				//	default fields
				// -------------------------------------

				foreach ($default_fields as $field_name)
				{
					$form_sql .= $entry_prefix . $field_name . ",\n";
				}

				// -------------------------------------
				//	custom fields
				// -------------------------------------

				$select_fields = array();

				if ( ! empty($all_field_ids))
				{
					//this ensures all selects are in the same order
					foreach ($all_field_ids as $id)
					{
						$col_name = $this->form_field_prefix . $id;

						//missing field?
						if ( ! in_array($id, $form['field_ids']))
						{
							$select_fields[] = 'NULL as `' . $this->db->escape_str($col_name) . '`';
							$select_fields[] = 'NULL as `' . $this->db->escape_str($field_names[$col_name]) . '`';
						}
						//if we do have it, we need to select
						//column as fieldname as well
						else
						{
							$select_fields[] = $this->db->escape_str($col_name);
							$select_fields[] = $col_name .
												' as `' . $this->db->escape_str($field_names[$col_name]) . '`';
						}
					}

					$form_sql .= implode(",\n", $select_fields) . ",\n";
				}

				// -------------------------------------
				//	member data select
				// -------------------------------------

				$form_sql .= $author_select . ",\n";

			}
			//END if ! $count

			if ( ! $count)
			{
				// -------------------------------------
				//	form_id for identifying
				// -------------------------------------

				$form_sql .= $form['form_id'] . " as form_id\n";
			}

			// -------------------------------------
			//	from
			// -------------------------------------

			$form_sql .= "FROM " . $table_name;
			$form_sql .= " as " . str_replace('.', '', $entry_prefix) . "\n";

			// -------------------------------------
			//	author
			// -------------------------------------

			//lets only include this if we need it
			if (preg_match($author_find, $form_sql) OR
				preg_match($author_find, $where))
			{
				$form_sql .= "LEFT JOIN exp_members as m\n";
				$form_sql .= "ON " . $entry_prefix . "author_id = m.member_id\n";
			}

			// -------------------------------------
			//	where
			// -------------------------------------

			//some fields are not present in this form,
			//and we just have to select NULL
			if ( ! empty($where_replace))
			{
				$where = preg_replace(
					'/(\s+)((`?)(' . implode('|', $where_replace) . ')\\3\s+)/',
					' NULL ',
					$where
				);
			}

			// -------------------------------------
			//	fix missing fields in count clauses
			// -------------------------------------
			//	this replaces:
			//		WHERE missing_field LIKE '%search%'
			//	with
			//		WHERE '' LIKE '%search%'
			//	because you can't fire a where clause
			//	on a
			//		SELECT NULL AS missing_field
			//	and we want a blank string so searches
			//	return NOT LIKE and LIKE correctly
			//	as tables without said field
			//	would all need to return positive
			//	reactions to that result but
			//		NULL NOT LIKE '%thing%'
			//	always results in a negative where as
			//		'' NOT LIKE '%thing%'
			//	results positive
			// -------------------------------------

			foreach ($all_field_ids as $id)
			{
				$col_name = $this->form_field_prefix . $id;
				//missing field?

				if ( ! in_array($id, $form['field_ids']))
				{
					$where = preg_replace(
						"/\b" . $col_name . '\b/ms',
						"''",
						$where
					);
				}
			}

			// -------------------------------------
			//	add where
			// -------------------------------------

			$form_sql .= $where;

			// -------------------------------------
			//	end (orderby and sort go outside)
			// -------------------------------------

			$form_sql .= ")\n";

			// -------------------------------------
			//	default fields could be ambiguous
			//	so we need to prefix them
			// -------------------------------------
			//  This must not happen outside of the union
			//  setup, otherwise it fails
			// -------------------------------------

			$form_sql = preg_replace(
				//this looks insane, but it has to dance around items like
				// 'ip_address,entry_date' being right next to each other
				// so just '^' wont work. Cannot use word boundries alone,
				// or it will double prefix. So, complication...
				//
				// in order (numbered by capture parens):
				// 1. all beginning items
				// (not captured) start of string
				// 2. preceding space
				// 3. '`' backtick NOT following a period
				// (not captured) '\b' word boundry NOT following a period or backtick
				// 4. default fields we are looking for
				// 5. if a backtick was found, that +some space or word boundry
				'/(^|(\s+)|(?<!\.)(`)|(?<![\.`])\b)(' .
					implode('|', $default_fields) .
				')(\3\s+|\b)/m',
				//$2 put back any preceding space
				// the prefix
				//$3 backtick if found,
				//$4 matched wored
				//$5 possible ending
				'$2' . $entry_prefix . '$3$4$5',
				$form_sql
			);

			$sqls[] = $form_sql;
		}
		//END foreach ($forms_data as $form)

		//With multi-tables you have to add all counts and not UNION
		if ($count)
		{
			$sql = 'SELECT (' . implode("+\n", $sqls) . ') as count';
		}
		else
		{
			$sql = implode("UNION ALL\n", $sqls);
		}

		// -------------------------------------
		//	orderby
		// -------------------------------------

		if ( ! $count AND ! empty($order_by))
		{
			$sql .= ' ORDER BY ' . implode(', ', $order_by);
		}

		// -------------------------------------
		//	orderby
		// -------------------------------------

		if ( ! $count AND ($limit > 0 OR $offset > 0))
		{
			$sql .= ' LIMIT ' . $this->db->escape_str($offset) .
					', ' . $this->db->escape_str($limit);
		}

		return $sql;
	}
	//end build_query


	// --------------------------------------------------------------------

	/**
	 * Local pointer for freeform_form_model->table_name
	 *
	 * @access protected
	 * @param  int 		$form_id 	id of form to get entry table name for
	 * @return string          		name of entry table for the form id
	 */

	protected function table_name ($form_id)
	{
		return ee()->freeform_form_model->table_name($form_id);
	}
	//END table_name


	// --------------------------------------------------------------------

	/**
	 * Include columns in results?
	 *
	 * @access public
	 * @param  boolean $include_columns set flag to include columns in results
	 * @return object                   $this for chaining
	 */

	public function columns ($include_columns = TRUE)
	{
		$this->include_columns = (bool) $include_columns;

		return $this;
	}
	//END columns


	// --------------------------------------------------------------------

	/**
	 * Rest override so we can undo some special items for entries
	 *
	 * @access public
	 * @return object 	$this for chaining
	 */

	public function reset ()
	{
		$this->_table 			= '';
		$this->inclusive 		= TRUE;
		$this->include_columns 	= TRUE;

		parent::reset();

		return $this;
	}
	//END reset


	// --------------------------------------------------------------------

	/**
	 * Run stashed db calls
	 *
	 * prerunner for search stash
	 *
	 * @access protected
	 * @return object  		this for chaining
	 */

	/*protected function run_stash ($reset_after_call = FALSE)
	{
		$stash = ($this->isolated) ? 'db_isolated_stash' : 'db_stash';

		if ( ! empty($this->{$stash}['search']))
		{
			$search = $this->{$stash}['search'];

			if ($reset_after_call)
			{
				unset($this->{$stash}['search']);
			}

			foreach ($search as $args)
			{
				$this->add_search($args[0], $args[1], $this->_table);
			}
		}

		if ( ! empty($this->{$stash}['_group_like']))
		{
			$_group_like = $this->{$stash}['_group_like'];

			if ($reset_after_call)
			{
				unset($this->{$stash}['_group_like']);
			}

			foreach ($_group_like as $args)
			{
				$this->_group_like($args[0], $args[1]);
			}
		}

		return parent::run_stash($reset_after_call);
	}*/
	//END run_stash


	// --------------------------------------------------------------------

	/**
	 * Search
	 *
	 * adds a search to the query
	 *
	 * @access public
	 * @param  string $field field to search
	 * @param  string $where what to search on
	 * @return object        $this
	 */

	public function search ($field = '', $where = '')
	{
		$stash = ($this->isolated) ? 'db_isolated_stash' : 'db_stash';

		$this->{$stash}[] = array('add_search', array($field, $where, $this->_table));

		return $this;
	}
	//END search


	// --------------------------------------------------------------------

	/**
	 * Group like: calls _group_like when stash is run
	 *
	 * @access	public
	 * @param	string	$search			term to search on
	 * @param	array	$field_names	fields to search for said term
	 * @return	object					$this for chaining
	 */

	public function group_like ($search, $field_names)
	{
		$stash = ($this->isolated) ? 'db_isolated_stash' : 'db_stash';

		$this->{$stash}[] = array('_group_like', array($search, $field_names));

		return $this;
	}
	//END group_like


	// --------------------------------------------------------------------

	/**
	 * Group like: Have a series of fields to like against in a group () sql
	 *
	 * @access	public
	 * @param	string	$search			term to search on
	 * @param	array	$field_names	fields to search for said term
	 * @param	boolean	$return_sql		return sql string or run through AR?
	 * @return	mixed					string sql if return_sql,
	 *									or object $this for chaining
	 */

	public function _group_like ($search, $field_names, $return_sql = FALSE)
	{
		if ( ! is_array($field_names) AND is_string($field_names))
		{
			$field_names = array($field_names);
		}

		$where = '';

		if (is_array($field_names))
		{
			$first	= TRUE;
			$and	= "\nAND ";
			$or		= "\nOR ";
			$where	.= '(';

			//a LIKE or LIKE for every shown column
			foreach ($field_names as $name)
			{
				$joiner = $or;
				$start	= $or;

				if ($first)
				{
					$first	= FALSE;
					$start	= '';
					$joiner	= $and;
				}

				$where .= $start . implode(
					$joiner,
					call_user_func_array(
						array($this, '_like'),
						array($name, $search)
					)
				);
			}

			$where .= ')';
		}

		if ($return_sql)
		{
			return $where;
		}
		else
		{
			if ($where)
			{
				$this->where($where);
			}

			return $this;
		}
	}
	//END _group_like


	// --------------------------------------------------------------------

	/**
	 * add_search
	 *
	 * Because we need AND/OR groups here, we have to use non-standard AR :/
	 *
	 * @access	public
	 * @param 	string 	$field 			edit?
	 * @param 	string  $where 			where string from params
	 * @param 	string  $table 			name of table to prefix
	 * @param 	bool 	$sql_string 	return sql string or do active record
	 * @return	mixed 					if $sql_string, then sql, else $this
	 */

	public function add_search ($field = '', $where = '', $table = '', $sql_string = FALSE)
	{
		$field = trim($field);
		$where = trim($where);

		if ($field == '' OR $where == '')
		{
			return '';
		}

		//table prefix?
		if (trim($table) != '')
		{
			$table = (substr($table, 0, strlen($this->dbprefix)) != $this->dbprefix) ?
						$this->dbprefix . $table :
						$table ;

			$field = trim($table) . '.' . $field;
		}

		$sql = '';

		if (strncmp($where, '=', 1) ==  0)
		{
			//---------------------------------------
			// search:field_name="=wizard"
			//---------------------------------------

			$where = substr($where, 1);

			// special handling for IS_EMPTY
			if (strpos($where, 'IS_EMPTY') !== FALSE)
			{
				$where = str_replace('IS_EMPTY', '', $where);

				$add_search = $this->functions->sql_andor_string(
					$where,
					$field
				);

				// remove the first AND so we can can add parens
				$add_search = substr($add_search, 3);

				$add_search = $field . " IS NULL";

				$andor = ($add_search != '' AND
						 strncmp($where, 'not ', 4) != 0) ?
							'OR ' :
							'AND ';

				if (strncmp($where, 'not ', 4) == 0)
				{
					$sql = '(' . $add_search . ' ' .
									  $andor . $field . ' != "")';
				}
				else
				{
					$sql = '(' . $add_search . ' ' .
									  $andor . $field . ' = "")';
				}
			}
			else
			{
				$sql = $this->functions->sql_andor_string($where, $field);
			}
		}
		else
		{
			//---------------------------------------
			// search:field_name="wizard"
			//---------------------------------------

			if (strncmp($where, 'not ', 4) == 0)
			{
				$where = substr($where, 4);
				$like = ' NOT LIKE ';
			}
			else
			{
				$like = ' LIKE ';
			}

			if (strpos($where, '&&') !== FALSE)
			{
				$where = explode('&&', $where);
				$andor = (strncmp($like, 'NOT', 3) == 0) ? ' OR ' : ' AND ';
			}
			else
			{
				$where = explode('|', $where);
				$andor = (strncmp($like, 'NOT', 3) == 0) ? ' AND ' : ' OR ';
			}

			$sql .= '(';

			foreach ($where as $word)
			{
				if ($word == 'IS_EMPTY')
				{
					if (strpos($like, 'NOT') !== false) {
						$sql .= '(' . $field . ' ' . $like . ' "" AND ' . $field . ' IS NOT NULL) ' . $andor;
					} else {
						$sql .= '(' . $field . ' ' . $like . ' "" OR ' . $field . ' IS NULL) ' . $andor;
					}
				}
				elseif (strpos($word, '\W') !== FALSE)
				{
					$not = ($like == 'LIKE') ? ' ' : ' NOT ';

					// MySQL POSIX regex word boundary is [[:>:]]
					$word = '([[:<:]]|^)' .
								preg_quote(str_replace('\W', '', $word)) .
							'([[:>:]]|$)';

					$sql .= $field . $not .
								' REGEXP "' .
									$this->db->escape_str($word) .
								'" ' .
								$andor;
				}
				else
				{
					$sql .= $field . ' ' . $like .
								' "%' .
									$this->db->escape_like_str($word) .
								'%" ' .
								$andor;
				}
			}

			$sql = substr($sql, 0, -strlen($andor)).') ';
		}

		//remove first AND because AR will add its own
		$sql = preg_replace('/^[AND|OR]+[\s]*/', '', trim($sql));

		if ($sql_string)
		{
			return $sql;
		}
		else if ($sql != '')
		{
			$this->where($sql);
		}

		return $this;
	}
	//add_search


	// --------------------------------------------------------------------

	/**
	 * Date range searching on entries
	 * Must have at least one arg, but all are optional
	 *
	 * @access public
	 * @param  mixed $date_range       string name of type of date range
	 * @param  mixed $date_range_start start date range
	 * @param  mixed $date_range_end   end date range
	 * @return object                  $this for chaining
	 */

	public function date_where ($date_range = FALSE, $date_range_start = FALSE, $date_range_end = FALSE)
	{
		if ( ! empty($date_range_start) OR ! empty($date_range_end))
		{
			//for pagination vars
			$has_range = FALSE;

			if ($date_range_start)
			{
				$start = strtotime($date_range_start);

				if (ctype_digit((string) $start))
				{
					$this->where('entry_date >=', $start);
				}
			}

			if ($date_range_end)
			{
				$end = strtotime($date_range_end);

				if (ctype_digit((string) $end))
				{
					$this->where('entry_date <=', $end);
				}
			}
		}
		else if ( ! empty($date_range))
		{

			$since 	= '';
			$before = '';

			$date_range = strtolower(trim($date_range));

			if ($date_range == 'today')
			{
				$since	= mktime(0,0,0, date('m'), date('d'), date('Y'));
			}
			else if (preg_match('/^this([\s]+|_)week$/', $date_range))
			{
				$since 	= mktime(
					0,
					0,
					0,
					date('m'),
					date('d') - ((date('N') == 7) ? 0 : date('N')),
					date('Y')
				);
			}
			else if (preg_match('/^this([\s]+|_)month$/', $date_range))
			{
				$since 	= mktime(0,0,0, date('m'), 1, date('Y'));
			}
			else if (preg_match('/^last([\s]+|_)month$/', $date_range))
			{
				$since 	= mktime(0,0,0, date('m') - 1, 1, date('Y'));
				$before = mktime(0,0,0, date('m'), 1, date('Y'));
			}
			else if (preg_match('/^this([\s]+|_)year$/', $date_range))
			{
				$since = mktime(0,0,0, 1, 1, date('Y'));
			}

			if ($since)
			{
				$this->where('entry_date >', $since);
			}

			if ($before)
			{
				$this->where('entry_date <', $before);
			}
		}

		return $this;
	}
	//END date_where


	// --------------------------------------------------------------------
	//	Copied and modified AR functions because they are not
	//	public nor do they return the SQL instead of directly
	//	sending to the DB as a query or storing in private arrays.
	// --------------------------------------------------------------------
	//	This could be mitigated if the current version of CI in EE was up
	//	to date with the github version, but it appears to be an old fork
	//	and we sadly cannot trust what will and wont change so the only
	//	option was to copy and modify these functions locally as pulling
	//	in the newer AR driver from github just wouldn't work. :/ -gf
	// --------------------------------------------------------------------
	//	The below functions, even in thier slightly modified state,
	//	are derived from Codeigniter 2.0.1, which is Copyright (c)
	//	2008 - 2011, EllisLab, Inc., Now owned by B.C. Institute of Technology.
	//	The license of which can be found here:
	//	https://github.com/bcit-ci/CodeIgniter/blob/v2.1.0/license.txt
	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	array
	 */

	public function _where ($key, $value = NULL, $type = 'AND ', $escape = NULL)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		// If the escape value was not set will will base it on the global setting
		if ( ! is_bool($escape))
		{
			$escape = TRUE;
		}

		$where = array();

		foreach ($key as $k => $v)
		{
			if (is_null($v) && ! $this->_has_operator($k))
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}

			if ( ! is_null($v))
			{
				if ($escape === TRUE)
				{
					$k = $this->_protect_identifiers($k, FALSE, $escape);

					$v = ' '.$this->db->escape($v);
				}

				if ( ! $this->_has_operator($k))
				{
					$k .= ' = ';
				}
			}
			else
			{
				$k = $this->_protect_identifiers($k, FALSE, $escape);
			}

			$where[] = $k.$v;
		}

		return $where;
	}
	//END _where


	// --------------------------------------------------------------------

	/**
	 * Where_in
	 *
	 * @access	public
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @param	boolean	If the statement would be IN or NOT IN
	 * @return	string
	 */

	public function _where_in ($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ')
	{
		if ($key === NULL OR $values === NULL)
		{
			return;
		}

		if ( ! is_array($values))
		{
			$values = array($values);
		}

		$not = ($not) ? ' NOT' : '';

		$where_in = array();

		foreach ($values as $value)
		{
			$where_in[] = $this->db->escape($value);
		}

		$where_in = $this->_protect_identifiers($key) .
						$not . " IN (" . implode(", ", $where_in) . ") ";

		return $where_in;
	}
	//END _where_in


	// --------------------------------------------------------------------

	/**
	 * Like
	 *
	 * Called by like() or orlike()
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */

	function _like ($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}

		$like_statements = array();

		foreach ($field as $k => $v)
		{
			$k = $this->_protect_identifiers($k);

			$v = $this->db->escape_like_str($v);

			if ($side == 'before')
			{
				$like_statement = " $k $not LIKE '%{$v}'";
			}
			elseif ($side == 'after')
			{
				$like_statement = " $k $not LIKE '{$v}%'";
			}
			else
			{
				$like_statement = " $k $not LIKE '%{$v}%'";
			}

			// some platforms require an escape sequence
			// definition for LIKE wildcards
			if ($this->_like_escape_str != '')
			{
				$like_statement .= sprintf(
					$this->_like_escape_str,
					$this->_like_escape_chr
				);
			}

			$like_statements[] = $like_statement;
		}

		return $like_statements;
	}
	//END _like


	// --------------------------------------------------------------------

	/**
	 * Sets the ORDER BY value
	 *
	 * @access	public
	 * @param	string
	 * @param	string	direction: asc or desc
	 * @return	object
	 */

	public function _order_by ($orderby, $direction = '')
	{
		if (strtolower($direction) == 'random')
		{
			$orderby = ''; // Random results want or don't need a field name
			$direction = $this->random_keyword;
		}
		elseif (trim($direction) != '')
		{
			$direction = (in_array(
				strtoupper(trim($direction)),
				array('ASC', 'DESC'),
				TRUE
			)) ? ' ' . $direction : ' ASC';
		}

		if (strpos($orderby, ',') !== FALSE)
		{
			$temp = array();

			foreach (explode(',', $orderby) as $part)
			{
				$temp[] = trim($part);
			}

			$orderby = implode(', ', $temp);
		}
		else if ($direction != $this->_random_keyword)
		{
			$orderby = $this->_protect_identifiers($orderby);
		}

		$orderby_statement = $orderby.$direction;

		return $orderby_statement;
	}
	//END _order_by


	// --------------------------------------------------------------------

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Active Record class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it.  Some logic is necessary in order to deal with
	 * column names that include the path.  Consider a query like this:
	 *
	 * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @param	bool
	 * @return	string
	 */

	public function _protect_identifiers ($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE)
	{
		if ( ! is_bool($protect_identifiers))
		{
			$protect_identifiers = $this->_protect_identifiers;
		}

		if (is_array($item))
		{
			$escaped_array = array();

			foreach($item as $k => $v)
			{
				$escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v);
			}

			return $escaped_array;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/[\t ]+/', ' ', $item);

		// If the item has an alias declaration we remove it and set it aside.
		// Basically we remove everything to the right of the first space
		$alias = '';
		if (strpos($item, ' ') !== FALSE)
		{
			$alias = strstr($item, " ");
			$item = substr($item, 0, - strlen($alias));
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix.  There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		if (strpos($item, '(') !== FALSE)
		{
			return $item.$alias;
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== FALSE)
		{
			$parts	= explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified?  If so,
			// we have nothing more to do other than escape the item
			if (in_array($parts[0], $this->ar_aliased_tables))
			{
				if ($protect_identifiers === TRUE)
				{
					foreach ($parts as $key => $val)
					{
						if ( ! in_array($val, $this->_reserved_identifiers))
						{
							$parts[$key] = $this->_escape_identifiers($val);
						}
					}

					$item = implode('.', $parts);
				}
				return $item.$alias;
			}

			// Is there a table prefix defined in the config file?  If not, no need to do anything
			if ($this->dbprefix != '')
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if (isset($parts[3]))
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif (isset($parts[2]))
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}

				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
				if ($field_exists == FALSE)
				{
					$i++;
				}

				// Verify table prefix and replace if necessary
				if ($this->swap_pre != '' && strncmp($parts[$i], $this->swap_pre, strlen($this->swap_pre)) === 0)
				{
					$parts[$i] = preg_replace("/^".$this->swap_pre."(\S+?)/", $this->dbprefix."\\1", $parts[$i]);
				}

				// We only add the table prefix if it does not already exist
				if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix)
				{
					$parts[$i] = $this->dbprefix.$parts[$i];
				}

				// Put the parts back together
				$item = implode('.', $parts);
			}

			if ($protect_identifiers === TRUE)
			{
				$item = $this->_escape_identifiers($item);
			}

			return $item.$alias;
		}

		// Is there a table prefix?  If not, no need to insert it
		if ($this->dbprefix != '')
		{
			// Verify table prefix and replace if necessary
			if ($this->swap_pre != '' && strncmp($item, $this->swap_pre, strlen($this->swap_pre)) === 0)
			{
				$item = preg_replace("/^".$this->swap_pre."(\S+?)/", $this->dbprefix."\\1", $item);
			}

			// Do we prefix an item with no segments?
			if ($prefix_single == TRUE AND substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix)
			{
				$item = $this->dbprefix.$item;
			}
		}

		if ($protect_identifiers === TRUE AND ! in_array($item, $this->_reserved_identifiers))
		{
			$item = $this->_escape_identifiers($item);
		}

		return $item.$alias;
	}
	//END _protect_indentifiers


	// --------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	public function _escape_identifiers ($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}

		foreach ($this->_reserved_identifiers as $id)
		{
			if (strpos($item, '.'.$id) !== FALSE)
			{
				$str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);

				// remove duplicates if the user already included the escape
				return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
			}
		}

		if (strpos($item, '.') !== FALSE)
		{
			$str = 	$this->_escape_char .
					str_replace(
						'.',
						$this->_escape_char . '.' . $this->_escape_char,
						$item
					) .
					$this->_escape_char;
		}
		else
		{
			$str = $this->_escape_char.$item.$this->_escape_char;
		}

		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
	}
	//END _escape_identifiers


	// --------------------------------------------------------------------

	/**
	 * Tests whether the string has an SQL operator
	 * utilized from ./codeigniter/system/database/DB_driver.php
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */

	public function _has_operator ($str)
	{
		$str = trim($str);
		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str))
		{
			return FALSE;
		}

		return TRUE;
	}
	//END _has_operator

	// --------------------------------------------------------------------
	//	End Codeigniter derived functions. See above for details.
	// --------------------------------------------------------------------
}
//END Freeform_entry_model
