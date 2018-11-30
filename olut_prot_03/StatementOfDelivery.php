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
 * ���Υץ����ϥե꡼���եȥ������Ǥ������ʤ��Ϥ���򡢥ե꡼���եȥ���
 * �����Ĥˤ�ä�ȯ�Ԥ��줿 GNU ���̸������ѵ��������(�С������2������
 * ˾�ˤ�äƤϤ���ʹߤΥС������Τ����ɤ줫)��������β��Ǻ�����
 * �ޤ��ϲ��Ѥ��뤳�Ȥ��Ǥ��ޤ���
 *
 * ���Υץ�����ͭ�ѤǤ��뤳�Ȥ��ä����ۤ���ޤ�����*������̵�ݾ�* 
 * �Ǥ������Ȳ�ǽ�����ݾڤ��������Ū�ؤ�Ŭ�����ϡ������˼����줿��Τ��
 * ������¸�ߤ��ޤ��󡣾ܤ�����GNU ���̸������ѵ���������������������
 *
 * ���ʤ��Ϥ��Υץ����ȶ��ˡ�GNU ���̸������ѵ���������ʣ��ʪ�����
 * ������ä��Ϥ��Ǥ����⤷������äƤ��ʤ���С��ե꡼���եȥ��������Ĥ�
 * �����ᤷ�Ƥ�������(����� the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)��
 *
 *   Program name:
 *    Ǽ�ʽ���� - StatementOfDelivery.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

/*
 *
 *   ���Υ���ȥ�ϥ�˥塼��Ǽ�ʽ�����פ���ƤФ�ޤ���
 *   (Ǽ�ʽ�ư����ǤϤʤ��Τ���ա�
 *
 *  �ݥ���ȡ���
 *      1. �в���Ͽ����Ͽ������ɼ�ֹ�򥻥å�������¸����Ƥ��롣
 *      2. �в���Ͽ�ν�λ������Ͽ������ɼ���٤Ƥ򣱤Ĥ�PDF�ե�����Ȥ��ư������롣
 *      3. ��Ĥ�PDF�Ȥ��ư�������Ȥ������Ȥϥѥ�᡼���Ȥ����Ϥ�����硢����
 *         ���ޤ��¿����URLĹ�����¤Ǥ��٤Ƥ����������Ǥ��ʤ����Ȥ�ͽ�ۤ���롣
 *      4. �ʤΤǡ������оݤȤʤ롢��ɼ�ֹ�ϥ��å�����Ϥ��ˤ��롣
 *      5. ���å����̾�ϸ����'STATEMENT_OF_DELIVERY' �Ȥ���ʸ������������ȤϤ�������ɼ�ֹ�ˤȤ��롣
 *
 *
 *   ����˽��פ�����ˤĤ��ơ�
 *
 *   session_start(); ����ȥإ�����cache: none; �ʤɤ��ɲä����Τ�PDF�ե����뤬
 *   �˲�����Ƥ���ȥ��顼���Фޤ������򤹤�ˤ�php.ini�ΰʲ�������򤷤ޤ���
 *   (default is nocache)
 *   session.cache_limiter = private
 *   �嵭��������ȥڡ���������å��夵�쥢�ץꥱ��������ư��櫓�狼��ʤ�
 *   ���֤Ȥʤ�ޤ��Τǡ����Υڡ�����set_cache_limiter('none') �Ȥ��뤳�Ȥˤ��ޤ�����
 *
 *   see this url:
 *    http://php.s3.to/man/function.session-cache-limiter.html
 *
 */

session_cache_limiter('none');
session_start();

include('sys_define.inc');
include(OLUT_DIR . "StatementOfDeliveryClass.php");
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{
    $obj =& new StatementOfDelivery;

    $codes = $_SESSION['STATEMENT_OF_DELIVERY'];
    if($codes != null)
    {
        $obj->printOut($codes);

        // ���ߤΥ桼����ID����¸
        $uid = $_SESSION['user_id'];
        $screen_lines = $_SESSION['screen_lines'];
        session_unset();
        // �����ꡣ
        $_SESSION['user_id'] = $uid;
        $_SESSION['screen_lines'] = $screen_lines;
        //
    }
    else
    {
        $obj->error = "�������٤��и˥ǡ����Ϥ���ޤ���";
        $obj->renderErrorScreen();
    }
}
?>