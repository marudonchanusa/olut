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
 *    ǯ�����в٥ե������� - ImportAnnualInventory.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB


function importAnualInventory($fn)
{
    $fn = "c:\\ntc\\olut\\data\\cdnennyu\\$fn";
    $fh = fopen($fn,"r");

    $db =& new SQL;
    $db->connect(OLUT_DSN);

    while (!feof($fh))
    {
        $line = fgets($fh);

        if(strlen($line) < 10)
        {
            break;
        }

        $data = split(",",$line);

        $com_code = $data[1];       // ���ʥ�����

        if($data[2] != '9')         // ���ץ쥳���ɤ���ʤ���
        {
            continue;
        }

        // 2005�����쥤����
        // 2002�����쥤����
        // 2003�����쥤����
        // 2004�����쥤����

        $years = array(2005,2002,2003,2004);

        for($j=0; $j<4; $j++)
        {
            $year = $years[$j];

            for($i=0; $i<12; $i++)
            {
                $dt = mktime(0,0,0,$i+1,1,$year);
                $dt = date('Y/m/d',$dt);

                $amount = $data[$i+4+$j*13];
                $price  = $data[$i+4+13*4+$j*13];
                
                if($com_code=='23500')
                {
                    print "hoge";
                }

                $_query = "insert into m_annual_inventory(com_code,dt,amount,price)" ;
                $_query .= " values('$com_code','$dt',$amount,$price)";

                if($db->query($_query)==false)
                {
                    print "$_query<br>";
                    break;
                }
            }
        }
    }

    $db->disconnect();

    fclose($fh);

}

// main.

$files = array("cdnennyu_1.csv","\cdnennyu_2.csv","\cdnennyu_3.csv");

foreach($files as $fn)
{
    importAnualInventory($fn);
}

?>