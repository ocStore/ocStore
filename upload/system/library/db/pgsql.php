<?php
namespace Opencart\System\Library\DB;
/**
 * Class PgSQL
 *
 * @package Opencart\System\Library\DB
 */
class PgSQL {
	/**
	 * @var bool|\PgSql\Connection
	 */
	private $connection = false;

	/**
	 * @var bool|\PgSql\Result
	 */
	private $last_result = false;

	/**
	 * Constructor
	 *
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param int    $port
	 */
	public function __construct(string $hostname, string $username, string $password, string $database, int $port = 0) {
		if (!$port) {
			$port = 5432;
		}

		try {
			$pg = pg_connect('host=' . $hostname . ' port=' . $port . ' user=' . $username . ' password=' . $password . ' dbname=' . $database . ' options=\'--client_encoding=UTF8\' ');
		} catch (\Exception $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname);
		}

		if (!$pg) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname);
		}

		$this->connection = $pg;

		$this->query("SET CLIENT_ENCODING TO 'UTF8'");
		$this->query("SET client_min_messages TO WARNING");

		// Sync PHP and DB time zones
		$this->query("SET timezone TO '" . $this->escape(date('P')) . "'");
	}

	/**
	 * Query
	 *
	 * @param string $sql
	 *
	 * @return bool|\stdClass
	 */
	public function query(string $sql): bool|\stdClass {
		// Free the previous result if it exists to save memory
		if ($this->last_result instanceof \PgSql\Result) {
			pg_free_result($this->last_result);
		}

		$resource = pg_query($this->connection, $sql);

		if ($resource) {
			$this->last_result = $resource;

			if (pg_num_fields($resource) > 0) {
				$data = [];

				while ($row = pg_fetch_assoc($resource)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->num_rows = pg_num_rows($resource);
				$result->row = $data[0] ?? [];
				$result->rows = $data;

				return $result;
			}

			return true;
		} else {
			throw new \Exception('Error: ' . pg_last_error($this->connection) . '<br />' . $sql);
		}
	}

	/**
	 * Escape
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function escape(string $value): string {
		return pg_escape_string($this->connection, $value);
	}

	/**
	 * Count Affected
	 *
	 * @return int
	 */
	public function countAffected(): int {
		if ($this->last_result !== false) {
			return pg_affected_rows($this->last_result);
		}

		return 0;
	}

	/**
	 * Get Last Id
	 *
	 * @return int
	 */
	public function getLastId(): int {
		$query = $this->query("SELECT LASTVAL() AS id");

		return (int)$query->row['id'];
	}

	/**
	 * Is Connected
	 *
	 * @return bool
	 */
	public function isConnected(): bool {
		return $this->connection instanceof \PgSql\Connection && pg_connection_status($this->connection) === PGSQL_CONNECTION_OK;
	}

	/**
	 * Destructor
	 *
	 * Closes the DB connection when this object is destroyed.
	 */
	public function __destruct() {
		if ($this->connection instanceof \PgSql\Connection) {
			pg_close($this->connection);
		}

		$this->connection = false;
	}
}
