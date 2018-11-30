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
 *    ��̳�ǡ������� - FinancialDataTransferClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once('OlutAppLib.php');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

class FinancialDataTransfer_SQL extends SQL
{
    function FinancialDataTransfer_SQL()
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

class FinancialDataTransfer extends OlutApp
{
    var $sql;

    //
    function FinancialDataTransfer()
    {
        $this->sql =& new FinancialDataTransfer_SQL();
    }

    /*
    *   CSV����
    */
    function transferToCSV()
    {
        // ��������롣  ==> should see master @later.
        
        OlutApp::GetCurrentProcessDate($this->sql,&$year,&$month);
        
        // $year  = Date('Y');
        // $month = Date('m');
        
        $last  = OlutApp::getLastDate($year,$month);

        $date_from = "$year/$month/01";
        $date_to   = "$year/$month/$last";

        $_query  = "select * from t_main where act_flag<>'0' ";
        $_query .= " and deleted is null ";
        $_query .= " and act_date>='$date_from' and act_date<='$date_to'";

        if(!$this->sql->query($_query,SQL_INIT,SQL_ASSOC))
        {
            print "error";
            return false;
        }
        
        if($this->sql->record == null)
        {
            print "no data for $year/$month";
            return false;
        }
        
        $fn = $year . "_" . $month . ".csv";
        
        header("content-type: application/csv;");
        header("content-disposition: attachment; filename=$fn");

        $line_no = 0;

        while(1)
        {
            if($line_no==0)
            {
                // �إ����Ԥ������
                $is_first = true;
                //
                foreach($this->sql->record as $key=>$value)
                {
                    if($is_first)
                    {
                        $is_first = false;
                    }
                    else
                    {
                        print ",";
                    }
                    print trim($key);
                }
                print "\r\n";
            }

            $is_first = true;
            //
            foreach($this->sql->record as $key=>$value)
            {
                if($is_first)
                {
                    $is_first = false;
                }
                else
                {
                    print ",";
                }
                print trim($value);

            }
            print "\r\n";

            if(!$this->sql->next())
            {
                break;
            }
            $line_no++;
        }
    }
}

?>
