<?php
/**
 * @description 显示不同的地方
 *
 * @package Assert
 *
 * @author kovey
 *
 * @time 2019-10-17 10:11:33
 *
 * @file test/Test/Assert/Show.php
 *
 */
namespace Test\Assert;

class Show
{
    public static function showDiff(Array $excepts, Array $gives, Array $nots = array())
    {
        if (!isset($excepts[0]) || !is_array($excepts[0])) {
            $excepts = array($excepts);
        }
        if (!isset($gives[0]) || !is_array($gives[0])) {
            $gives = array($gives);
        }

        if (count($nots) > 0) {
            if (!isset($nots[0]) || !is_array($nots[0])) {
                $nots = array($nots);
            }
        }

        $ekeys = array_keys($excepts[0]);
        $gkeys = array_keys($gives[0]);
		$hcount = self::getHcount(count($ekeys));

        self::showHeader($ekeys, $hcount, 'Excepts');

        foreach ($excepts as $except) {
            self::showContent($except, $hcount);
        }

        self::showHeader($gkeys, $hcount, 'Gives', $ekeys);

        foreach ($gives as $key => $give) {
            self::showContent($give, $hcount, $nots[$key] ?? array());
        }
    }

	private static function getHcount($count)
	{
		$total = 120;
		$per = intval($total / $count);
		if ($per < 10) {
			return 10;
		}

		return $per;
	}

    private static function showContent(Array $excepts, $hcount, Array $not = array())
    {
        $bottom = '+';
        $content = '| ';
        foreach ($excepts as $header => $val) {
            $len = strlen($header) + $hcount;
            for ($i = 0; $i <= $len; $i ++) {
                $bottom .= '-';
            }

            if (isset($not[$header])) {
                $val = $not[$header];
            }

            $sub = $len - strlen($val);
            if ($sub >= 0) {
                $content .= $val;
                for ($i = 0; $i < $sub; $i ++) {
                    $content .= ' ';
                }
                $content .= '|';
                continue;
            }

            $content .= substr($val, 0, $len - 3) . '...|';
        }

        $bottom .= '+';

        echo "$content\n";
        echo "$bottom\n";
    }

    private static function showHeader(Array $headers, $hcount, $name = 'Excepts', Array $excepts = array())
    {
        $coutEx = count($excepts);

        echo "+-----$name:\n";
        $top = '+';
        $bottom = '+';
        $content = '| ';
        foreach ($headers as $key => $header) {
            $len = strlen($header) + $hcount;
            for ($i = 0; $i <= $len; $i++) {
                $top .= '-';
                $bottom .= '-';
            }

            if ($coutEx > 0) {
                if (!isset($excepts[$key]) || $excepts[$key] !== $header) {
                    $header = 'g:' . $header . ',e:' . ($excepts[$key] ?? '');
                }
            }

            $newLen = strlen($header);
            $sub = $newLen - $len;
            if ($sub <= 0) {
                for ($i = 0; $i < $hcount; $i ++) {
                    $header .= ' ';
                }
            } else {
                $header = substr($header, 0, $len - 3) . '...';
            }

            $content .= $header . '|';
        }

        $top .= '+';
        $bottom .= '+';
        echo "$top\n";
        echo "$content\n";
        echo "$bottom\n";
    }
}
