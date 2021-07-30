<?php
/**
 * This file is part of the LynkCMS Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS Components
 * @subpackage ApplicationCompiler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\ApplicationCompiler;

/**
 * List of build events as constants.
 */
class BuildEvents {

	/**
	 * @var Sub-compiler event name.
	 */
	const SUB_COMPILERS = 'build.sub_compilers';

	/**
	 * @var Config event name.
	 */
	const CONFIG = 'build.config';

	/**
	 * @var File injection event name.
	 */
	const FILE_INJECTION = 'build.injection';

	/**
	 * @var Compile event name.
	 */
	const COMPILE = 'build.compile';
}