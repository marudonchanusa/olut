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
 * ���Υץ������ϥե꡼���եȥ������Ǥ������ʤ��Ϥ���򡢥ե꡼���եȥ���
 * �����Ĥˤ�ä�ȯ�Ԥ��줿 GNU ���̸������ѵ��������(�С������2������
 * ˾�ˤ�äƤϤ���ʹߤΥС������Τ����ɤ줫)��������β��Ǻ�����
 * �ޤ��ϲ��Ѥ��뤳�Ȥ��Ǥ��ޤ���
 *
 * ���Υץ�������ͭ�ѤǤ��뤳�Ȥ��ä����ۤ���ޤ�����*������̵�ݾ�* 
 * �Ǥ������Ȳ�ǽ�����ݾڤ��������Ū�ؤ�Ŭ�����ϡ������˼����줿��Τ��
 * ������¸�ߤ��ޤ��󡣾ܤ�����GNU ���̸������ѵ���������������������
 *
 * ���ʤ��Ϥ��Υץ������ȶ��ˡ�GNU ���̸������ѵ���������ʣ��ʪ�����
 * ������ä��Ϥ��Ǥ����⤷������äƤ��ʤ���С��ե꡼���եȥ��������Ĥ�
 * �����ᤷ�Ƥ�������(����� the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)��
 *
 *   Program name:
 *    �����ƥ���� - sys_define.inc
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */
/*
 *
 * OLUT�����ƥ๽����������ޤ���
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