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
 *    商品参照クラス - CommodityReferenceClass.php
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
    // マスター結果表示数
    var $master_display_lines;
    // マスター参照結果
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
     *  メイン処理
     *
     */
    function performReference()
    {
        if (isset($_REQUEST['item_letter'])){

            //
            // 「あ行」とか文字が選択された。
            //
            $letter = $_REQUEST['item_letter'];
            $this->displayForm($this->queryCommodity($letter));

        } else if (isset($_REQUEST['prev'])){

            // 現在表示されている先頭取引先のコードと先頭文字はセッションに保存されている。
            $this->displayForm($this->prevCommodity());


        } else if (isset($_REQUEST['next'])){

            // 現在表示されている先頭取引先のコードと先頭文字はセッションに保存されている。
            $this->displayForm($this->nextCommodity());

        } else if (isset($_REQUEST['select'])){

            $this->commodity_code =  $_REQUEST['commodity_code'];
            
            if(isset($this->commodity_code))
            {
              // 選択されました。
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

            // キャンセルされた。セッションはクリア。
            unset($this->commodity_code);
            unset($this->commodity_name);
            unset($_SESSION['is_commodity_ref']);
        }
        else
        {
            //
            // 初回GET時
            //
            $this->displayForm($_POST);
        }
   }

   /*
    *   パラメータで「あ行」とか渡される。「ア~オ」に展開してSQL問い合わせ。
    *
    */
   function queryCommodity($letter_from)
   {
       // 
       $where = OlutApp::translateWhere($letter_from);
       $sql = "select code,name from m_commodity where $where and deleted is null order by name_k,code limit $this->master_display_lines";
       if($this->sql->query($sql,SQL_ALL) == true)
       {
           // セッションに保存。
           $_SESSION['letter_from'] = $letter_from;
           $_SESSION['offset'] = 0;       // 現在のオフセットを保存する。
       }
       
       return $this->sql->record;
   }
   
   /*
    *   次ボタン処理。
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
           // セッションに保存。
           $_SESSION['letter_from'] = $letter_from;
           $_SESSION['offset'] = $offset;       // 現在のオフセットを保存する。           
       }
       return $this->sql->record;;
   }
   
   /*
    *   前ボタン処理
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
           // セッションに保存。
           $_SESSION['letter_from'] = $letter_from;
           $_SESSION['offset'] = $offset;       // 現在のオフセットを保存する。           
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

