<?php
/**
 * polygon
 * @author paperen<paperen@gmail.com>
 */

date_default_timezone_set('Asia/Shanghai');

// 环境
define('ENV', 'develop');

define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
if ( !@include( ROOT.'core/polygon.php' ) ) exit('polygon missing');

require 'vendor/autoload.php';

Polygon::instance()->run();