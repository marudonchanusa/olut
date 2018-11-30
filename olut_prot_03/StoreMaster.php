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
 *    Ź�ޥޥ�������Ͽ�ᥤ�� - StoreMaster.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');
include(OLUT_DIR . 'StoreMasterClass.php');
include(OLUT_DIR . 'StoreMasterSetup.php');
require_once(OLUT_DIR . '/libs/OlutUtilLib.inc');
require_once(OLUT_DIR . 'Login.php');

$login = new Login();
if( $login->validateUser($_POST) == true)
{
    // create Vendor
    $store =& new Store;

    if (isset($_REQUEST['add'])){
        //
        // ������Ͽ�ܥ��󤬲����줿��硣
        //
        //  1. �ե�����򥯥ꥢ�ˤ��롣
        //  2. �祭��(�����ɡˤ����ϲ�ǽ�Ȥ��롣
        //

        $store->displayForm();

    } else if(isset($_REQUEST['save'])){

        //
        // ��¸�ܥ��󤬲����줿��硣
        //

        $store->mungeFormData($_POST);
        if($store->isValidForm($_POST))
        {
            if($store->saveEntry($_POST))
            {
                $store->clear();
                $store->displayForm();
            }
            else
            {
                $store->displayForm($_POST);
            }
        } else {
            //
            $store->displayForm($_POST);
        }

    } else if(isset($_REQUEST['reload'])){

        //
        // ����ɥܥ��󤬲����줿��
        //
        $store->displayForm($store->findEntryExact($_POST));

    } else if(isset($_REQUEST['search'])){

        //
        // �ե����౦��˥��������Ϥ��줿���˸������ƥե�����˥ǡ���ɽ�����롣
        //

        $store->displayForm($store->findEntry($_POST));

    } else if(isset($_REQUEST['next'])){

        //
        // �ե���������Ρּ�ɽ���ץܥ���
        //
        $store->displayForm($store->getNextEntry($_POST));

    } else if(isset($_REQUEST['prev'])){

        //
        // �ե���������Ρ���ɽ���ץܥ���
        //

        $store->displayForm($store->getPrevEntry($_POST));

        //
    } else if(isset($_REQUEST['delete'])){

        //
        // ����ܥ���
        //
        $store->deleteEntry($_POST);
        $store->displayForm();

    }else{

        //
        // �ե�����[GET]���˥֥�󥯥ե������ɽ��
        //
        //
        $store->displayForm();
    }
}


?>
