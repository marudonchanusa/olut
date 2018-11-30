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
 *    ������Ͽ - ArrivalOfGoodsClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

class ArrivalOfGoods
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;

    // �ʲ��ϲ��̤�ɽ������ǡ���������ǣ����Ļ��ġ�
    var $vendor_code = null;
    var $vendor_name = null;
    var $commodity_code = null;
    var $commodity_name = null;        // ���٤��Ȥξ���̾
    var $unit_name    = null;          // ���٤��Ȥ�ñ��
    var $unit_price   = null;          // ���٤��Ȥ�ñ��
    var $total_price  = null;          // ���٤��Ȥβ���
    var $amount       = null;          // ���٤��Ȥο���
    var $payment_flag = null;          // ���٤��Ȥλ�ʧ��ʬ��
    var $act_flag = null;
    var $memo = null;
    var $year  = null;
    var $month = null;
    var $date  = null;
    var $slip_no = null;               // ��ɼ�ֹ�
    var $warehouse_code;

    // ����������
    var $search_result_count = 0;
    var $search_result_slip_no;
    var $search_result_commodity_name;
    var $search_result_vendor_name;
    var $search_result_date;
    var $date_from;
    var $date_to;

    /**
     * class constructor
     */
    function ArrivalOfGoods()
    {
        // instantiate the sql object
        $this->sql =& new ArrivalOfGoods_SQL;

        // instantiate the template object
        $this->tpl =& new ArrivalOfGoods_Smarty;

        // ���å���󤫤������ѿ��˥쥹�ȥ���
        $this->restoreFromSession();
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
        if(isset($formvars['warehouse_code']))
        {
            $this->warehouse_code = $formvars['warehouse_code'];
        }
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
        if(isset($formvars['slip_no']))
        {
            $this->slip_no = $formvars['slip_no'];
        }

        //
        // ���ٹԤˤ����͡�
        //

        $do_save = false;

        for($i=0; $i<10; $i++)
        {
            $id = "payment_flag_$i";
            if(isset($formvars[$id]))
            {
                $this->payment_flag[$i] = $formvars[$id];
                $do_save = true;
            }

            $id = "act_flag_$i";
            if(isset($formvars[$id]))
            {
                $this->act_flag[$i] = $formvars[$id];
                $do_save = true;
            }

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

            $id = "vendor_code_$i";
            if(isset($formvars[$id]))
            {
                $this->vendor_code[$i] = $formvars[$id];
                $do_save = true;
            }


            $id = "memo_$i";
            if(isset($formvars[$id]))
            {
                $this->memo[$i] = $formvars[$id];
                $do_save = true;
            }
        }

        if($do_save)
        {
            $this->saveToSession();
        }
    }

    /**
     * display the vendor entry form
     *
     * @param array $formvars the form variables
     */
    function displayForm($formvars = array())
    {
        if(!isset($this->vendor_name))
        {
            $this->clearAllSession();
        }

        // assign the form vars
        $this->tpl->assign('year',$this->year);
        $this->tpl->assign('month',$this->month);
        $this->tpl->assign('date',$this->date);
        $this->tpl->assign('slip_no',sprintf("%07d",$this->slip_no));
        $this->tpl->assign('post',$formvars);
        $this->tpl->assign('vendor_code', $this->vendor_code);
        $this->tpl->assign('vendor_name', $this->vendor_name);
        $this->tpl->assign('commodity_code', $this->commodity_code);
        $this->tpl->assign('commodity_name', $this->commodity_name);
        $this->tpl->assign('unit_name',  $this->unit_name);
        $this->tpl->assign('unit_price', $this->unit_price);
        $this->tpl->assign('total_price', $this->total_price);
        $this->tpl->assign('amount', $this->amount);
        $this->tpl->assign('warehouse_list', $this->getWarehouseList($formvars));   // �Ҹ˥����ɤΥꥹ�Ȥ����롣
        $this->tpl->assign('payment_flag_list', $this->getPaymentFlagList());       // ��ʧ��ʬ�Υꥹ�Ȥ����롣
        $this->tpl->assign('act_flag_list',$this->getActFlagList());
        $this->tpl->assign('memo',$this->memo);

        if( $_SESSION['modify_mode'] == true )
        {
            $this->tpl->assign('readonly','readonly');
        }
        else
        {
            $this->tpl->assign('readonly','');
        }

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ArrivalOfGoodsForm.tpl');

        // ��������¸���Ƥ��뤳�Ȥ���ա�
        // ������destrouctor����¸������(?)
        $this->saveToSession();

    }

    function displaySelectionForm($formvars = array())
    {
        // assign the form vars
        if(strlen($this->date_from)==0 && strlen($this->date_to) == 0)
        {
            // �ʲ��Ͻ��������꤫���������ɬ�פ����롣@later
            $year = Date('Y');
            $month = Date('m');
            $this->date_from = sprintf("%04d%02d01",$year,$month);
            $last = OlutApp::GetLastDate($year,$month);
            $this->date_to = sprintf("%04d%02d%02d",$year,$month,$last);
        }


        $this->tpl->assign('date_from', $this->date_from);
        $this->tpl->assign('date_to', $this->date_to);
        $this->tpl->assign('count',$this->search_result_count);
        $this->tpl->assign('date', $this->search_result_date);
        $this->tpl->assign('slip_no',$this->search_result_slip_no);
        $this->tpl->assign('vendor_name', $this->search_result_vendor_name);
        $this->tpl->assign('commodity_name', $this->search_result_commodity_name);

        // assign error message
        if($this->sql->error != null)
        {
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('ArrivalOfGoodsSelectionForm.tpl');
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
        $_SESSION['WAREHOUSE_CODE']       = $this->warehouse_code;

        $_SESSION['VENDOR_CODE_ARRAY']    = $this->vendor_code;
        $_SESSION['VENDOR_NAME_ARRAY']    = $this->vendor_name;
        $_SESSION['COMMODITY_CODE_ARRAY'] = $this->commodity_code;
        $_SESSION['COMMODITY_NAME_ARRAY'] = $this->commodity_name;
        $_SESSION['UNIT_NAME_ARRAY']      = $this->unit_name;
        $_SESSION['UNIT_PRICE_ARRAY']     = $this->unit_price;
        $_SESSION['TOTAL_PRICE_ARRAY']    = $this->total_price;
        $_SESSION['AMOUNT_ARRAY']         = $this->amount;
        $_SESSION['PAYMENT_FLAG_ARRAY']   = $this->payment_flag;
        $_SESSION['ACT_FLAG_ARRAY']       = $this->act_flag;
        $_SESSION['MEMO_ARRAY']           = $this->memo;


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
        $this->slip_no = $_SESSION['SLIP_NO'];
        $this->warehouse_code = $_SESSION['WAREHOUSE_CODE'];

        // restore vendor information
        $this->vendor_code  = $_SESSION['VENDOR_CODE_ARRAY'];
        $this->vendor_name  = $_SESSION['VENDOR_NAME_ARRAY'];

        // restore commodity information
        $this->commodity_code = $_SESSION['COMMODITY_CODE_ARRAY'];
        $this->commodity_name = $_SESSION['COMMODITY_NAME_ARRAY'];
        $this->unit_name      = $_SESSION['UNIT_NAME_ARRAY'];
        $this->unit_price     = $_SESSION['UNIT_PRICE_ARRAY'];
        $this->total_price    = $_SESSION['TOTAL_PRICE_ARRAY'];
        $this->amount         = $_SESSION['AMOUNT_ARRAY'];
        $this->payment_flag   = $_SESSION['PAYMENT_FLAG_ARRAY'];
        $this->act_flag       = $_SESSION['ACT_FLAG_ARRAY'];
        $this->memo           = $_SESSION['MEMO_ARRAY'];
    }

    /*
    *  ���å���󥯥ꥢ����Ӥ��Υ��ԡ��ѿ��򥯥ꥢ��
    */

    function clearAllSession()
    {
        unset($_SESSION['YEAR']);
        unset($_SESSION['MONTH']);
        unset($_SESSION['DATE'] );
        unset($_SESSION['SLIP_NO']);
        unset($_SESSION['VENDOR_CODE_ARRAY']);
        unset($_SESSION['VENDOR_NAME_ARRAY']);
        unset($_SESSION['COMMODITY_CODE_ARRAY']);
        unset($_SESSION['COMMODITY_NAME_ARRAY']);
        unset($_SESSION['UNIT_NAME_ARRAY'])   ;
        unset($_SESSION['UNIT_PRICE_ARRAY'])  ;
        unset($_SESSION['TOTAL_PRICE_ARRAY']) ;
        unset($_SESSION['AMOUNT_ARRAY'])      ;
        unset($_SESSION['PAYMENT_FLAG_ARRAY']);
        unset($_SESSION['ACT_FLAG_ARRAY']);
        unset($_SESSION['MEMO_FLAG_ARRAY']);

        $this->vendor_name    = array();
        $this->vendor_code    = array();
        $this->commodity_name = array();
        $this->commodity_code = array();
        $this->unit_name      = array();
        $this->unit_price     = array();
        $this->total_price    = array();
        $this->payment_flag   = array();
        $this->act_flag       = array();
        $this->memo           = array();
        $this->amount         = array();
        $this->slip_no        = $this->getNewSlipNo(); // null; // array();
        $this->warehouse_code = '01';

    }

    /*
    *   ��ɼ�ֹ�κ����͡ܣ������롣
    *   ʣ���桼���������ѻ��ˤ����꤬���롣
    *   �����ѤΥơ��֥���̤˻��Ĥ����������� & ����ϥƥ�ݥ�ꥤ��ץ���ȡ�@later
    */
    function getNewSlipNo()
    {
        $_query = "select max(slip_no)+1 from t_main";
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            //
            $this->slip_no = $this->sql->record[0];
            if($this->slip_no == null )
            {
                $this->slip_no = 1;
            }
        }
        return $this->slip_no;
    }

    /*
    *  ���ʤ��������祳���ɡʻ���⥳���ɡˤ����롣
    */
    function getShipSectionCode($commodity_code)
    {
        $_query = sprintf("select ship_section_code from m_commodity where code='%s'", $commodity_code);
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            return $this->sql->record[0];
        }
        return "10";  // ������
    }

    /*
    *    �ȥ�󥶥��������ɲäޤ��Ͻ�����
    *
    *     1. autocommit �ϥ��դˤ��롣
    *     2. �������commit���롣
    *
    */

    function saveEntry($formvars = array())
    {
        // �ե�����Υإ����ˤ����ѿ���
        if(strlen($formvars['month'])==1)
        {
            $formvars['month'] = '0' . $formvars['month'];
        }
        if(strlen($formvars['date'])==1)
        {
            $formvars['date'] = '0' . $formvars['date'];
        }
        
        $dt  = $formvars['year'] . "/" . $formvars['month'] . "/" . $formvars['date'];
        $warehouse_code = $formvars['warehouse_code'];
        $slip_no        = $formvars['slip_no'];

        if(OlutApp::checkDate($dt)==false)
        {
            $this->error = "���դ�������������ޤ���";
            return false;
        }
        
        // �桼����ID
        $uid = $_SESSION['user_id'];

        //
        $this->sql->Autocommit(false);

        // �����쥳���ɥ롼��
        for($i=0; $i<10; $i++)
        {
            //
            $commodity_code = $formvars["commodity_code_$i"];

            if(strlen($commodity_code) > 0)
            {
                $ship_sec_code  = $this->getShipSectionCode($commodity_code);
                $memo           = $formvars["memo_$i"];
                $act_flag       = $formvars["act_flag_$i"];
                $payment_flag   = $formvars["payment_flag_$i"];
                $vendor_code    = $formvars["vendor_code_$i"];
                $amount         = $formvars["amount_$i"];
                $unit_price     = $formvars["unit_price_$i"];
                $total_price    = $formvars["total_price_$i"];

                if($this->alreadyThere($slip_no,$i))
                {
                    // �����⡼�ɡ�
                    $_query  = "update t_main ";
                    $_query .= " set act_date='$dt',";
                    $_query .= " slip_no=$slip_no,line_no=$i,";
                    $_query .= " com_code='$commodity_code',";
                    $_query .= " warehouse_code='$warehouse_code',";
                    $_query .= " orig_code='$vendor_code',";
                    $_query .= " act_flag='$act_flag',";
                    $_query .= " amount=$amount,";
                    $_query .= " unit_price=$unit_price,";
                    $_query .= " total_price=$total_price,";
                    $_query .= " payment_flag='$payment_flag',";
                    $_query .= " memo='$memo',";
                    $_query .= " ship_sec_code='$ship_sec_code',";
                    $_query .= " update_userid='$uid',";                    
                    $_query .= " updated=now()";
                    $_query .= " where slip_no=$slip_no and line_no=$i and deleted is null";
                    $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')";

                    // print $_query;
                }
                else
                {
                    // �����ɲá�
                    $_query  = "insert into t_main (";
                    $_query .= " act_date,slip_no,line_no,";
                    $_query .= " com_code,warehouse_code,orig_code,act_flag,";
                    $_query .= " amount, unit_price, total_price, payment_flag,memo,ship_sec_code, update_userid, updated) ";
                    $_query .= " values ('$dt',$slip_no,$i,";
                    $_query .= "'$commodity_code',";
                    $_query .= "'$warehouse_code',";
                    $_query .= "'$vendor_code',";
                    $_query .= "'$act_flag',";
                    $_query .= "$amount,";
                    $_query .= "$unit_price,";
                    $_query .= "$total_price,";
                    $_query .= "'$payment_flag',";
                    $_query .= "'$memo',";
                    $_query .= "'$ship_sec_code','$uid',now())";

                    // print $_query;
                }

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
            }
        }
        $this->sql->Commit();

        // ���Υ����ȥ��ߥåȤ��᤹��
        $this->sql->AutoCommit(true);
        return true;
    }

    function alreadyThere($slip_no,$line_no)
    {
        $_query = "select count(*) from t_main";
        $_query .= " where slip_no=$slip_no and line_no=$line_no and deleted is null";
        $_query .= " and (act_flag='1' or act_flag='2' or act_flag='3')";

        if($this->sql->query($_query,SQL_INIT))
        {
            if($this->sql->record[0] > 0)
            {
                return true;
            }
        }
        return false;
    }

    /*
    *  ����襳���ɤ�¸�ߥ����å���
    */
    function vendorCodeExsists($code,$index)
    {
        $_query = sprintf("select code,name from m_vendor where code='%s'", $code);
        if($this->sql->query($_query,SQL_INIT)==true)
        {
            // �����ɤ��ѹ��ˤʤ���⤢��ΤǼ����̾�Ϻ������ꡣ
            if( $this->sql->record[0] > 0)
            {
                $this->vendor_name[$index] = $this->sql->record[1];
                return true;
            }
        }
        return false;
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
                    $this->unit_price[$index] = $this->sql->record[2];
                    $formvars[$id]  = $this->sql->record[2];             // �ե�����ǡ����ˤ��᤹��
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

        if(strlen($formvars['slip_no']) != 7)
        {
            $this->error = "��ɼ�ֹ�����������Ϥ��Ƥ���������";
            return false;
        }

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

        //
        // ê�����飲�������Υ����å�������뤳�ȡ������� @later.
        //
        // ����ʥ����ɤǤ��餷����
        // $lastmonth = mktime(0, 0, 0, date("m")-1, date("d"),  date("Y"));
        //



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
                if(!$this->commodityCodeExsists($commodity_code,$i,&$formvars))
                {
                    $this->error = "$count ���ܤξ��ʥ����ɤ���Ͽ����Ƥ��ޤ���";
                    return false;
                }

                // ����襳�������ϥ����å�
                $id = "vendor_code_$i";
                if(strlen($formvars[$id]) != 5)
                {
                    $this->error = "$count ���ܤμ���襳���ɤ����������Ϥ���Ƥ��ޤ���";
                    return false;
                }

                // ����襳����¸�ߥ����å���
                if(!$this->vendorCodeExsists($formvars[$id],$i))
                {
                    $this->error = "$count ���ܤμ���襳���ɤ���Ͽ����Ƥ��ޤ���";
                    return false;
                }

                // �������ϥ����å���
                if($act_flag != '2')   // �Ͱ����ϥΡ������å��Ȥ��롣
                {
                    $id = "amount_$i";
                    if(strlen($formvars[$id]) == 0)
                    {
                        $this->error = "$count ���ܤο��̤����������Ϥ���Ƥ��ޤ���";
                        return false;
                    }
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
                    // ���ʤ�׻��������ꡣ ==> �������ʲ��ڼΤơ�
                    $this->total_price[$i] = floor($formvars["unit_price_$i"] * $formvars["amount_$i"]);
                    // �ե�������᤹��
                    $formvars[$id] = $this->total_price[$i];
                }
                //
                //
                // �Ͱ����ΤȤ��϶�ۤ�ޥ��ʥ������Ϥ��Ƥ�餦��
                //

                if($act_flag == '2')
                {
                    if($formvars["total_price_$i"] > 0)
                    {
                        $this->error = "$count �Ԥ��Ͱ����ʤΤǶ�ۤ�ޥ��ʥ��Ȥ��Ƥ�������";
                        return false;
                    }
                    if($this->$formvars["amount_$i"] != 0)
                    {
                        $this->error = "$count �Ԥ��Ͱ����ʤΤǿ��̤򥼥�Ȥ��Ƥ�������";
                        return false;
                    }
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
    *    ����軲�ȥܥ���
    *
    */
    function isVendorRef($formvars = array())
    {
        for($i=0; $i<10; $i++)
        {
            $name = "vendor_ref_$i";
            if(isset($formvars[$name]))
            {
                return $i;
            }
        }
        return -1;
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
    *  �Ҹ˥����ɤΥꥹ�Ȥ����롣
    */

    function getWarehouseList($formvars)
    {
        if(isset($formvars['warehouse_code']))
        {
            $selected_code = $formvars['warehouse_code'];
        }
        else
        {
            $selected_code = $_SESSION['warehouse_code'];
        }

        if($selected_code == null)
        {
            $selected_code = $this->warehouse_code;
        }

        $_query = "select code,name from m_warehouse order by code";
        if( $this->sql->query($_query,SQL_ALL) == true )
        {
            foreach($this->sql->record as $rec)
            {
                $warehouse_code = trim($rec[0]);
                if(!strcmp($warehouse_code,$selected_code))
                {
                    $result .= sprintf("<option value='%s' selected>",$warehouse_code);
                }
                else
                {
                    $result .= sprintf("<option value='%s'>",$warehouse_code);
                }
                $result .= $rec[1];
                $result .= "</option>";
            }
        }
        return $result;
    }

    /*
    *  ��ʧ��ʬ�Υꥹ�Ȥ����롣
    */
    function getPaymentFlagList()
    {
        for($i=0; $i<10; $i++)
        {
            if($this->payment_flag[$i] == '1')
            {
                $selected0 = "";
                $selected1 = "selected";
            }
            else
            {
                $selected0 = "selected";
                $selected1 = "";
            }
            $result[$i] .= "<option value=0 $selected0>���</option>";
            $result[$i] .= "<option value=1 $selected1>����</option>";
        }
        return $result;
    }

    /*
    *  ���˥ե饰�Υꥹ�Ȥ����롣
    */
    function getActFlagList()
    {
        $afl = array(1=>'����',2=>'�Ͱ�����',3=>'��������');
        $result = '';
        for($i=0; $i<10; $i++)
        {
            foreach($afl as $key=>$value)
            {
                if($this->act_flag[$i] == $key)
                {
                    $sel = "selected";
                }
                else
                {
                    $sel = '';
                }
                $result[$i] .= "<option value=$key $sel>$value</option>";
            }
        }
        return $result;
    }

    function getFromTo($formvars)
    {
        $this->date_from = $formvars['date_from'];
        $this->date_to   = $formvars['date_to'];
    }

    function getTargetSlipNo($formvars)
    {
        if(strlen($formvars['arg'])==0)
        {
            $this->error = "��ɼ�����򤷤Ƥ�������";
            return false;
        }
        else
        {
            // select_0000000 �η����ǥǡ������ݥ��Ȥ���Ƥ��롣
            $this->slip_no = ereg_replace("select_","",$formvars['arg']);
        }
        return true;
    }

    /*
    *   �����ǡ������ɤ߹��ࡣ
    */
    function getModifyData()
    {
        $_query  = "select t.act_date,t.warehouse_code, t.line_no,t.payment_flag,t.act_flag,";
        $_query .= " t.orig_code,v.name,c.unit_code,u.name,";
        $_query .= " t.com_code, c.name,t.unit_price, t.total_price, t.amount";
        $_query .= " from t_main t, m_commodity c, m_vendor v, m_unit u";
        $_query .= " where t.slip_no=$this->slip_no";
        $_query .= "  and t.com_code=c.code and v.code=t.orig_code";
        $_query .= "  and c.unit_code=u.code and t.deleted is null";
        $_query .= " order by t.line_no";

        // print $_query;

        if(!$this->sql->query($_query,SQL_ALL))
        {
            $this->error = $this->sql->error;
            return false;
        }

        $header_is_set = false;

        foreach($this->sql->record as $rec)
        {
            if(!$header_is_set)
            {
                // YYYY-MM-DD
                $this->year = substr($rec[0],0,4);
                $this->month = substr($rec[0],5,2);
                $this->date  = substr($rec[0],8,2);
                $this->warehouse_code = $rec[1];
                $header_is_set = true;
            }

            // base is zero.
            $index = $rec[2];       // line_no
            $this->payment_flag[$index] = $rec[3];
            $this->act_flag[$index]     = $rec[4];
            $this->vendor_code[$index]  = $rec[5];
            $this->vendor_name[$index]  = $rec[6];
            $this->unit_code[$index]    = $rec[7];
            $this->unit_name[$index]    = $rec[8];
            $this->commodity_code[$index] = $rec[9];
            $this->commodity_name[$index] = $rec[10];
            $this->unit_price[$index]     = $rec[11];
            $this->total_price[$index]    = $rec[12];
            $this->amount[$index]         = $rec[13];
            //
        }

        // �ʲ������åȤ���Ƥ��ʤ��ʤ顢�ǡ�����̵���ä���
        if($header_is_set == false)
        {
            $this->error = "�ǡ���������ޤ���";
            return false;
        }
        return true;

    }

    function delete()
    {
        $_query  = "delete from t_main ";
        $_query .= " where slip_no=$this->slip_no";
        $_query .= "  and (act_flag='1' or act_flag='2' or act_flag='3')";
        $_query .= "  and deleted is null";

        // print $_query;

        if(!$this->sql->query($_query,SQL_NONE))
        {
            $this->error = $this->sql->error;
            return false;
        }
        return true;
    }
    /*
    *  ��ɼ�θ���
    */

    function find()
    {
        $_query = "select t.act_date,t.slip_no,c.name,v.name ";
        $_query .= " from t_main t, m_commodity c, m_vendor v";
        $_query .= " where t.orig_code = v.code and t.line_no=0 and ";
        $_query .= " t.com_code=c.code and (t.act_flag='1' or t.act_flag='3')";
        $_query .= " and t.deleted is null";

        if(strlen($this->date_from) && strlen($this->date_to))
        {
            $dt1 = OlutApp::formatDate($this->date_from);
            $dt2 = OlutApp::formatDate($this->date_to);

            $_query .= " and t.act_date>='$dt1' and t.act_date<='$dt2'";
        }
        else if(strlen($this->date_from))
        {
            $dt = OlutApp::formatDate($this->date_from);
            $_query .= " and t.act_date='$dt'";
        }
        else if(strlen($this->date_to))
        {
            $dt = OlutApp::formatDate($this->date_to);
            $_query .= " and t.act_date<='$dt'";
        }

        $_query .= " order by t.act_date, t.slip_no";

        // print $_query;

        if($this->sql->query($_query,SQL_ALL) && $this->sql->record != null)
        {
            $i = 0;
            foreach($this->sql->record as $rec)
            {
                $this->search_result_date[$i]    = ereg_replace("-","/",$rec[0]);
                $this->search_result_slip_no[$i] = sprintf("%07d",$rec[1]);
                $this->search_result_commodity_name[$i] = $rec[2];
                $this->search_result_vendor_name[$i] = $rec[3];
                $i++;
            }
            $this->search_result_count = $i;
        }

        if($this->search_result_count == 0)
        {
            $this->error = "�ǡ���������ޤ���";
            return false;
        }

        return true;
    }
}
?>
