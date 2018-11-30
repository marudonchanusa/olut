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
 *    �в���Ͽ - Shipment.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

/*
 *
 * ���Υե����뤬���Ū�˥�����󥰤�����̤ϰʲ��ˤʤ�ޤ���
 *
 * 1. ������Ͽ����
 * 2. ���ʥޥ��������Ȳ���
 * 3. �����ޥ��������Ȳ���
 *
 * �嵭���ڤ�ʬ���ϥ��å�����ѿ��ǹԤ��ޤ���
 *
 * $_SESSION['is_store_ref']      ==> Ź�޻����档�ʤ��Υե�����Ǻ�ɽ�����Ĥޤ�֤��ԡפȤ������סּ��פ������
 * $_SESSION['is_commodity_ref']   ==> �����軲���档
 *
 * �嵭�����åȤ���Ƥ��ʤ����ϡֽв���Ͽ�פβ��̡�
 *
 */
session_start();

include('sys_define.inc');
include(OLUT_DIR . 'ShipmentClass.php');
include(OLUT_DIR . 'ShipmentSetup.php');
include(OLUT_DIR . 'StoreReferenceClass.inc');
include(OLUT_DIR . 'StoreReferenceSetup.inc');
include(OLUT_DIR . 'CommodityReferenceClass.inc');
include(OLUT_DIR . 'CommodityReferenceSetup.inc');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{
    // ���󥹥���������
    $obj =& new Shipment();
    $obj->savePostedValues($_POST);      // POST�ǡ����򥻥å�������¸��
    //

    if(isset($_SESSION['is_store_ref']))
    {
        // Ź�ޥޥ����������档
        $storeRef =& new StoreReference;
        $storeRef->performReference();

        // Ź�ޤ����ꤵ��Ƥ�����
        if(isset($storeRef->store_code))
        {
            $obj->store_code = $storeRef->store_code;
            $obj->store_name = $storeRef->store_name;
            $obj->displayForm($_POST);
        }

        if(isset($_POST['cancel'])){
            $obj->displayForm($_POST);
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
            $obj->commodity_code[$line] = $commodityRef->commodity_code;
            $obj->commodity_name[$line] = $commodityRef->commodity_name;
            $obj->unit_price[$line]     = $commodityRef->commodity_unit_price;
            $obj->unit_name[$line]      = $commodityRef->commodity_unit_name;

            $obj->displayForm($_POST);
        }

        if(isset($_POST['cancel'])){
            $obj->displayForm($_POST);
        }
    }
    else
    if( isset($_POST['check'])  || ($_POST['blur_flag'] == '1'))
    {
        // �������ޤ���
        $obj->checkEntry(&$_POST);
        $obj->displayForm($_POST);
    }
    else 
    if( isset($_POST['store_check']) )
    {
        $obj->referStore(&$_POST);
        $obj->displayForm($_POST);        
    }
    else
    if( isset($_POST['save']) )
    {
        // ��¸��
        if($obj->checkEntry(&$_POST) == true)
        {
            if($obj->addNewEntry($_POST)==true)
            {
                //
                //    Ǽ�ʽ�����Τ���˥��å�������ɼ�ֹ���ɲä�����¸
                //
                $tmp = $_SESSION['STATEMENT_OF_DELIVERY'];
                $tmp[] = $obj->slip_no;
                $_SESSION['STATEMENT_OF_DELIVERY'] = $tmp;
                //

                $obj->clearAllSession();
                $_POST = array();
            }
        }
        $obj->displayForm($_POST);
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
            if($obj->isStoreRef($_REQUEST)==true)
            {
                //
                // ����軲�ȡ�
                //
                $storeRef =& new StoreReference;
                $_SESSION['is_store_ref'] = true;
                $storeRef->displayForm(null);
                //
            }
            else
            {
                // added clear. 2005/10/17
                $obj->clearAllSession();
                // �ǽ��GET���줿��硣
                if(!isset($_POST['slip_no']))
                {
                    $obj->getNewSlipNo();
                }
                $obj->displayForm($_POST);
            }
        }
    }
}

?>
