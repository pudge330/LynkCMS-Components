<?php
/**
 * This file is part of the BGStudios Config Component.
 *
 * (c) Brandon Garcia <brandon@bgstudios.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package BGStudios PHP Components
 * @subpackage Config
 * @author Brandon Garcia <brandon@bgstudios.io>
 */

namespace BGStudios\Component\Config\Formatter;

use InvalidArgumentException;

/**
 * Ini formatter class used for generating ini configs from a PHP array.
 */
class IniFormatter {

    /**
     * Format an ini config from a PHP array.
     *
     * @param Array $array Array of config values.
     * @return string The ini config as a string.
     */
	public function formatArray(Array $array) {
		if (!is_array($array)) {
		    throw new InvalidArgumentException('IniFormatter::formatArray() Function argument must be an array.');
		}
		$isNumber = function($value) {
			return (is_numeric($value) && is_string($value) === false);
		};
		$data = Array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.($isNumber($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.($isNumber($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.($isNumber($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.($isNumber($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            $data[] = null;
        }
        return implode(PHP_EOL, $data).PHP_EOL;
	}
}