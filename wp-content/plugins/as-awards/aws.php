<?php
Namespace AWS\Entry;
/**Plugin Name: Ablestate 
 * Description: The Ablestate plugin helps you issue certificates, member's track performance and attendance.
 *Author: David Wampamba
 *Author URI: https//davidofug.com
 *Version: 0.0.3
*/
date_default_timezone_set('Africa/Kampala');

if(!defined('ABSPATH')) exit();

require_once 'inc/base.php';
require_once 'inc/columns.php';
require_once 'inc/shortcode.php';

$base = new \AWS\INC\Base();
$shortCode = new \AWS\INC\shortCode(); 
