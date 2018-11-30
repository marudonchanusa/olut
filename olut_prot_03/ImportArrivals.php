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
 *    �в٥ǡ����Ѵ� - ImportArrivals.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB


function getUnitPrice($db,$code)
{
    if($db->query("select current_unit_price from m_commodity where code='$code'",SQL_ALL,SQL_ASSOC))
    {
        //
        return $db->record[0]['current_unit_price'];
    }
    return 0;
}

function getNextMonth($dd)  // expect "yyyy/mm/dd' format
{
    $year = substr($dd,0,4);
    $month = substr($dd,5,2);
    $date = substr($dd,8,2);
    $prev = mktime (0,0,0,$month+1,$date,$year);
    $year = date('Y',$prev);
    $month = date('m',$prev);
    $date  = date('d',$prev);

    return "$year/$month/$date";
}

function checkCommodity($db,$code)
{
    $_query = "select count(*) from m_commodity where code='$code'";
    if(!$db->query($_query,SQL_INIT))
    {
        return false;
    }
    if($db->record == null)
    {
        return false;
    }
    if($db->record[0] == 0)
    {
        return false;
    }
    return true;
}

function checkVendor($db,$code)
{
    $_query = "select count(*) from m_vendor where code='$code'";
    if(!$db->query($_query,SQL_INIT))
    {
        return false;
    }
    if($db->record == null)
    {
        return false;
    }
    if($db->record[0] == 0)
    {
        return false;
    }
    return true;
}

function importShipments($target)
{
    $count = 0;
    $compare_target = substr($target,0,4) . '/' . substr($target,4,2);
    $fn = "c:\\ntc\\olut\\data\\ntc_data\\$target\\cfidou.txt";
    $fh = fopen($fn,"r");

    $db =& new SQL;
    $db->connect(OLUT_DSN);

    while (!feof($fh))
    {
        $count++;

        $line = fgets($fh);

        if(strlen($line) < 10)
        {
            break;
        }

        $data = split(",",$line);
        
        // ��Ƭ������ɼ���դ���
        // 8���ܤ��������դ���

        $inpdate = $data[8];    // ���ͽ񤬴ְ㤤���Ȥξ��󤢤ꡣ 0 <-> 8
        $inpdate = "200" . substr($inpdate,3,1) . '/' . substr($inpdate,4,2) . '/' . substr($inpdate,6,2);
        //
        
        $slip_no  = ereg_replace("^\+*","",$data[1]);
        $line_no  = '0';        // $data[2];
        $syocd    = $data[2];
        $shicd    = $data[3];   //01234567
        $inskocd  = $data[4];   //

        $ottoricd = $data[7];   //
        $actdate  = $data[0];   // 
        $actdate = "200" . substr($actdate,3,1) . '/' . substr($actdate,4,2) . '/' . substr($actdate,6,2);

        $nesakbn  = $data[9];
        $payment_flag = $data[10];

        $gaku  = $data[11];
        $suryo = $data[12];

        $gaku  = ereg_replace("^\+*","",$gaku);
        $suryo = ereg_replace("^\+*","",$suryo);
        
        // ����ʬ�ǤϤʤ�������Ͽ���ʤ���
        if(strncmp($compare_target,$actdate,7) != 0)
        // if(strncmp($compare_target,$inpdate,7) != 0)
        {
            continue;
        }

        if($suryo==0 && $gaku==0)
        {
            continue;
        }
        
        if($nesakbn != '1')
        {
            print "nesage $nesakbn \n";
            continue;
        }
        
        // ���ʤ�¸�ߤ��뤫��
        if(!checkCommodity($db,$syocd))
        {
            print "inconsistent commodity at $target, $count";
            continue;
        }

        // ������¸�ߤ��뤫��
        if(!checkVendor($db,$ottoricd))
        {
            print "inconsistent vendor at $target, $count";
            continue;
        }

        if($suryo != 0 && $gaku == 0)
        {
            $unit_price = getUnitPrice($db,$syocd);
            if($unit_price != 0)
            {
              //  $gaku = $suryo * $unit_price;
            }
        }
        else
        if($suryo == 0 && $gaku != 0)
        {
            $unit_price = getUnitPrice($db,$syocd);
            if($unit_price != 0)
            {
                // print "hoge";
                // $suryo  = $gaku / $unit_price;
            }
        }
        else
        {
            $unit_price = $gaku / $suryo;
        }

        $_query = "insert into t_main (act_date,act_flag,slip_no,line_no,orig_code,ship_sec_code,warehouse_code,com_code,amount,total_price,unit_price,payment_flag)";
        $_query .= " values('$actdate','1','$slip_no','$line_no','$ottoricd','$shicd','$inskocd','$syocd',$suryo,";
        $_query .= "  $gaku, $unit_price,$payment_flag )";

        if($db->query($_query)==false)
        {
            print "$_query<br>";
            break;
        }
    }

    $db->disconnect();

    fclose($fh);

}

// main.

//$months = array("200312","200401","200402","200403",
//"200404", "200405", "200406","200407","200408",
//"200409","200410","200411","200412","200501",
//"200502","200503","200504","200505","200506");

$months = array("200507","200508");

// $months= array("200504");
foreach($months as $m)
{
    importShipments($m);
}

?>