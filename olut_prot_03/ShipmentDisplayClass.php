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
 *    �в�ɽ����ǧ - ShipmentDisplayClass.php
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

// smarty configuration
class ShipmentDisplay_Smarty extends Smarty
{
    function ShipmentDisplay_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class ShipmentDisplay_SQL extends SQL
{
    function ShipmentDisplay_SQL()
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


class ShipmentDisplay extends OlutApp
{
    var $sql;
    var $tpl;
    var $month;
    var $year;
    var $date;
    var $commodity_codes;
    var $commodity_names;
    var $unit_names;
    var $unit_prices;
    var $amounts;
    var $total_prices;
    var $act_flags;
    var $act_flag_names;
    var $memos;
    var $slip_no;
    var $store_section_name;
    var $store_name;
    var $grand_total;


    function ShipmentDisplay()
    {
        $this->sql =& new ShipmentDisplay_SQL();
        $this->tpl =& new ShipmentDisplay_Smarty();
    }

    function renderScreen($formvars)
    {
        $this->tpl->assign('year', $this->year);
        $this->tpl->assign('month',$this->month);
        $this->tpl->assign('date', $this->date);
        $this->tpl->assign('slip_no',$this->slip_no);
        $this->tpl->assign('store_name',        $this->store_name);
        $this->tpl->assign('store_section_name',$this->store_section_name);
        
        $this->tpl->assign('commodity_code',$this->commodity_codes);
        $this->tpl->assign('commodity_name',$this->commodity_names);
        $this->tpl->assign('unit_price',    $this->unit_prices);
        $this->tpl->assign('unit_name',     $this->unit_names);
        $this->tpl->assign('amount',        $this->amounts);
        $this->tpl->assign('total_price',   $this->total_prices);
        $this->tpl->assign('act_flag',      $this->act_flag_names);
        $this->tpl->assign('memo',          $this->memo);
        $this->tpl->assign('grand_total',   $this->grand_total);


        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ShipmentDisplayForm.tpl');
    }

    function find($formvars)
    {
        // ��ɼ�ֹ�Ǹ���
        if(strlen($formvars['slip_no'])==0)
        {
            $this->error = '��ɼ�ֹ�����Ϥ��Ƥ�������';
            return false;
        }

        $this->slip_no = $formvars['slip_no'];
        
        $so = '';
        //
        // �����ΤϺ���ʬ�ʹߤˤ��뤫�� @later
        //
        $_query  = "select t.act_date,t.line_no,s.name,sd.name,t.com_code,c.name,u.name,";
        $_query .= " t.unit_price,-t.amount,-t.total_price,t.act_flag,t.memo ,t.slip_no";    // unit price��amount���դʤΤ�fix. 2005/9/28.
        $_query .= " from t_main t,m_commodity c,m_unit u, m_store s, m_store_division sd";

        if(isset($formvars['find']))
        {
            $_query .= " where slip_no=$this->slip_no";
        }
        else if(isset($formvars['prev'])){
            $_query .= " where slip_no<$this->slip_no";
            $so = 'desc';
        }
        else if(isset($formvars['next'])){
            $_query .= " where slip_no>$this->slip_no";
        }

        $_query .= " and c.code=t.com_code and c.unit_code=u.code and sd.code=t.store_sec_code ";
        $_query .= " and s.code=t.dest_code  ";
        $_query .= " order by t.slip_no $so, t.line_no asc limit 6";

        if(!$this->sql->query($_query,SQL_ALL))
        {
            print $_query;
            $this->error = '�ǡ��������Ĥ���ޤ���';
            return false;
        }

        if($this->sql->record == null)
        {
            $this->error = '�ǡ��������Ĥ���ޤ���';
            return false;
        }

        $is_first = true;
        $this->grand_total = 0;

        foreach($this->sql->record as $rec)
        {
            if($is_first)
            {
                $this->slip_no = $save_slip_no = $rec[12];
                
                $this->year = substr($rec[0],0,4);     // yyyy/mm/dd
                $this->month = substr($rec[0],5,2);    // 0123456789
                $this->date  = substr($rec[0],8,2);

                $this->store_name = $rec[2];
                $this->store_section_name = $rec[3];

                $is_first = false;
            }
            
            if($save_slip_no != $rec[12])
            {
                break;
            }
            
            // ���ʥ����ɤ�����
            $this->commodity_codes[] = $rec[4];
            $this->commodity_names[] = $rec[5];
            $this->unit_names[]      = $rec[6];
            $this->unit_prices[]     = number_format($rec[7],2);
            $this->amounts[]         = number_format($rec[8],2);
            $this->total_prices[]    = number_format($rec[9]);
            $this->act_flags[]       = $rec[10];
            $this->memos[]           = $rec[11];
            // $this->slip_no           = $rec[12];
            $this->grand_total += $rec[9];

        }

        // �ե饰̾���Ѵ���
        $this->convertActFlags();
        
        // �ե����ޥå�
        $this->grand_total = number_format($this->grand_total);

        return true;

    }

    function convertActFlags()
    {
        $names = array('5' => '�и�', '6' => '����ץ�и�', '7' => '�����и�');
        foreach($this->act_flags as $af)
        {
            $this->act_flag_names[] = $names[$af+0];  // needed +0, why??
        }
    }
}

?>
