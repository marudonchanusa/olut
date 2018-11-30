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
*    ê�����ϥᥤ�� - InventoryEntry.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*
*/

session_start();

include('sys_define.inc');
include(OLUT_DIR . 'InventoryEntrySetup.php');
include(OLUT_DIR . 'InventoryEntryClass.php');
require_once(OLUT_DIR . 'CommodityReferenceClass.inc');
require_once(OLUT_DIR . 'CommodityReferenceSetup.inc');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{

    // ���󥹥���������
    $obj =& new InventoryEntry;

    if(isset($_SESSION['is_commodity_ref']))
    {
        // ���ʥޥ����������档
        $commodityRef =& new CommodityReference;
        $commodityRef->performReference();


        // ���ʤλ��꤬���ꡣ
        if(isset($commodityRef->commodity_code))
        {
            $obj->commodity_code = $commodityRef->commodity_code;
            $obj->commodity_name = $commodityRef->commodity_name;
            $obj->unit_price     = $commodityRef->commodity_unit_price;
            $obj->unit_name      = $commodityRef->commodity_unit_name;

            $obj->saveToSession();
            $obj->newInventoryMode();
            $obj->renderEntryForm($_POST);
        }

        if(isset($_POST['cancel'])){
            $obj->newInventoryMode();
            $obj->renderEntryForm($_POST);
        }
    }
    else
    if(isset($_POST['start']))
    {
        //
        // ê���оݤ����򤵤줿��
        //
        $obj->parseSelectionForm($_POST);
        $obj->referMasters();
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['save']))
    {
        // ��¸�ܥ���
        $obj->restoreFromSession();
        $obj->parseInventory($_POST);
        $obj->registerInventory();
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['prev']))
    {
        $obj->restoreFromSession();
        $obj->previous();
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['next']))
    {
        $obj->restoreFromSession();
        $obj->next();
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['search']))
    {
        $obj->restoreFromSession();
        $obj->setSearchArg($_POST);
        $obj->getInventoryList($_POST);
        $obj->saveToSession();
        $obj->renderEntryForm($_POST);
    }
    else
    if(isset($_POST['new_inventory']))
    {
        // �����ԥ⡼��
        $obj->clear();
        $obj->newInventoryMode();
        $obj->RenderEntryForm($_POST);
    }
    else
    if(isset($_POST['check_new_commodity']))
    {
        // �����ǳ�ǧ�ܥ��󤬲����줿��
        $obj->parseNewInventory($_POST);
        $obj->saveToSession();
        $obj->newInventoryMode();
        $obj->checkNewInventory($_POST);
        $obj->RenderEntryForm($_POST);
    }
    else
    if(isset($_POST['save_new_commodity']))
    {
        $obj->parseNewInventory($_POST);
        //
        if($obj->checkNewInventory($_POST)==true)
        {
            if($obj->saveNewInventory($_POST) == false )
            {
                $obj->newInventoryMode();
                $obj->renderEntryForm($_POST);
            }
            else
            {
                // ���ｪλ
                $obj->clear();
                $obj->renderEntryForm($_POST);
            }
        }
        else
        {
            $obj->renderEntryForm($_POST);
        }
    }
    else
    if(isset($_POST['commodity_ref']))
    {
        // ���ʻ��ȥ⡼�ɡ�
        //
        $commodityRef =& new CommodityReference;
        $_SESSION['is_commodity_ref'] = true;
        $commodityRef->displayForm(null);
    }
    else
    if(isset($_POST['calc']))
    {
        $obj->restoreFromSession();
        $obj->parseInventory($_POST);
        // �Ʒ׻���
        $obj->reCalc($_POST);
        $obj->renderEntryForm($_POST);
    }
    else
    {
        // ����GET����ê���оݤ����򤵤��롣
        $obj->renderSelectionForm($_POST);
    }
}


?>
