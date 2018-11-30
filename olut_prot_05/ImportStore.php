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
 *    Ź�ޥǡ����Ѵ� - ImportStore.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

// require_once('c:\wampp2\php\pear\DB.php'); // PEAR DB
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

function convDate($dt)
{
    // +0891101 �Τ褦�ʷ�����
    // 01234567
    $yy = substr($dt,2,2);
    $mm = substr($dt,4,2);
    $dd = substr($dt,6,2);
    
    if(strcmp($dd,"00")==0)
    {
        return null;
    }
    
    if($yy < 10)
    {
        $yy = "20" . $yy;
    }
    else 
    {
        $yy = "19" . $yy;
    }
    //
    return $yy . '/' . $mm . '/' . $dd;
}

function addStoreSection($db,$code,$ss)
{
    foreach($ss as $s)
    {
        $s = trim($s);
        if($s=='00' || strlen($s)==0)
        {
            break;
        }
        
        $_query = "insert into m_store_section (code,store_section_code) ";
        $_query .= " values('$code','$s')";
        
        if(!$db->query($_query))
        {
            break;
        }
    }
    
}

function convertStore()
{
    $fn = 'c:\ntc\olut\master_data\bin\cftempo.txt';
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
        
        $arr = split(",",$line);
        
        $store_code = $arr[0];
        
        if(!strcmp($store_code,'0000'))
        {
            continue;
        }
        
        $ckd    = $arr[1];
        $scode  = $arr[2];
        $sckd   = $arr[3];
        $isdcd  = $arr[4];
        $name_n = $arr[5];
        $adrs_n = $arr[6];
        $tel    = $arr[7];
        
        // skip fuken,chiiki,
        
        // ��Ź����Ź
        $kaiten = convDate($arr[10]);
        $heiten = convDate($arr[11]);
        
        // index 12����15�Ĥ�Ź�������硣
        for($i=0; $i<15;$i++)
        {
            $div[$i] = $arr[$i+12];
        }
        
        addStoreSection($db,$store_code,$div);
        
        $name_k = $arr[113];  // ���ե��åȶ��äƤ롩
        
        //
        $name_n = mb_convert_encoding($name_n,"EUC-JP","SJIS");
        $adrs_n = mb_convert_encoding($adrs_n,"EUC-JP","SJIS");
        $name_k = mb_convert_encoding($name_k,"EUC-JP","SJIS");
        
        // ���Ѥ��Ѵ�
        $name_k = mb_convert_kana($name_k,"KV");

        $_query = "insert into m_store (code,ckd,scode,sckd,isdcd,";
        $_query .= " name,name_k,address,tel,open_date,close_date,updated) ";
        $_query .= " values('$store_code','$ckd','$scode','$sckd',";
        $_query .= " '$isdcd','$name_n','$name_k','$adrs_n','$tel',";
        
        if(strlen($kaiten))
        {
            $_query .= "'$kaiten',";
        }
        else 
        {
            $_query .= "null,";
        }
        if(strlen($heiten))
        {
            $_query .= "'$heiten'";
        }
        else 
        {
            $_query .= "null";
        }
        $_query .= ",now())";
        
        if($db->query($_query)==false)
        {
            break;
        }
    }
    
    $db->disconnect();

    fclose($fh);

}

// main.

convertStore();


?>