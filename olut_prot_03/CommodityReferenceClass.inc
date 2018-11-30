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
 * ���Υץ������ϥե꡼���եȥ������Ǥ������ʤ��Ϥ���򡢥ե꡼���եȥ���
 * �����Ĥˤ�ä�ȯ�Ԥ��줿 GNU ���̸������ѵ��������(�С������2������
 * ˾�ˤ�äƤϤ���ʹߤΥС������Τ����ɤ줫)��������β��Ǻ�����
 * �ޤ��ϲ��Ѥ��뤳�Ȥ��Ǥ��ޤ���
 *
 * ���Υץ�������ͭ�ѤǤ��뤳�Ȥ��ä����ۤ���ޤ�����*������̵�ݾ�* 
 * �Ǥ������Ȳ�ǽ�����ݾڤ��������Ū�ؤ�Ŭ�����ϡ������˼����줿��Τ��
 * ������¸�ߤ��ޤ��󡣾ܤ�����GNU ���̸������ѵ���������������������
 *
 * ���ʤ��Ϥ��Υץ������ȶ��ˡ�GNU ���̸������ѵ���������ʣ��ʪ�����
 * ������ä��Ϥ��Ǥ����⤷������äƤ��ʤ���С��ե꡼���եȥ��������Ĥ�
 * �����ᤷ�Ƥ�������(����� the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA)��
 *
 *   Program name:
 *    ���ʻ��ȥ��饹 - CommodityReferenceClass.php
 *
 *   Release History:
 *    2005/9/30  ver 1.00.00 Initial Release
 *
 */

require_once('OlutAppLib.php');
  
class CommodityReference extends OlutApp
{
    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;
    // �ޥ��������ɽ����
    var $master_display_lines;
    // �ޥ��������ȷ��
    var $commodity_code       = null;
    var $commodity_name       = null;
    var $commodity_unit_price = null;
    var $commodity_unit_name  = null;
    
    /**
     * class constructor
     */
    function CommodityReference()
    {
        // instantiate the sql object
        $this->sql =& new CommodityReference_SQL;

        // instantiate the template object
        $this->tpl =& new CommodityReference_Smarty;
        
        //
        $this->master_display_lines = $_SESSION['screen_lines'] * 2;
    }


    /*
     *  �ᥤ�����
     *
     */
    function performReference()
    {
        if (isset($_REQUEST['item_letter'])){

            //
            // �֤��ԡפȤ�ʸ�������򤵤줿��
            //
            $letter = $_REQUEST['item_letter'];
            $this->displayForm($this->queryCommodity($letter));

        } else if (isset($_REQUEST['prev'])){

            // ����ɽ������Ƥ�����Ƭ�����Υ����ɤ���Ƭʸ���ϥ��å�������¸����Ƥ��롣
            $this->displayForm($this->prevCommodity());


        } else if (isset($_REQUEST['next'])){

            // ����ɽ������Ƥ�����Ƭ�����Υ����ɤ���Ƭʸ���ϥ��å�������¸����Ƥ��롣
            $this->displayForm($this->nextCommodity());

        } else if (isset($_REQUEST['select'])){

            $this->commodity_code =  $_REQUEST['commodity_code'];
            
            if(isset($this->commodity_code))
            {
              // ���򤵤�ޤ�����
               $this->getCommodity($this->commodity_code);
            }
            else
            {
              unset($this->commodity_name);
              unset($this->commodity_unit_name);
              unset($this->commodity_unit_price);
            }
            unset($_SESSION['is_commodity_ref']);

        } else if (isset($_REQUEST['cancel'])){

            // ����󥻥뤵�줿�����å����ϥ��ꥢ��
            unset($this->commodity_code);
            unset($this->commodity_name);
            unset($_SESSION['is_commodity_ref']);
        }
        else
        {
            //
            // ���GET��
            //
            $this->displayForm($_POST);
        }
   }

   /*
    *   �ѥ�᡼���ǡ֤��ԡפȤ��Ϥ���롣�֥�������פ�Ÿ������SQL�䤤��碌��
    *
    */
   function queryCommodity($letter_from)
   {
       // 
       $where = OlutApp::translateWhere($letter_from);
       $sql = "select code,name from m_commodity where $where and deleted is null order by name_k,code limit $this->master_display_lines";
       if($this->sql->query($sql,SQL_ALL) == true)
       {
           // ���å�������¸��
           $_SESSION['letter_from'] = $letter_from;
           $_SESSION['offset'] = 0;       // ���ߤΥ��ե��åȤ���¸���롣
       }
       
       return $this->sql->record;
   }
   
   /*
    *   ���ܥ��������
    */
   function nextCommodity()
   {
       $offset = $_SESSION['offset'];
       $offset += $this->master_display_lines;
       $letter_from = $_SESSION['letter_from'];
       $where = OlutApp::translateWhere($letter_from);   // translate_where($letter_from);
       $sql = "select code,name from m_commodity where $where and deleted is null order by name_k,code offset $offset limit $this->master_display_lines";
       if($this->sql->query($sql,SQL_ALL,SQL_INDEX) == true)
       {
           // ���å�������¸��
           $_SESSION['letter_from'] = $letter_from;
           $_SESSION['offset'] = $offset;       // ���ߤΥ��ե��åȤ���¸���롣           
       }
       return $this->sql->record;;
   }
   
   /*
    *   ���ܥ������
    */
   function prevCommodity()
   {
       $offset = $_SESSION['offset'];
       $offset -= $this->master_display_lines;
       if($offset < $master_display_lines)
       {
           $offset = 0;
       }
       $letter_from = $_SESSION['letter_from'];
       $where = OlutApp::translateWhere($letter_from);  // translate_where($letter_from);
       $sql = "select code,name from m_commodity where $where and deleted is null order by name_k,code offset $offset limit $this->master_display_lines";
       if($this->sql->query($sql,SQL_ALL,SQL_INDEX)==true)
       {
           // ���å�������¸��
           $_SESSION['letter_from'] = $letter_from;
           $_SESSION['offset'] = $offset;       // ���ߤΥ��ե��åȤ���¸���롣           
       }
       return $this->sql->record;
   }
   
   function getCommodity($code)
   {
       $sql = sprintf("select c.name, u.name, c.current_unit_price from m_commodity c, m_unit u where c.code='%s' and c.unit_code=u.code",$code);
       
       //
       // print $sql;
       
       if($this->sql->query($sql,SQL_ALL,SQL_INDEX)==true)
       {
           $this->commodity_name = $this->sql->record[0][0];
           $this->commodity_unit_name = $this->sql->record[0][1];
           $this->commodity_unit_price = $this->sql->record[0][2];
           
           // return $this->sql->record[0][0];
       }
   }
    
    /**
     * display the vendor entry form
     *
     * @param array $formvars the form variables
     */
    function displayForm($formvars = array()) {

        // assign the form vars
        $this->tpl->assign('post',$formvars);
        $this->tpl->assign('master_display_lines', $this->master_display_lines);

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }else{
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('CommodityReferenceForm.tpl');
    }
}
?>
