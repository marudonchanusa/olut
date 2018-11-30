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
 *    ���ץꥱ�������١��� - OlutAppLib.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once('SystemProfile.php');

class OlutApp
{
    /*
    *  yyyy/mm/dd �ޤ��� yyyymmdd �����ǥ����å���
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
    *    YYYYMMDD => YYYY/MM/DD ���Ѵ����ޤ���
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
    *  ���ͥ�����Ѥ˥����ѥåɤ��롣
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
    *  ���ǯ���鼡�η��yyyy/mm/01 �������֤���
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
    *  ��������롣
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
    *  ��κǸ���������롣
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
    *  ��������3�������֤���(�ץ�ե�����������ѹ���
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
    *  ��������
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
    *  ������
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
    *  �ޥ��������ȷ��ѡ�
    */
    function translateWhere($letter_from)
    {
        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��' and name_k < '��'";
        }

        if(!strncmp($letter_from, '��',2))
        {
            return "name_k >= '��'";
        }

        return "";
    }
}

?>