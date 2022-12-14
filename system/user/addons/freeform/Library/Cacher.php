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

namespace Solspace\Addons\Freeform\Library;

/*
Use like:

function thing($var)
{
	$cache = new Cacher(func_get_args(), __FUNCTION__, __CLASS__);
	if ($cache->is_set()){ return $cache->get(); }

	return $cache->set($var);
}

*/

class Cacher
{
	protected static $cache;
	protected $_cache;

	protected $func			= '';
	protected $args			= '';
	protected $data_group 	= '';

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * NOTE: $args MUST come first because func_get_args() only works as the
	 * first param in PHP 5.2.x, as any other argument to a function it fails
	 *
	 * @access public
	 * @param  array  $args        	array of inputted arguments
	 * @param  string $func        	function name
	 * @param  string $data_group 	prefix to separate class functions
	 */

	public function __construct($args, $func, $data_group = 'data')
	{
		$this->args				= md5(serialize($args));
		$this->func 			= $func;
		$this->data_group 		= strtolower($data_group);

		if ( ! isset(self::$cache))
		{
			self::$cache = array();
		}

		if ( ! isset(self::$cache[$this->data_group]))
		{
			self::$cache[$this->data_group] = array();
		}

		$this->_cache =& self::$cache[$this->data_group];
	}
	//END __construct


	// --------------------------------------------------------------------

	/**
	 * Clear
	 *
	 * Clear a data groups function, a data group or all cache data.
	 * sending no params clears all cache data.
	 *
	 * @static
	 * @access public
	 * @param  string $data_group data group to clear
	 * @param  string $func       function to clear
	 * @return null
	 */

	static public function clear ($data_group = '', $func = '')
	{
		$data_group = strtolower($data_group);
		if ($data_group !== '' AND $func !== '')
		{
			unset(self::$cache[$data_group][$func]);
		}
		else if ($data_group !== '')
		{
			unset(self::$cache[$data_group]);
		}
		else
		{
			self::$cache = array();
		}
	}
	//END set


	// --------------------------------------------------------------------

	/**
	 * Checks function name and arg hash against data group if its set
	 *
	 * @access public
	 * @return boolean is the current function with args cached?
	 */

	public function is_set ()
	{
		return isset($this->_cache[$this->func][$this->args]);
	}
	//END is_set


	// --------------------------------------------------------------------

	/**
	 * Gets cached data if set or returns false
	 *
	 * @access public
	 * @return mixed cached data or boolean FALSE
	 */

	public function get ()
	{
		return ($this->is_set()) ? $this->_cache[$this->func][$this->args] : FALSE;
	}
	//END get


	// --------------------------------------------------------------------

	/**
	 * Sets the data to the cache and returns it immediatly for inline setting
	 *
	 * @access public
	 * @param  mixed $var whatever data needs to be cached for the func+args
	 * @return mixed returns $var from the set data array
	 */

	public function set ($var)
	{
		$this->_cache[$this->func][$this->args] = $var;
		return $this->_cache[$this->func][$this->args];
	}
	//END set
}
//END Cacher
