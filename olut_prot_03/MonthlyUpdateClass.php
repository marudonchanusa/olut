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
 *    ����� - MonthlyUpdateClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('OlutAppLib.php');


// database configuration
class MonthlyUpdate_SQL extends SQL
{
    function MonthlyUpdate_SQL()
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

// smarty configuration
class MonthlyUpdate_Smarty extends Smarty
{
    function MonthlyUpdate_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

/*
*  ��������饹
*/
class MonthlyUpdate extends OlutApp
{
    var $tpl;
    var $sql;
    var $error;
    
    var $new_year;
    var $new_month;

    function MonthlyUpdate()
    {
        $this->sql =& new MonthlyUpdate_SQL;
        $this->tpl =& new MonthlyUpdate_Smarty;
    }

    function performUpdates()
    {
        // �ޥ������β��ʤ����β��ʤǹ������롣
        $_query = "update m_commodity set current_unit_price=next_unit_price,updated=now() where deleted is null";
        if(!$this->sql->query($_query))
        {
            $this->error = $this->sql->error;
            return false;
        }

        // ���������ޥ������򹹿����롣
        
        OlutApp::getCurrentProcessDate($this->sql,&$year,&$month);
        $tmp_year = "$year/$month/01";
        $next_month = OlutApp::getNextMonth($tmp_year);
        
        $_query = "update m_calendar set process_target='$next_month', updated=now()";
        if(!$this->sql->query($_query))
        {
            $this->error = $this->sql->error;
            return false;
        }
        
        //
        $this->new_year  = substr($next_month,0,4);
        $this->new_month = substr($next_month,5,2);
        return true;
    }

    function renderScreen($formvars)
    {
        $this->tpl->assign('year', $this->new_year);
        $this->tpl->assign('month',$this->new_month);
        
        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('MonthlyUpdateForm.tpl');
    }
    
    function renderConfirmScreen($formvars)
    {
        $year = null;
        $month = null;
        
        OlutApp::getCurrentProcessDate($this->sql,&$year,&$month);
                
        $this->tpl->assign('year', $year);
        $this->tpl->assign('month',$month);
        
        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('MonthlyUpdateConfirmForm.tpl');       
    }
}


?>