<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Session redis driver.
 *
 * $Id: Database.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Session_Redis_Driver implements Session_Driver {

	// Encryption
	protected $encrypt;

	public function __construct()
	{
		// Load configuration
		$this->config = Kohana::config('session');

		if ( ! empty($this->config['encryption']))
		{
			// Load encryption
			$this->encrypt = Encrypt::instance();
		}

		if ( ! extension_loaded('redis'))
			throw new Exception('The redis PHP extension must be loaded to use this driver.');

		$this->backend = new Redis;

		if (empty($this->config['server'])) throw new Cache_Exception('Define the "server" settings in your session config');

		$server = $this->config['server'];

		if (empty($server['host'])) throw new Cache_Exception('Missing Redis host');
		$method = !empty($server['persistent']) ? 'pconnect' : 'connect';

		if (empty($server['port']))
		{
			$this->backend->$method($server['host'], $server['port']);
		}
		else
		{
			$this->backend->$method($server['host']);
		}

		Kohana_Log::add('error', 'Session Redis Driver Initialized');
	}

	public function open($path, $name)
	{
Kohana_Log::add("error", "open session");
		return TRUE;
	}

	public function close()
	{
Kohana_Log::add("error", "close session");
		if (empty($this->config['server']['persistent']))
		{
			$this->backend->close();
		}

		return TRUE;
	}

	public function read($id)
	{
Kohana_Log::add("error", "read session for $id");
		$data = $this->backend->get($id);

		if (!strlen($data))
		{
			return '';
		}
Kohana_Log::add("error", 'sdata1: '.base64_decode($data));
#Kohana_Log::add("error", 'sdata2: '.print_r($_SESSION,1));
#session_decode(base64_decode($data));
#Kohana_Log::add("error", 'sdata3: '.print_r($_SESSION,1));

		return ($this->encrypt === NULL) ? base64_decode($data) : $this->encrypt->decode($data);
	}

	public function write($id, $data)
	{
Kohana_Log::add("error", "write session $id: ".print_r($data,1));
		if ( ! Session::$should_save)
			return TRUE;

		$this->backend->setEx(
			$id,
			ini_get('session.gc_maxlifetime'),
			($this->encrypt === NULL) ? base64_encode($data) : $this->encrypt->encode($data)
		);

		return TRUE;
	}

	public function destroy($id)
	{
Kohana_Log::add("error", "destroy: $id");
		$this->backend->del($id);

		return TRUE;
	}

	public function regenerate()
	{
		// Generate a new session id
		session_regenerate_id();
Kohana_Log::add("error", "regen ".session_id());

		// Return new session id
		return session_id();
	}

	public function gc($maxlifetime)
	{
		// Delete all expired sessions

		return TRUE;
	}

} // End Session Redis Driver
