<?php

namespace IP2Location;

class Database
{

	public const VERSION = '9.7.3';
	public const FIELD_NOT_SUPPORTED = 'This parameter is unavailable in selected .BIN data file. Please upgrade data file.';
	public const FIELD_NOT_KNOWN = 'This parameter is inexistent. Please verify.';
	public const INVALID_IP_ADDRESS = 'Invalid IP address.';
	public const MAX_IPV4_RANGE = 4294967295;
	public const MAX_IPV6_RANGE = 340282366920938463463374607431768211455;
	public const COUNTRY_CODE = 1;
	public const COUNTRY_NAME = 2;
	public const COUNTRY = 101;
	public const ALL = 1001;
	public const EXCEPTION = 10000;
	public const EXCEPTION_DATABASE_FILE_NOT_FOUND = 10005;
	public const EXCEPTION_NO_CANDIDATES = 10007;
	public const EXCEPTION_FILE_OPEN_FAILED = 10008;
	public const EXCEPTION_NO_PATH = 10009;
	public const EXCEPTION_INVALID_BIN_DATABASE = 10010;
	public const FILE_IO = 100001;
	
	private $columns = [
		self::COUNTRY_CODE         => [8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,   8],
		self::COUNTRY_NAME         => [8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,  8,   8],
	];
	
	private $names = [
		self::COUNTRY_CODE         => 'countryCode',
		self::COUNTRY_NAME         => 'countryName',
	];
	
	private $buffer = [];
	private $floatSize = null;
	private $mode;
	private $resource = false;
	private $date;
	private $type;
	private $columnWidth = [];
	private $offset = [];
	private $ipCount = [];
	private $ipBase = [];
	private $indexBaseAddr = [];
	private $year;
	private $month;
	private $day;
	private $productCode;
	private $licenseCode;
	private $databaseSize;
	private $rawPositionsRow;
	private $apiKey;
	private $package;
	private $defaultFields = self::ALL;
		
	private $error='';
		
	public function __construct($file = null, $mode = self::FILE_IO, $defaultFields = self::ALL)
	{

		$realPath = $this->findFile($file);
		$fileSize = filesize($realPath);
		$this->mode = self::FILE_IO;
		// @codingStandardsIgnoreStart
		$this->resource = @fopen($realPath, 'r');
		// @codingStandardsIgnoreEnd
		if ($this->resource === false) {
			//throw new \Exception(__CLASS__ . ": Unable to open file '{$realPath}'.", self::EXCEPTION_FILE_OPEN_FAILED);
			$this->error='Unable to open file '.$realPath;
		}

		if ($this->floatSize === null) {
			$this->floatSize = \strlen(pack('f', M_PI));
		}

		$this->defaultFields = $defaultFields;
		$headers = $this->read(0, 512);
		$this->type = unpack('C', $headers, 0)[1] - 1;
		$this->columnWidth[4] = unpack('C', $headers, 1)[1] * 4;
		$this->columnWidth[6] = $this->columnWidth[4] + 12;
		$this->offset[4] = -4;
		$this->offset[6] = 8;
		$this->productCode = unpack('C', $headers, 29)[1];
		$this->databaseSize = unpack('C', $headers, 31)[1];
		$this->ipCount[4] = unpack('V', $headers, 5)[1];
		$this->ipBase[4] = unpack('V', $headers, 9)[1];
		$this->ipCount[6] = unpack('V', $headers, 13)[1];
		$this->ipBase[6] = unpack('V', $headers, 17)[1];
		$this->indexBaseAddr[4] = unpack('V', $headers, 21)[1];
		$this->indexBaseAddr[6] = unpack('V', $headers, 25)[1];
		if ($this->productCode == 0) {
			//throw new \Exception(__CLASS__ . ': Incorrect IP2Location BIN file format. Please make sure that you are using the latest IP2Location BIN file.', self::EXCEPTION_INVALID_BIN_DATABASE);
			$this->error='Incorrect IP2Location BIN file format';
		}
	}

	public function __destruct()
	{
		if ($this->resource !== false) {
			// @codingStandardsIgnoreStart
			fclose($this->resource);
			// @codingStandardsIgnoreEnd
			$this->resource = false;
		}
	}

	public function lookup($ip, $fields = null, $asNamed = true)
	{

		list($ipVersion, $ipNumber) = $this->ipVersionAndNumber($ip);
		if (!$ipVersion) {
			return false;
		}

		$pointer = $this->binSearch($ipVersion, $ipNumber);
		if (empty($pointer)) {
			return false;
		}

		if ($fields === null) {
			$fields = $this->defaultFields;
		}

		if ($ipVersion === 4) {
			$this->rawPositionsRow = $this->read($pointer - 1, $this->columnWidth[4] + 4);
		} elseif ($ipVersion === 6) {
			$this->rawPositionsRow = $this->read($pointer - 1, $this->columnWidth[6]);
		}

		$ifields = (array) $fields;
		if (\in_array(self::ALL, $ifields)) {
			$ifields[] = self::COUNTRY;
		}

		$afields = array_keys(array_flip($ifields));
		rsort($afields);
		$done = [
			self::COUNTRY_CODE                => false,
			self::COUNTRY_NAME                => false,
			self::COUNTRY                     => false,
		];
		$results = [];
		foreach ($afields as $afield) {
			switch ($afield) {

				case self::ALL:
					break;
				case self::COUNTRY:
					if (!$done[self::COUNTRY]) {
						list($results[self::COUNTRY_NAME], $results[self::COUNTRY_CODE]) = $this->readCountryNameAndCode($pointer);
						$done[self::COUNTRY] = true;
						$done[self::COUNTRY_CODE] = true;
						$done[self::COUNTRY_NAME] = true;
					}
					break;
				case self::COUNTRY_CODE:
					if (!$done[self::COUNTRY_CODE]) {
						$results[self::COUNTRY_CODE] = $this->readCountryNameAndCode($pointer)[1];
						$done[self::COUNTRY_CODE] = true;
					}
					break;
				case self::COUNTRY_NAME:
					if (!$done[self::COUNTRY_NAME]) {
						$results[self::COUNTRY_NAME] = $this->readCountryNameAndCode($pointer)[0];
						$done[self::COUNTRY_NAME] = true;
					}
					break;

				default:
					$results[$afield] = self::FIELD_NOT_KNOWN;
			}
		}

		if (\is_array($fields) || \count($results) > 1) {

			if ($asNamed) {

				$return = [];
				foreach ($results as $key => $val) {
					if (\array_key_exists($key, $this->names)) {
						$return[$this->names[$key]] = $val;
					} else {
						$return[$key] = $val;
					}
				}

				return $return;
			}

			return $results;
		}

		return array_values($results)[0];
	}

	private function findFile($file = null)
	{
		if ($file !== null) {

			$realPath = realpath($file);
			if ($realPath === false) {
				//throw new \Exception(__CLASS__ . ": Database file '{$file}' does not seem to exist.", self::EXCEPTION_DATABASE_FILE_NOT_FOUND);
				$this->error="Database file '{$file}' does not seem to exist.";
			}

			return $realPath;
		}

		$current = realpath(__DIR__);
		if ($current === false) {
		//	throw new \Exception(__CLASS__ . ': Cannot determine current path.', self::EXCEPTION_NO_PATH);
			$this->error='Cannot determine current path.';
		}

		foreach ($this->databases as $database) {
			$realPath = realpath("{$current}/{$database}.BIN");
			if ($realPath !== false) {
				return $realPath;
			}
		}

		//throw new \Exception(__CLASS__ . ': No candidate database files found.', self::EXCEPTION_NO_CANDIDATES);
		$this->error='No candidate database files found.';

	}

	private function wrap8($x)
	{
		return $x + ($x < 0 ? 256 : 0);
	}

	private function wrap32($x)
	{
		return $x + ($x < 0 ? 4294967296 : 0);
	}

	private function ipVersionAndNumber($ip)
	{
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$number = sprintf('%u', ip2long($ip));
	
			return [4, ($number == self::MAX_IPV4_RANGE) ? ($number - 1) : $number];
		} elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$result = 0;
			$ip = $this->expand($ip);
	
			// 6to4 Address - 2002::/16
			if (substr($ip, 0, 4) == '2002') {
				foreach (str_split(bin2hex(inet_pton($ip)), 8) as $word) {
					$result = bcadd(bcmul($result, '4294967296', 0), $this->wrap32(hexdec($word)), 0);
				}
	
				return [4, bcmod(bcdiv($result, bcpow(2, 80)), '4294967296')];
			}
	
			// Teredo Address - 2001:0::/32
			if (substr($ip, 0, 9) == '2001:0000' && str_replace(':', '', substr($ip, -9)) != '00000000') {
				return [4, ip2long(long2ip(~hexdec(str_replace(':', '', substr($ip, -9)))))];
			}
	
			foreach (str_split(bin2hex(inet_pton($ip)), 8) as $word) {
				$result = bcadd(bcmul($result, '4294967296', 0), $this->wrap32(hexdec($word)), 0);
			}
	
			// IPv4 address in IPv6
			if (bccomp($result, '281470681743360') >= 0 && bccomp($result, '281474976710655') <= 0) {
				return [4, bcsub($result, '281470681743360')];
			}
	
			return [6, $result];
		}
	
		// Invalid IP address, return false
		return [false, false];
	}

	private function ipBetween($version, $ip, $low, $high)
	{
		if ($version === 4) {

			if ($low <= $ip) {
				if ($ip < $high) {
					return 0;
				}

				return 1;
			}

			return -1;
		}

		if (bccomp($low, $ip, 0) <= 0) {
			if (bccomp($ip, $high, 0) <= -1) {
				return 0;
			}

			return 1;
		}

		return -1;
	}

	private function bcBin2Dec($data)
	{
		if (!$data) {
			return;
		}

		$parts = [
			unpack('V', substr($data, 12, 4)),
			unpack('V', substr($data, 8, 4)),
			unpack('V', substr($data, 4, 4)),
			unpack('V', substr($data, 0, 4)),
		];
		foreach ($parts as &$part) {
			if ($part[1] < 0) {
				$part[1] += 4294967296;
			}
		}

		$result = bcadd(bcadd(bcmul($parts[0][1], bcpow(4294967296, 3)), bcmul($parts[1][1], bcpow(4294967296, 2))), bcadd(bcmul($parts[2][1], 4294967296), $parts[3][1]));
		return $result;
	}

	private function expand($ipv6)
	{
		$hex = unpack('H*hex', inet_pton($ipv6));
		return substr(preg_replace('/([A-f0-9]{4})/', '$1:', $hex['hex']), 0, -1);
	}

	private function read($pos, $len)
	{
		// @codingStandardsIgnoreStart
		fseek($this->resource, $pos, SEEK_SET);
		return fread($this->resource, $len);
		// @codingStandardsIgnoreEnd
	}

	private function readString($pos, $additional = 0)
	{

		$newPosition = unpack('V', substr($this->rawPositionsRow, $pos, 4))[1] + $additional;
		return $this->read($newPosition + 1, $this->readByte($newPosition + 1));
	}

	private function readFloat($pos)
	{

		return unpack('f', substr($this->rawPositionsRow, $pos, $this->floatSize))[1];
	}

	private function readByte($pos)
	{
		return $this->wrap8(unpack('C', $this->read($pos - 1, 1))[1]);
	}

	private function readCountryNameAndCode($pointer)
	{
		if ($pointer === false) {
			$countryCode = self::INVALID_IP_ADDRESS;
			$countryName = self::INVALID_IP_ADDRESS;
		} elseif ($this->columns[self::COUNTRY_CODE][$this->type] === 0) {
			$countryCode = self::FIELD_NOT_SUPPORTED;
			$countryName = self::FIELD_NOT_SUPPORTED;
		} else {

			$countryCode = $this->readString($this->columns[self::COUNTRY_CODE][$this->type]);
			$countryName = $this->readString($this->columns[self::COUNTRY_NAME][$this->type], 3);
		}

		return [$countryName, $countryCode];
	}

	private function getIpBoundary($ipVersion, $position, $width)
	{

		$section = $this->read($position, 128);
		switch ($ipVersion) {
			case 4:
				return [unpack('V', substr($section, 0, 4))[1], unpack('V', substr($section, $width, 4))[1]];
			case 6:
				return [$this->bcBin2Dec(substr($section, 0, 16)), $this->bcBin2Dec(substr($section, $width, 16))];
		}

		return [false, false];
	}

	private function binSearch($version, $ipNumber, $cidr = false)
	{
		$base = $this->ipBase[$version];
		$offset = $this->offset[$version];
		$width = $this->columnWidth[$version];
		$high = $this->ipCount[$version];
		$low = 0;
		$indexBaseStart = $this->indexBaseAddr[$version];
		if ($indexBaseStart > 1) {
			$indexPos = 0;
			switch ($version) {
				case 4:
					$number = (int) ($ipNumber / 65536);
					$indexPos = $indexBaseStart + ($number << 3);
					break;
				case 6:
					$number = (int) (bcdiv($ipNumber, bcpow('2', '112')));
					$indexPos = $indexBaseStart + ($number << 3);
					break;
			}

			$section = $this->read($indexPos - 1, 8);
			$low = unpack('V', substr($section, 0, 4))[1];
			$high = unpack('V', substr($section, 4, 4))[1];
		}

		while ($low <= $high) {
			$mid = (int) ($low + (($high - $low) >> 1));
			$position = $base + $width * $mid - 1;
			list($ipStart, $ipEnd) = $this->getIpBoundary($version, $position, $width);
			switch ($this->ipBetween($version, $ipNumber, $ipStart, $ipEnd)) {
				case 0:
					return ($cidr) ? [$ipStart, $ipEnd] : $base + $offset + $mid * $width;
				case -1:
					$high = $mid - 1;
					break;
				case 1:
					$low = $mid + 1;
					break;
			}
		}

		return false;
	}
}