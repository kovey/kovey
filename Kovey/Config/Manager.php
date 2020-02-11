<?php
/**
 *
 * @description 配置管理
 *
 * @package     Config
 *
 * @time        Tue Sep 24 08:54:41 2019
 *
 * @class       vendor/Kovey/Config/Manager.php
 *
 * @author      kovey
 */
namespace Kovey\Config;

class Manager
{
	private static $keys;

	private static $values;

	private static $path;

	public static function init($path)
	{
		self::$path = $path;
		self::$keys = new \Swoole\Table(1024);
		self::$keys->column('k', \Swoole\Table::TYPE_STRING, 256);
		self::$keys->create();
		self::$values = new \Swoole\Table(1024);
		self::$values->column('v', \Swoole\Table::TYPE_STRING, 512);
		self::$values->create();
	}

	public static function parse()
	{
		$files = scandir(self::$path);
		foreach ($files as $file) 
		{
			if ($file === '.' || $file === '..') {
				continue;
			}

			$filePath = self::$path . '/' . $file;
			$content = file_get_contents($filePath);
			if (!$content) {
				continue;
			}

			self::writeIntoMemory(str_replace('.ini', '', $file), $content);
		}
	}

	private static function writeIntoMemory($file, $content)
	{
		$contents = explode("\n", $content);
		$areaKey = '';
		$areaKeys = array();
		foreach ($contents as $oneLine) {
			$oneLine = trim($oneLine);
			$first = substr($oneLine, 0, 1);
			if ($first === ';'
				|| $first === '#'
			) {
				continue;
			}

			// area begin
			if (preg_match('/\[/', $oneLine, $match)) {
				$areaKey = str_replace(array('[',']'), '', $oneLine);
				continue;
			}

			$info = explode('=', $oneLine);
			if (count($info) < 2) {
				continue;
			}

			$key = trim($info[0]);
			if ($key === '') {
				continue;
			}

			$val = self::getValue($info, '=', 1);
			$finalKey = $file . '.' . $areaKey . '.' . $key;
			self::$values->set(md5($finalKey), array('v' => $val));
			$areaKeys[] = $finalKey;
		}

		self::writeKeyIntoMemory('', $areaKeys);
	}

	private static function getValue($info, $split, $index)
	{
		$len = count($info);
		if ($len == $index + 1) {
			return trim($info[$index]);
		}

		$result = '';
		for ($i = $index; $i < $len - 1; $i ++) {
			$result .= $info[$i] . $split;
		}

		return trim($result . $info[$len - 1]);
	}

	private static function writeKeyIntoMemory($pref, $areaKeys)
	{
		$keys = array();
		$areaKey = '';
		foreach ($areaKeys as $subKey) {
			$info = explode('.', $subKey);
			if (count($info) < 2) {
				continue;
			}

			$areaKey = $info[0];
			$key = $info[1];
			if (!isset($keys[$key])) {
				$keys[$key] = array();
			}

			if (count($info) > 2) {
				$keys[$key][] = self::getValue($info, '.', 1);
			}
		}
		$areaKey = $pref === '' ? $areaKey : $pref . '.' . $areaKey;
		self::$keys->set(md5($areaKey), array('k' => serialize(array_keys($keys))));
		foreach ($keys as $key => $val) {
			if (count($val) > 0) {
				self::writeKeyIntoMemory($areaKey, $val);
			}
		}
	}

	public static function get($key)
	{
		$val = self::$values->get(md5($key));
		if ($val !== false) {
			return $val['v'];
		}

		$kitem = self::$keys->get(md5($key));
		if ($kitem === false) {
			return false;
		}

		$keys = unserialize($kitem['k']);
		$vals = array();
		foreach ($keys as $k) {
			$result = self::get($key . '.' . $k);
			if ($result === false) {
				continue;
			}

			$vals[$k] = $result;
		}

		return $vals;
	}
}
