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
 * このプログラムはフリーソフトウェアです。あなたはこれを、フリーソフトウェ
 * ア財団によって発行された GNU 一般公衆利用許諾契約書(バージョン2か、希
 * 望によってはそれ以降のバージョンのうちどれか)の定める条件の下で再頒布
 * または改変することができます。
 *
 * このプログラムは有用であることを願って頒布されますが、*全くの無保証* 
 * です。商業可能性の保証や特定の目的への適合性は、言外に示されたものも含
 * め全く存在しません。詳しくはGNU 一般公衆利用許諾契約書をご覧ください。
 *
 * あなたはこのプログラムと共に、GNU 一般公衆利用許諾契約書の複製物を一部
 * 受け取ったはずです。もし受け取っていなければ、フリーソフトウェア財団ま
 * で請求してください(宛先は the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)。
 *
 *   Program name:
 *    商品データ変換 - ImportCommodity.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

include('sys_define.inc');

require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once('DB.php'); // PEAR DB

function mb_trim($str)
{
    return mb_ereg_replace("　","",$str);
}

function convertStore()
{
    $fn = 'c:\ntc\olut\master_data\bin\cfsyohin.txt';
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
        
        $arr = split(',',$line);
        
        $code   = $arr[0];   // substr($line,0,5);
        $name_n = $arr[1];   // substr($line,5,30);
        $shitei = $arr[2];   // substr($line,35,1);
        $ktancd = $arr[3];   // substr($line,36,2);

        $jtancd = $arr[5];   //substr($line,48,2);
        $ltancd = $arr[8];   // substr($line,63,2);
        
        $shicd  = $arr[11];  // substr($line,78,2);
        
        $accd   = $arr[12];  // substr($line,80,4);
        $itmcd  = $arr[13];  // substr($line,84,4);

        // $isdcd  = $arr[14];  // substr($line,88,5);
        // $prtkbn = substr($line,95,1);
        
        $com_class_code   = $arr[14]; // substr($isdcd,0,5);
        $order_sheet_flag = $arr[15]; // substr($isdcd,5,1);
        $stock_flag  =      $arr[16];  // substr($isdcd,6,1);
        
        if($order_sheet_flag == false)
        {
            $order_sheet_flag = '0';
        }
        if($stock_flag == false)
        {
            $stock_flag = '0';
        }
        
        $gentanka = $arr[19] ; // 100; // substr($line,96,5);
        $raitanka = $arr[20] ; // 120; //  substr($line,101,5);
        
        // jan codeは結局入っていない。
        $jancd  = '00000000000000000000'; // substr($line,166,20);
        $note_n = $arr[33];     // substr($line,186,40);
        $name_e = $arr[34];     // substr($line,226,20);
        //
        $name_n = mb_convert_encoding($name_n,"EUC-JP","SJIS");
        $note_n = mb_convert_encoding($note_n,"EUC-JP","SJIS");
        $name_e = mb_convert_encoding($name_e,"EUC-JP","SJIS");
        
        // 全角に変換
        $name_e = mb_convert_kana($name_e,"KV");
        
        $name_n = mb_trim($name_n);
        $note_n = trim($note_n);
        $name_e = trim($name_e);

        // print "$name_n<br>";
        
        $_query = "insert into m_commodity (code,name,name_k,dist_flag,unit_code,order_unit_code,";
        $_query .= "lot_unit_code,ship_section_code,accd,itmcd,com_class_code,order_sheet_flag,stock_flag,";
        $_query .= " current_unit_price,next_unit_price,jancd,note) ";
        $_query .= " values('$code','$name_n','$name_e','$shitei',";
        $_query .= " '$ktancd','$jtancd','$ltancd','$shicd','$accd','$itmcd','$com_class_code',";
        $_query .= " '$order_sheet_flag','$stock_flag',";
        $_query .= "$gentanka,$raitanka,'$jancd','$note_n')";
        
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