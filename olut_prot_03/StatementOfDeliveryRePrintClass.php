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
 *    Ǽ�ʽ�ư��� - StatementOfDeliveryRePrintClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF ���ܸ�Ķ�����
require_once('OlutAppLib.php');
require_once('StatementOfDeliveryClass.php');    // 

// smarty configuration
class StatementOfDeliveryRePrint_Smarty extends Smarty
{
    function StatementOfDeliveryRePrint_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class StatementOfDeliveryRePrint_SQL extends SQL
{
    function StatementOfDeliveryRePrint_SQL()
    {
        //
        // dbtype://user:pass@host/dbname
        //
        $dsn = OLUT_DSN;

        if ($this->connect($dsn) == false)
        {
            $this->error = "�ǡ����١�����³���顼(" + $dsn + ")";
        }
    }
}

class StatementOfDeliveryRePrint extends OlutApp
{
    var $tpl;
    var $sql;
    var $error;

    // ctor
    function StatementOfDeliveryRePrint()
    {
        $this->tpl =& new StatementOfDeliveryRePrint_Smarty();
        $this->sql =& new StatementOfDeliveryRePrint_SQL();
    }

    function renderScreen($formvars)
    {
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];
        
        if(strlen($code_from))
        {
            $this->tpl->assign('code_from',$code_from);
        }

        if(strlen($code_to))
        {
            $this->tpl->assign('code_to',$code_to);
        }
        
        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('StatementOfDeliveryRePrintForm.tpl');
    }

    function printOut($formvars)
    {
        //
        $code_from = $formvars['code_from'];
        $code_to   = $formvars['code_to'];

        //
        // �в٤Υ����ɥ����å��� TO ��̵���Ƥ��ɤ���
        //

        if(strlen($code_from) == 0)
        {
            $this->error = "�в٥����ɤ����Ϥ��Ƥ�������";
            return false;
        }
        
        if($code_from > $code_to)
        {
            $this->error = "�����ɤ��ϰϤ����������Ϥ��Ƥ�������";
            return false;
        }
        //
        // �в٤Υ����ɤ����롣
        //

        $_query  = "select distinct slip_no from t_main ";
        if( strlen($code_to) > 0)
        {
            // the range is from - to type.
            $_query .= " where slip_no >= '$code_from'";
            $_query .= " and slip_no <= '$code_to'";
        }
        else
        {
            $_query .= " where slip_no = '$code_from'";
        }
        $_query .= " and (act_flag='5' or act_flag='6' or act_flag='7')";
        $_query .= " order by slip_no";

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            // print $_query;
            $this->error = "�䤤��碌���顼";
            return false;
        }

        $i = 0;
        foreach($this->sql->record as $rec)
        {
            $codes[$i++] = $rec[0];
        }

        //
        // �������饹��������
        //
        $obj =& new StatementOfDelivery();
        return $obj->printOut($codes);
    }
}


?>