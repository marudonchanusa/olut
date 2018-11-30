<?php
/*
 * Olut inventory management system
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
 *    ���и˰�ư - StockTransferClass.php
 *
 *   Release History:
 *    2005/09/30  ver 1.00.00 Initial Release
 *    2005/10//19 ver 1.00.09 Set system date for new screen.
 *
 */

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB
require_once('mbfpdf.php');     // PDF ���ܸ�Ķ�����
require_once('OlutAppLib.php');

// smarty configuration
class StockTransfer_Smarty extends Smarty
{
    function StockTransfer_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

// database configuration
class StockTransfer_SQL extends SQL
{
    function StockTransfer_SQL()
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

/*
*      ���и˰�ư���饹��
*
*/

class StockTransfer extends OlutApp
{
    var $warehouse_from;
    var $warehouse_to;
    var $warehouse_name_from;
    var $warehouse_name_to;
    var $tpl;
    var $sql;
    var $commodity_code;
    var $commodity_name;
    var $amount;
    var $unit_name;
    var $unit_price;
    var $total_price;
    var $memo;
    var $year;
    var $month;
    var $date;
    var $slip_no;

    // ctor
    function StockTransfer()
    {
        $this->sql =& new StockTransfer_SQL;
        $this->tpl =& new StockTransfer_Smarty;

        $this->restoreFromSession();

    }

    /*
    *  �ѥ�᡼������from/to�����롣
    */

    function parseFromTo()
    {
        $parm = $_SERVER['QUERY_STRING'];

        if($parm != null)
        {
            preg_match('/from=(\d*)/',$parm,$matches);
            $this->warehouse_from = $matches[1];

            preg_match('/to=(\d*)/',$parm,$matches);
            $this->warehouse_to = $matches[1];
        }

    }

    /*
    *
    */

    function getWarehouseNames()
    {
        if(isset($this->warehouse_from))
        {
            $this->warehouse_name_from = $this->getWarehouseName($this->warehouse_from);
        }
        if(isset($this->warehouse_to))
        {
            $this->warehouse_name_to = $this->getWarehouseName($this->warehouse_to);
        }
    }

    /*
    *
    */

    function getWarehouseName($code)
    {
        $_query = "select name from m_warehouse where code='$code'";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            return $this->sql->record[0];
        }
        return null;
    }

    /*
    *  ����ɽ��
    */
    function renderScreen($formvars)
    {
        //
        $this->tpl->assign('year',$this->year);
        $this->tpl->assign('month', $this->month);
        $this->tpl->assign('date', $this->date);
        $this->tpl->assign('slip_no', $this->slip_no);

        $this->tpl->assign('warehouse_name_from',$this->warehouse_name_from);
        $this->tpl->assign('warehouse_name_to',  $this->warehouse_name_to);

        $this->tpl->assign('commodity_code',$this->commodity_code);
        $this->tpl->assign('commodity_name',$this->commodity_name);
        $this->tpl->assign('amount',$this->amount);
        $this->tpl->assign('unit_name', $this->unit_name);
        $this->tpl->assign('unit_price', $this->unit_price);
        $this->tpl->assign('total_price', $this->total_price);
        $this->tpl->assign('memo', $this->memo);

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('StockTransferForm.tpl');
    }

    /*
    *   ���˥ǡ�����ư����
    *   �Ҹ�2�쥳���ɤ��ɲä��롣
    */

    function move($formvars)
    {
        // ���������ѥ桼����ID
        $uid = $_SESSION['user_id'];

        // �����ȥ��ߥåȤ�ߤ�롣
        $this->sql->Autocommit(false);
        // �ȥ�󥶥�����󳫻�

        // ���դ���
        $dt = $formvars['year'] . '/' . $formvars['month'] . '/' . $formvars['date'];
        $line_no = 0;

        // �����쥳���ɥ롼��
        for($i=0; $i<10; $i++)
        {
            //
            $commodity_code = $formvars["commodity_code_$i"];

            if(strlen($commodity_code) > 0)
            {
                // ���
                //
                // ���и˶�ʬ�ϰ�ư�����Ȥ��롣
                // ��ʧ����ʬ�ϸ��⡧���Ȥ��롣
                // �����襳���ɤϡ��� 00000         ==>
                // ��ɼ�ֹ�ϡ���     00000000      ==>
                //

                $ship_sec_code  = $this->getShipSectionCode($commodity_code);
                $act_flag       = '4';
                $vendor_code    = '00000';
                $slip_no        = '0000000';
                $amount         = $formvars["amount_$i"];
                $unit_price     = $formvars["unit_price_$i"];
                $total_price    = $formvars["total_price_$i"];

                //
                // from �Ҹˤ���ޥ��ʥ��Υȥ�󥶥����������������ֹ�ϣ���
                //

                $_query  = "insert into t_main (";
                $_query .= " act_date,slip_no,line_no,";
                $_query .= " com_code,warehouse_code,orig_code,act_flag,";
                $_query .= " amount, unit_price, total_price, payment_flag,ship_sec_code, update_userid, updated) ";
                $_query .= " values ('$dt',$slip_no,$line_no,";
                $_query .= "'$commodity_code',";
                $_query .= "'$this->warehouse_from',";
                $_query .= "'$vendor_code',";
                $_query .= "'$act_flag',";
                $_query .= "-$amount,";
                $_query .= "$unit_price,";
                $_query .= "-$total_price,";
                $_query .= "'1',";
                $_query .= "'$ship_sec_code','$uid',now())";

                if($this->sql->query($_query)==false)
                {
                    // ���ߤΥ��顼����¸��
                    $this->error = $this->sql->error;

                    // ���顼�ʤΤ����ǡ�
                    $this->sql->Rollback();
                    // ���Υ����ȥ��ߥåȤ��᤹��
                    $this->sql->AutoCommit(true);
                    return false;
                }

                $line_no++;

                //
                // to  �Ҹˤ˥ȥ�󥶥������������� ���ֹ�ϣ���
                //

                $_query  = "insert into t_main (";
                $_query .= " act_date,slip_no,line_no,";
                $_query .= " com_code,warehouse_code,orig_code,act_flag,";
                $_query .= " amount, unit_price, total_price, payment_flag,ship_sec_code, update_userid, updated) ";
                $_query .= " values ('$dt',$slip_no,$line_no,";
                $_query .= "'$commodity_code',";
                $_query .= "'$this->warehouse_to',";
                $_query .= "'$vendor_code',";
                $_query .= "'$act_flag',";
                $_query .= "$amount,";
                $_query .= "$unit_price,";
                $_query .= "$total_price,";
                $_query .= "'1',";
                $_query .= "'$ship_sec_code','$uid',now())";

                if($this->sql->query($_query)==false)
                {
                    // ���ߤΥ��顼����¸��
                    $this->error = $this->sql->error;

                    // ���顼�ʤΤ����ǡ�
                    $this->sql->Rollback();
                    // ���Υ����ȥ��ߥåȤ��᤹��
                    $this->sql->AutoCommit(true);
                    return false;
                }

                $line_no++;
            }
        }

        // ���ߥåȤ��롣
        $this->sql->Commit();

        // ���Υ����ȥ��ߥåȤ��᤹��
        $this->sql->AutoCommit(true);
        return true;
    }

    /*
    *
    */
    function find($formvars)
    {
        $this->slip_no = $formvars['slip_no'];
        if($this->slip_no==null)
        {
            $this->error = "��ɼ�ֹ����ꤷ�Ƥ�������";
            return false;
        }

        $this->year  = $formvars['year'];
        $this->month = $formvars['month'];
        $this->date  = $formvars['date'];

        $dt = "$this->year/$this->month/$this->date";

        if(!strlen($this->year) || !strlen($this->month) || !strlen($this->date))
        {
            $this->error = "���դ�����ꤷ�Ƥ�������";
            return false;
        }

        $_query = "select t.com_code,c.name,t.amount,u.name,t.unit_price,t.total_price,t.memo";
        $_query .= " from t_main t, m_commodity c, m_unit u";
        $_query .= " where slip_no=$this->slip_no ";
        $_query .= " and act_date='$dt'";
        $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')";
        $_query .= " and c.code=t.com_code ";
        $_query .= " and c.unit_code=u.code ";
        $_query .= " and t.deleted is null";
        $_query .= " order by t.line_no";

        // print $_query;

        if($this->sql->query($_query,SQL_ALL)==false)
        {
            print $_query;
            $this->error = $this->sql->error;
            return false;
        }

        $i = 0;
        foreach($this->sql->record as $rec)
        {
            $this->commodity_code[$i] = $rec[0];
            $this->commodity_name[$i] = $rec[1];
            $this->amount[$i]         = number_format($rec[2]);
            $this->unit_name[$i]      = $rec[3];
            $this->unit_price[$i]     = number_format($rec[4]);
            $this->total_price[$i]    = number_format($rec[5]);
            $this->memo[$i]           = $rec[6];
            $i++;
        }

        if($i==0)
        {
            $this->error = "���ꤷ�����˥ǡ��������Ĥ���ޤ���";
            return false;
        }

        return true;

    }

    /*
    *  POST���줿�ͤ򥻥å�������¸���롣
    *  ñ���Ʊ���ե�����ʤ����¸����ɬ�פϤʤ�����
    *  ���ʥޥ��������Ȥʤɤ�$_POST������ͤ��ʤ��ʤ�Τǡ�
    *
    */
    function savePostedValues($formvars = array())
    {
        //
        // �إ����ˤ����͡� ��ɼ�ֹ�����դ��ʤɡ�
        //
        if(isset($formvars['year']))
        {
            $this->year = $formvars['year'];
        }
        if(isset($formvars['month']))
        {
            $this->month = $formvars['month'];
        }
        if(isset($formvars['date']))
        {
            $this->date = $formvars['date'];
        }

        //
        // ���ٹԤˤ����͡�
        //

        $do_save = false;

        for($i=0; $i<10; $i++)
        {
            $id = "amount_$i";
            if(isset($formvars[$id]))
            {
                //
                if($this->act_flag[$i] == '2') // �Ͱ���
                {
                    $this->amount[$i] = 0.0;
                }
                else
                {
                    $this->amount[$i] = $formvars[$id];
                }
                $do_save = true;
            }

            $id = "unit_price_$i";
            if(isset($formvars[$id]))
            {
                $this->unit_price[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "total_price_$i";
            if(isset($formvars[$id]))
            {
                $this->total_price[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "commodity_code_$i";
            if(isset($formvars[$id]))
            {
                $this->commodity_code[$i] = $formvars[$id];
                $do_save = true;
            }
        }

        if($do_save)
        {
            $this->saveToSession();
        }
    }

    /*
    *  ���å�������¸��
    */
    function saveToSession()
    {
        $_SESSION['YEAR']    = $this->year;
        $_SESSION['MONTH']   = $this->month;
        $_SESSION['DATE']    = $this->date;
        $_SESSION['SLIP_NO'] = $this->slip_no;
        $_SESSION['WAREHOUSE_FROM']        = $this->warehouse_from;
        $_SESSION['WAREHOUSE_TO']          = $this->warehouse_to;
        $_SESSION['WAREHOUSE_NAME_FROM']   = $this->warehouse_name_from;
        $_SESSION['WAREHOUSE_NAME_TO']     = $this->warehouse_name_to;

        $_SESSION['COMMODITY_CODE_ARRAY'] = $this->commodity_code;
        $_SESSION['COMMODITY_NAME_ARRAY'] = $this->commodity_name;
        $_SESSION['UNIT_NAME_ARRAY']      = $this->unit_name;
        $_SESSION['UNIT_PRICE_ARRAY']     = $this->unit_price;
        $_SESSION['TOTAL_PRICE_ARRAY']    = $this->total_price;
        $_SESSION['AMOUNT_ARRAY']         = $this->amount;
    }

    /*
    *  ���å���󥯥ꥢ����Ӥ��Υ��ԡ��ѿ��򥯥ꥢ��
    *  ==> ���������Ҹˤ�from / to �Ϥ��Τޤޡ�
    */

    function clearAllSession()
    {
        unset($_SESSION['YEAR']);
        unset($_SESSION['MONTH']);
        unset($_SESSION['DATE'] );
        unset($_SESSION['SLIP_NO']);
        
        $_SESSION['COMMODITY_CODE_ARRAY'] = array();
        $_SESSION['COMMODITY_NAME_ARRAY'] = array();
        $_SESSION['UNIT_NAME_ARRAY']      = array();
        $_SESSION['UNIT_PRICE_ARRAY']     = array();
        $_SESSION['TOTAL_PRICE_ARRAY']    = array();
        $_SESSION['AMOUNT_ARRAY']         = array();

        $this->commodity_name = array();
        $this->commodity_code = array();
        $this->unit_name      = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->payment_flag   = array();
        $this->act_flag       = array();
        $this->memo           = array();
        $this->amount         = array();
        $this->slip_no = null;
        
        // set system date. 2005/10/19.
        $this->year  = date('Y');
        $this->month = date('m');
        $this->date  = date('d');
    }

    /*
    *  ���å���󤫤������ѿ����᤹��
    */
    function restoreFromSession()
    {
        // restore dates
        $this->year  = $_SESSION['YEAR'];
        $this->month = $_SESSION['MONTH'];
        $this->date  = $_SESSION['DATE'];

        // restore commodity information
        $this->commodity_code = $_SESSION['COMMODITY_CODE_ARRAY'];
        $this->commodity_name = $_SESSION['COMMODITY_NAME_ARRAY'];
        $this->unit_name      = $_SESSION['UNIT_NAME_ARRAY'];
        $this->unit_price     = $_SESSION['UNIT_PRICE_ARRAY'];
        $this->total_price    = $_SESSION['TOTAL_PRICE_ARRAY'];
        $this->amount         = $_SESSION['AMOUNT_ARRAY'];

        $this->warehouse_from      = $_SESSION['WAREHOUSE_FROM'];
        $this->warehouse_to        = $_SESSION['WAREHOUSE_TO'];
        $this->warehouse_name_from = $_SESSION['WAREHOUSE_NAME_FROM'];
        $this->warehouse_name_to   = $_SESSION['WAREHOUSE_NAME_TO'];
    }

    /*
    *  ���ʥ����ɤ�¸�ߥ����å���
    */
    function commodityCodeExsists($code,$index,$formvars)
    {
        $_query = "select c.code,c.name,c.current_unit_price,u.name from m_commodity c, m_unit u where c.code='$code' and u.code=c.unit_code";

        // print $_query;

        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            if( $this->sql->record[0] > 0)
            {
                $this->commodity_name[$index] = $this->sql->record[1];

                $id = "unit_price_$index";
                if(!isset($formvars[$id])  || strlen($formvars[$id]) == 0 )
                {
                    $this->unit_price[$index]     = $this->sql->record[2];
                }
                $this->unit_name[$index]      = $this->sql->record[3];
                return true;
            }
        }
        return false;
    }

    /*
    * ���ϥ����å���
    */
    function checkEntry(&$formvars)
    {
        // �إ����ˤ������դ�������å���
        //
        // 1. �ե����ޥåȤ�����������
        // 2. ���ϲ�ǽ�ʥǡ����ʤΤ����ʣ��������Ϥ����
        //

        if(strlen($formvars['year']) != 4)
        {
            $this->error = "ǯ�����������Ϥ��Ƥ���������";
            return false;
        }

        if(strlen($formvars['month'])==1)
        {
            $formvars['month'] = '0' . $formvars['month'];
        }

        if(strlen($formvars['month']) != 2)
        {
            $this->error = "������������Ϥ��Ƥ���������";
            return false;
        }

        if(strlen($formvars['date'])==1)
        {
            $formvars['date'] = '0' . $formvars['date'];
        }

        if(strlen($formvars['date']) != 2)
        {
            $this->error = "�������������Ϥ��Ƥ���������";
            return false;
        }

        if(checkdate($formvars['month'],$formvars['date'],$formvars['year'])==false)
        {
            $this->error = "���դ������������Ϥ��Ƥ���������";
            return false;
        }

        // �����쥳���ɥ롼��
        for($i=$count=0; $i<10; $i++)
        {
            $commodity_code = $formvars["commodity_code_$i"];

            if(strlen($commodity_code) > 0){
                //
                //
                $count = $i+1;
                //
                $act_flag = $formvars["act_flag_$i"];

                // ���ʥ��������ϥ����å���
                if(strlen($commodity_code) != 5)
                {
                    $this->error = "$count ���ܤξ��ʥ����ɤ����������Ϥ���Ƥ��ޤ���";
                    return false;
                }
                // ���ʥ�����¸�ߥ����å���
                if(!$this->commodityCodeExsists($commodity_code,$i,$formvars))
                {
                    $this->error = "$count ���ܤξ��ʥ����ɤ���Ͽ����Ƥ��ޤ���";
                    return false;
                }

                // ñ�����ϥ����å���
                $id = "unit_price_$i";
                if(strlen($formvars[$id]) == 0)
                {
                    $this->error = "$count ���ܤ�ñ�������������Ϥ���Ƥ��ޤ���";
                    return false;
                }

                // �������ϥ����å���
                $id = "total_price_$i";
                if(strlen($formvars[$id]) == 0)
                {
                    // ���ʤ�׻��������ꡣ
                    $this->total_price[$i] = $formvars["unit_price_$i"] * $formvars["amount_$i"];
                    // �ե�������᤹��
                    $formvars[$id] = $this->total_price[$i];
                }
            }
        }

        if($count == 0)
        {
            $this->error = '���٤���Ԥ����Ϥ���Ƥ��ޤ���';
            return false;
        }
        return true;
    }

    /*
    *    ���ʻ��ȥܥ���
    *
    */
    function isCommodityRef($formvars = array())
    {
        for($i=0; $i<10; $i++)
        {
            $name = "commodity_ref_$i";
            if(isset($formvars[$name]))
            {
                return $i;
            }
        }
        return -1;
    }

    /*
    *  ���ʤ��������祳���ɡʻ���⥳���ɡˤ����롣
    */
    function getShipSectionCode($commodity_code)
    {
        $_query = sprintf("select ship_section_code from m_commodity where code='%s'", $commodity_code);
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            if($this->sql->record != null)
            {
                return $this->sql->record[0];
            }
        }
        return "10";  // ������
    }
}
?>