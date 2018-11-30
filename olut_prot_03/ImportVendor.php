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
 *    �����ǡ����Ѵ� - ImportVendor.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

// require_once('c:\wampp2\php\pear\DB.php'); // PEAR DB
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

function convertVendor()
{
    $fn = 'c:\ntc\olut\master_data\bin\cftori.txt';
    $fh = fopen($fn,"r");
    
    $db =& new SQL;
    $db->connect(OLUT_DSN);


    while (!feof($fh))
    {
        $line = fgets($fh);
        
        if(strlen($line)<3)
        {
            break;
        }
        
        $code = substr($line,0,5);
        $ckd    = substr($line,5,1);
        $name_n = substr($line,6,30);
        $adrs_n = substr($line,36,40);
        $tel    = substr($line,76,20);
        
        $name_k = substr($line,96,30);  // ���ե��åȶ��äƤ롩
        
        //
        $name_n = mb_convert_encoding($name_n,"EUC-JP","SJIS");
        $adrs_n = mb_convert_encoding($adrs_n,"EUC-JP","SJIS");
        $name_k = mb_convert_encoding($name_k,"EUC-JP","SJIS");

        // (��̡����ޤ�ƺ�������ޥ��������Ȥ˶�礬������
        //  $name_k = mb_ereg_replace("(\S*)","",$name_k);
        //  �嵭�Τ褦������ɽ���ϥХ��äƻȤ��ʤ���
        
        if(substr($name_k,0,1)=='(')
        {
            $end_index = strchr($name_k,')');
            if($end_index != false)
            {
              $name_k = substr($end_index,1);
            }
        }
        
        // ���Ѥ��Ѵ�
        $name_k = mb_convert_kana($name_k,"KV");
        

        print "$name_n<br>";
        
        $_query = "insert into m_vendor (code,ckd,";
        $_query .= " name,name_k,address,tel,updated) ";
        $_query .= " values('$code','$ckd',";
        $_query .= " '$name_n','$name_k','$adrs_n','$tel',now())";
        
        // print "$_query<br>";
        
        if($db->query($_query)==false)
        {
            break;
        }
    }
    
    $db->disconnect();

    fclose($fh);

}

// main.

convertVendor();


?>