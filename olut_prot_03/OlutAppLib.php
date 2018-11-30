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
 *    アプリケーションベース - OlutAppLib.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once('SystemProfile.php');

class OlutApp
{
    /*
    *  yyyy/mm/dd または yyyymmdd 形式でチェック。
    */

    function checkDate($dt)
    {
        if(substr($dt,4,1)=='/')
        {
            $year = substr($dt,0,4);
            $month = substr($dt,5,2);
            $date  = substr($dt,8,2);
        }
        else 
        {
            $year = substr($dt,0,4);
            $month = substr($dt,4,2);
            $date  = substr($dt,6,2);
        }

        return checkdate($month,$date,$year);
    }

    /*
    *    YYYYMMDD => YYYY/MM/DD に変換します。
    */

    function formatDate($dt)
    {
        if(strlen($dt) == 8)
        {
            return substr($dt,0,4) . '/' . substr($dt,4,2) . '/' . substr($dt,6,2);
        }
        return "2000/01/01";
    }

    /*
    *  数値カラム用にゼロをパッドする。
    */

    function paddZero($val)
    {
        if(strlen($val) == 0)
        {
            return "0";
        }
        return $val;
    }

    /*
    *
    */

    function formatMoney($val,$nsize)
    {
        $str = number_format($val);
        if(strlen($str) < $nsize)
        {
            $str = str_pad($str,$nsize-strlen($str)," ", STR_PAD_LEFT);
        }
        return $str;
    }

    /*
    *
    */

    function formatNumber($val,$nsize,$dec=0)
    {
        if(!isset($val))
        {
            $val = 0;
        }
        $str = number_format($val,$dec);
        $str = str_pad($str,$nsize," ",STR_PAD_LEFT);
        return $str;
    }


    /*
    *  月と年から次の月をyyyy/mm/01 形式で返す。
    */
    function getFirstDayOfNextMonth($year,$month)
    {
        $nextmonth = mktime (0,0,0,$month+1,1,$year);
        $year = date('Y',$nextmonth);
        $month = date('m',$nextmonth);

        return "$year/$month/01";
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
    /*
    *  前月を得る。
    */

    function getPrevMonth($dd)  // expect "yyyy/mm/dd' format
    {
        $year = substr($dd,0,4);
        $month = substr($dd,5,2);
        $date = substr($dd,8,2);
        $prev = mktime (0,0,0,$month-1,$date,$year);
        $year = date('Y',$prev);
        $month = date('m',$prev);
        $date  = date('d',$prev);

        return "$year/$month/$date";
    }
    /*
    *  月の最後の日を得る。
    */

    function getLastDate($year,$month)
    {
        $lday = 31;
        while(!checkdate($month, $lday, $year)) {
            $lday--;
        }
        return $lday;
    }

    /*
    *  本日から3ヶ月後を返す。(プロファイル設定に変更）
    */
    function getCloseDate()
    {
        $system_profile =& new SystemProfile();
        
        $year = date('Y',$prev);
        $month = date('m',$prev);
        $date  = date('d',$prev);

        $close_date = mktime (0,0,0,$month+$system_profile->show_after_close,$date,$year);
        $year  = date('Y',$close_date);
        $month = date('m',$close_date);
        $date  = date('d',$close_date);

        return "$year/$month/$date";
    }

    /*
    *  次の日。
    */
    function getNextDate($year,$month,$date)
    {

        $next_date = mktime (0,0,0,$month,$date+1,$year);
        $year  = date('Y',$next_date);
        $month = date('m',$next_date);
        $date  = date('d',$next_date);

        return "$year/$month/$date";
    }

    /*
    *  前日。
    */
    function getPreviousDate($year,$month,$date)
    {
        $prev_date = mktime (0,0,0,$month,$date-1,$year);
        $year  = date('Y',$prev_date);
        $month = date('m',$prev_date);
        $date  = date('d',$prev_date);

        return "$year/$month/$date";
    }

    function getCurrentProcessDate($db,&$year,&$month)
    {
        if(!$db->query("select process_target from m_calendar",SQL_INIT) || $db->record == null)
        {
            $year = date('Y');
            $month = date('m');
        }
        else
        {
            // yyyy-mm
            // 012345
            $year = substr($db->record[0],0,4);
            $month = substr($db->record[0],5,2);
        }
    }
    /*
    *  マスター参照系用。
    */
    function translateWhere($letter_from)
    {
        if(!strncmp($letter_from, 'あ',2))
        {
            return "name_k >= 'ア' and name_k < 'カ'";
        }

        if(!strncmp($letter_from, 'か',2))
        {
            return "name_k >= 'カ' and name_k < 'サ'";
        }

        if(!strncmp($letter_from, 'さ',2))
        {
            return "name_k >= 'サ' and name_k < 'タ'";
        }

        if(!strncmp($letter_from, 'た',2))
        {
            return "name_k >= 'タ' and name_k < 'ナ'";
        }

        if(!strncmp($letter_from, 'な',2))
        {
            return "name_k >= 'ナ' and name_k < 'ハ'";
        }

        if(!strncmp($letter_from, 'は',2))
        {
            return "name_k >= 'ハ' and name_k < 'マ'";
        }

        if(!strncmp($letter_from, 'ま',2))
        {
            return "name_k >= 'マ' and name_k < 'ヤ'";
        }

        if(!strncmp($letter_from, 'ら',2))
        {
            return "name_k >= 'ラ' and name_k < 'ワ'";
        }

        if(!strncmp($letter_from, 'わ',2))
        {
            return "name_k >= 'ワ'";
        }

        return "";
    }
}

?>