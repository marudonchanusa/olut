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
 *    ���и˰�ư - StockTransfer.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

session_start();

include('sys_define.inc');
include(OLUT_DIR . 'CommodityReferenceClass.inc');
include(OLUT_DIR . 'CommodityReferenceSetup.inc');
include(OLUT_DIR . "StockTransferClass.php");

$obj =& new StockTransfer;
$obj->savePostedValues($_POST);

if(isset($_SESSION['is_commodity_ref']))
{
    // ���ʥޥ����������档
    $commodityRef =& new CommodityReference;
    $commodityRef->performReference();


    // ���ʤλ��꤬���ꡣ
    if(isset($commodityRef->commodity_code))
    {
        $line = $_SESSION['target_line'];
        $obj->commodity_code[$line] = $commodityRef->commodity_code;
        $obj->commodity_name[$line] = $commodityRef->commodity_name;
        $obj->unit_price[$line]     = $commodityRef->commodity_unit_price;
        $obj->unit_name[$line]      = $commodityRef->commodity_unit_name;

        $obj->renderScreen($_POST);
        $obj->saveToSession();
    }

    if(isset($_POST['cancel']))
    {
        $obj->renderScreen($_POST);
    }
}
else
if(isset($_POST['move']))
{
    $obj->move($_POST);
    $obj->clearAllSession();
    $obj->renderScreen($_POST);
}
else 
if( isset($_POST['check']) )
{
    // �������ޤ���
    $obj->checkEntry(&$_POST);
    $obj->renderScreen($_POST);
}
else
{
    // ���ʤλ��ȡ�
    $line = $obj->isCommodityRef($_REQUEST);

    if($line != -1)
    {
        //
        // ���ʻ��ȡ�
        //
        $commodityRef =& new CommodityReference;
        $_SESSION['target_line'] = $line;
        $_SESSION['is_commodity_ref'] = true;
        $commodityRef->displayForm(null);
        //
    }
    else
    {
        $obj->clearAllSession();
        $obj->parseFromTo();
        $obj->getWarehouseNames();
        $obj->saveToSession();
        $obj->renderScreen($_POST);

    }
}


?>