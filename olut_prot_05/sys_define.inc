<?php
/*
 * Olut inventory management  system
 * Copyright (C) 2005 NTC all rights reserved.
 *
 *   http://www.newtokyo.co.jp/olut/
 *
 * Development and delpoyments by TechKnowledge inc.
 *   http://www.techknowldge.co.jp/olut.html
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * このプログラムはフリーソフトウェアです。あなたはこれを、フリーソフトウェ
 * ア財団によって発行された GNU 一般公衆利用許諾契約書(バージョン2か、希
 * 望によってはそれ以降のバージョンのうちどれか)の定める条件の下で再頒布
 * または改変することができます。
 *
 * このプログラムは有用であることを願って頒布されますが、*全くの無保証* 
 * です。商業可能性の保証や特定の目的への適合性は、言外に示されたものも含
 * め全く存在しません。詳しくはGNU 一般公衆利用許諾契約書をご覧ください。
 *
 * あなたはこのプログラムと共に、GNU 一般公衆利用許諾契約書の複製物を一部
 * 受け取ったはずです。もし受け取っていなければ、フリーソフトウェア財団ま
 * で請求してください(宛先は the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)。
 *
 *   Program name:
 *    システム定義 - sys_define.inc
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */
/*
 *
 * OLUTシステム構成を定義します。
 * 
 */

define('OLUT_DIR', 'c:/eclipse/workspace/olut/');
define('SMARTY_DIR', 'c:/ntc/Smarty-2.6.9/libs/');

define('OLUT_DSN','pgsql://olut:olut@localhost/olut');
//define('OLUT_DSN','pgsql://olut:olut@192.168.1.82/olut');

define('FPDF_FONTPATH','c:/ntc/fpdf153/font/');
define('OLUT_B4_WIDTH', 257);
define('OLUT_B4_HEIGHT',364);
define('OLUT_A4_WIDTH', 210);
define('OLUT_A4_HEIGHT', 297);
define('OLUT_IMAGE_PATH','c:/eclipse/workspace/olut/image/');  // path to NTC logos.
define('OLUT_SYSTEM_PROFILE','c:/eclipse/workspace/olut/OlutProfile.xml');

?>
