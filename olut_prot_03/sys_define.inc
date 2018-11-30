<?php
/*
 * Created on 2005/06/26
 *
 * OLUTシステム構成を定義します。
 * 
 */
define('OLUT_DIR', '/var/www/html/olut/');
define('SMARTY_DIR', '/var/www/html/smarty/libs/');

define('OLUT_DSN','pgsql://olut:olut@localhost/olut');

define('FPDF_FONTPATH','/var/www/html/font/');
define('OLUT_B4_WIDTH', 257);
define('OLUT_B4_HEIGHT',364);
define('OLUT_A4_WIDTH', 210);
define('OLUT_A4_HEIGHT', 297);
define('OLUT_IMAGE_PATH','/var/www/html/olut/image/');  // path to NTC logos.
define('OLUT_SYSTEM_PROFILE','/var/www/html/olut/conf/OlutProfile.xml');

?>
