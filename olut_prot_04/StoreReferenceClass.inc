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
 *    Ź���軲�ȥ��饹 - StoreReferenceClass.inc
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once('OlutAppLib.php');

class StoreReference extends OlutApp
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;
    // �ޥ��������ɽ����
    var $master_display_lines;
    // �ޥ��������ȷ��
    var $store_code  = null;
    var $store_name = null;

    /**
     * class constructor
     */
    function StoreReference()
    {
        // instantiate the sql object
        $this->sql =& new StoreReference_SQL;

        // instantiate the template object
        $this->tpl =& new StoreReference_Smarty;

        //
        $this->master_display_lines = $_SESSION['screen_lines']*2;
    }


    /*
    *  �ᥤ�����
    *
    */
    function performReference()
    {
        if (isset($_REQUEST['item_letter'])){

            //
            // �֤��ԡפȤ�ʸ�������򤵤줿��
            //
            $letter = $_REQUEST['item_letter'];
            $this->displayForm($this->queryStore($letter));

        } else if (isset($_REQUEST['prev'])){

            // ����ɽ������Ƥ�����ƬŹ�ޤΥ����ɤ���Ƭʸ���ϥ��å�������¸����Ƥ��롣
            $this->displayForm($this->prevStore());


        } else if (isset($_REQUEST['next'])){

            // ����ɽ������Ƥ�����ƬŹ�ޤΥ����ɤ���Ƭʸ���ϥ��å�������¸����Ƥ��롣
            $this->displayForm($this->nextStore());

        } else if (isset($_REQUEST['select'])){

            $this->store_code =  $_REQUEST['store_code'];

            if(isset($this->store_code))
            {
                // ���򤵤�ޤ�����
                $this->store_name = $this->getStoreName($this->store_code);
            }
            else
            {
                unset($this->store_name);
            }
            unset($_SESSION['is_store_ref']);

        } else if (isset($_REQUEST['cancel'])){

            // ����󥻥뤵�줿�����å����ϥ��ꥢ��
            unset($this->store_code);
            unset($this->store_name);
            unset($_SESSION['is_store_ref']);
        }
        else
        {
            //
            // ���GET��
            //
            $this->displayForm($_POST);
        }
    }

    /*
    *   �ѥ�᡼���ǡ֤��ԡפȤ��Ϥ���롣�֥�������פ�Ÿ������SQL�䤤��碌��
    *
    */
    function queryStore($letter_from)
    {
        //
        $where = OlutApp::translateWhere($letter_from);
        $close_date = OlutApp::getCloseDate();

        $sql  = "select code,name from m_store";
        $sql .= " where $where and deleted is null ";
        $sql .= " and (close_date is null or close_date < '$close_date')";
        $sql .= " order by code limit $this->master_display_lines";

        if($this->sql->query($sql,SQL_ALL) == true)
        {
            // ���å�������¸��
            $_SESSION['letter_from'] = $letter_from;
            $_SESSION['offset'] = 0;       // ���ߤΥ��ե��åȤ���¸���롣
        }

        return $this->sql->record;
    }

    /*
    *   ���ܥ��������
    */
    function nextStore()
    {
        $offset = $_SESSION['offset'];
        $offset += $this->master_display_lines;
        $letter_from = $_SESSION['letter_from'];
        $where = OlutApp::translateWhere($letter_from);
        $close_date = OlutApp::getCloseDate();           // ��Ź�����ꡣ

        $sql  = "select code,name from m_store ";
        $sql .= " where $where and deleted is null ";
        $sql .= " and (close_date is null or close_date < '$close_date')";
        $sql .= " order by code offset $offset limit $this->master_display_lines";

        if($this->sql->query($sql,SQL_ALL,SQL_INDEX) == true)
        {
            // ���å�������¸��
            $_SESSION['letter_from'] = $letter_from;
            $_SESSION['offset'] = $offset;       // ���ߤΥ��ե��åȤ���¸���롣
        }
        return $this->sql->record;;
    }

    /*
    *   ���ܥ������
    */
    function prevStore()
    {
        $offset = $_SESSION['offset'];
        $offset -= $this->master_display_lines;
        if($offset < $master_display_lines)
        {
            $offset = 0;
        }
        $letter_from = $_SESSION['letter_from'];
        $where = OlutApp::translateWhere($letter_from);
        $close_date = OlutApp::getCloseDate();

        $sql = "select code,name from m_store ";
        $sql .= "where $where and deleted is null ";
        $sql .= " and (close_date is null or close_date < '$close_date')";
        $sql .= " order by code offset $offset limit $this->master_display_lines";
        if($this->sql->query($sql,SQL_ALL,SQL_INDEX)==true)
        {
            // ���å�������¸��
            $_SESSION['letter_from'] = $letter_from;
            $_SESSION['offset'] = $offset;       // ���ߤΥ��ե��åȤ���¸���롣
        }
        return $this->sql->record;
    }

    function getStoreName($code)
    {
        $sql = sprintf("select name from m_store where code='%s'",$code);
        if($this->sql->query($sql,SQL_ALL,SQL_INDEX)==true)
        {
            return $this->sql->record[0][0];
        }
    }

    /**
     * display the store entry form
     *
     * @param array $formvars the form variables
     */
    function displayForm($formvars = array()) {

        // assign the form vars
        $this->tpl->assign('post',$formvars);
        $this->tpl->assign('master_display_lines', $this->master_display_lines);

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }else{
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('StoreReferenceForm.tpl');
    }

}
?>
