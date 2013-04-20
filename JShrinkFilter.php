<?php
/**
 * @package    SugiPHP
 * @subpackage Assets
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Assets;

use Assetic\Filter\FilterInterface;
use Assetic\Asset\AssetInterface;
use JShrink\Minifier;

/**
 * Filters assets through JShrink.
 *
 * All credit for the filter itself is mentioned in the file itself.
 */
class JShrinkFilter implements FilterInterface
{
	public function filterLoad(AssetInterface $asset)
	{

	}

	public function filterDump(AssetInterface $asset)
	{
		$asset->setContent(Minifier::minify($asset->getContent(), array("flaggedComments" => false)));
	}
}
