<?php

/**
 * Plugin Name: Hardening
 * Plugin URI: https://github.com/devidw/wp-hard
 * Description: Hardening WordPress.
 * Version: 1.0.0
 * Requires PHP: 8.0.0
 * Author: David Wolf
 * Author URI: https://david.wolf.gdn
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dw-hard
 */

declare(strict_types=1);

namespace Devidw\Hard;

use Devidw\Hard\Plugin;
use Devidw\Hard\MustUse;

defined('ABSPATH') or die('No script kiddies please!');

require_once(__DIR__ . '/vendor/autoload.php');

new Plugin();
new MustUse();
