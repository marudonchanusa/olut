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
 *    ������Ͽ - ArrivalOfGoodsModify.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

/*
 * ���ٽ�����Ͽ
 *
 * ���Υե����뤬���Ū�˥�����󥰤�����̤ϰʲ��ˤʤ�ޤ���
 *
 * 1. ������Ͽ����
 * 2. ���ʥޥ��������Ȳ���
 * 3. �����ޥ��������Ȳ���
 * 4. �����о��������
 *
 * �嵭���ڤ�ʬ���ϥ��å�����ѿ��ǹԤ��ޤ���
 *
 * $_SESSION['is_vendor_ref']      ==> ����軲���档�ʤ��Υե�����Ǻ�ɽ�����Ĥޤ�֤��ԡפȤ������סּ��פ������
 * $_SESSION['is_commodity_ref']   ==> �����軲���档
 *
 * �嵭�����åȤ���Ƥ��ʤ����ϡ�������Ͽ�פβ��̡�
 *
 */

session_start();

include('sys_define.inc');
include(OLUT_DIR . 'ArrivalOfGoodsClass.php');
include(OLUT_DIR . 'ArrivalOfGoodsSetup.php');
include(OLUT_DIR . 'VendorReferenceClass.inc');
include(OLUT_DIR . 'VendorReferenceSetup.inc');
include(OLUT_DIR . 'CommodityReferenceClass.inc');
include(OLUT_DIR . 'CommodityReferenceSetup.inc');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');

// ���󥹥���������
$arg =& new ArrivalOfGoods();
$arg->savePostedValues($_POST);      // POST�ǡ����򥻥å�������¸��
//

if(isset($_SESSION['is_vendor_ref']))
{
    // �����ޥ����������档
    $vendorRef =& new VendorReference;
    $vendorRef->performReference();

    // ����褬���ꤵ��Ƥ�����
    if(isset($vendorRef->vendor_code))
    {
        $line = $_SESSION['target_line'];
        $arg->vendor_code[$line] = $vendorRef->vendor_code;
        $arg->vendor_name[$line] = $vendorRef->vendor_name;
        $arg->displayForm($_POST);
    }

    if(isset($_POST['cancel'])){
        $arg->displayForm($_POST);
    }
}
else
if(isset($_SESSION['is_commodity_ref']))
{
    // ���ʥޥ����������档
    $commodityRef =& new CommodityReference;
    $commodityRef->performReference();


    // ���ʤλ��꤬���ꡣ
    if(isset($commodityRef->commodity_code))
    {
        $line = $_SESSION['target_line'];
        $arg->commodity_code[$line] = $commodityRef->commodity_code;
        $arg->commodity_name[$line] = $commodityRef->commodity_name;
        $arg->unit_price[$line]     = $commodityRef->commodity_unit_price;
        $arg->unit_name[$line]      = $commodityRef->commodity_unit_name;

        $arg->displayForm($_POST);
    }

    if(isset($_POST['cancel'])){
        $arg->displayForm($_POST);
    }
}
else
if( isset($_POST['check']) )
{
    // �������ޤ���
    $arg->checkEntry(&$_POST);
    $arg->displayForm($_POST);
}
else
if( isset($_POST['save']) )
{
    // ��¸��
    if($arg->checkEntry(&$_POST) == true)
    {
        if($arg->saveEntry($_POST)==true)
        {
            $arg->clearAllSession();
            $_POST = array();
            $_SESSION['modify_mode'] = false;
        }
    }
    $arg->displayForm($_POST);
}
else
if( isset($_POST['find']) )
{
    //
    // ���������������ϡ�
    //
    $arg->getFromTo($_POST);
    $arg->find();
    $arg->displaySelectionForm($_POST);
}
else
if( isset($_POST['modify']))
{
    // �����¹�
    if($arg->getTargetSlipNo($_POST))
    {
        if($arg->getModifyData())      // �����ǡ��������롣
        {
            // �����⡼�ɤ򥻥åȡ�
            $_SESSION['modify_mode'] = true;
            $arg->displayForm($_POST);
        }
        else
        {
            $arg->displaySelectionForm($_POST);

        }
    }
    else
    {
        $arg->displaySelectionForm($_POST);
    }
}
else
if( isset($_POST['delete']))
{
    if($arg->getTargetSlipNo($_POST))
    {
        if($arg->getModifyData())
        {
            $arg->delete();
            $arg->clearAllSession();
            $arg->displaySelectionForm($_POST);
        }
    }
}
else
{
    // ���ʤλ��ȡ�
    $line = $arg->isCommodityRef($_REQUEST);

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
        $line = $arg->isVendorRef($_REQUEST);

        if ($line != -1){
            //
            // ����軲�ȡ�
            //
            $vendorRef =& new VendorReference;
            $_SESSION['target_line'] = $line;
            $_SESSION['is_vendor_ref'] = true;
            $vendorRef->displayForm(null);
            //
        }
        else
        {
            // �ǽ��GET���줿��硣
            $arg->displaySelectionForm($_POST);
        }
    }
}

?>