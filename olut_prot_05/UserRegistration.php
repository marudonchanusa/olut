<?php
/*
* Olut inventory management system
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
*    ユーザー登録 -  UserRegistration.php
*
*   Release History:
*    2005/9/30  ver 1.00.00 Initial Release
*
*/

session_start();
require_once('sys_define.inc');
require_once(OLUT_DIR . 'libs/sql.lib.php');
require_once(SMARTY_DIR . 'Smarty.class.php');
require_once('DB.php');         // PEAR DB


// smarty configuration
class UserRegistration_Smarty extends Smarty
{
    function PasswordChange_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

class UserRegistration
{
    var $new_userid;
    var $password;
    var $screen_lines;

    function UserRegistration()
    {
        $this->screen_lines = $_SESSION['screen_lines'];

    }


    function renderScreen($formvars)
    {
        $this->tpl =& new UserRegistration_Smarty();

        $this->tpl->assign('new_userid', $this->new_userid);
        $this->tpl->assign('screen_lines',$this->screen_lines);

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('UserRegistrationForm.tpl');
    }

    function addUser($formvars)
    {
        $this->new_userid      = $formvars['new_userid'];
        $new_password    = $formvars['new_password'];
        $verify_password = $formvars['verify_password'];
        $this->screen_lines    = $formvars['screen_lines'];

        if(strlen($this->new_userid) == 0)
        {
            $this->error = "新ユーザーＩＤを入力してください";
            return false;
        }

        if(strlen($new_password)==0)
        {
            $this->error = "新パスワードを入力してください";
            return false;
        }

        if(strlen($verify_password)==0)
        {
            $this->error = "確認用パスワードを入力してください";
            return false;
        }

        if($new_password != $verify_password)
        {
            $this->error = "入力された確認用パスワードが合致しません";
            return false;
        }
        
        if(strlen($this->screen_lines)==0)
        {
            $this->error = "画面行数を入力してください";
            return false;
        }

        $uid = $_SESSION['user_id'];
        $enc_pwd = md5($new_password);

        $sql =& new SQL();
        $sql->connect(OLUT_DSN);

        // 同じユーザーが無いことを確認。
        $_query = "select count(*) from m_user where userid='$this->new_userid'";
        if(!$sql->query($_query,SQL_INIT))
        {
            $this->error = "データベース参照エラー";
            $sql->disconnect();
            return false;
        }

        if($sql->record == null || $sql->record[0]>0)
        {
            $this->error = "ユーザーＩＤはすでに登録されています";
            $sql->disconnect();
            return false;
        }

        $_query = "insert into m_user (userid,password,screen_lines,updated,update_userid)";
        $_query .= " values ('$this->new_userid','$enc_pwd','$this->screen_lines',now(),'$uid')";

        if(!$sql->query($_query))
        {
            //print $_query;
            $this->error = "データベース変更エラー";
            $sql->disconnect();
            return false;
        }
        $sql->disconnect();

        // save to session
        $_SESSION['screen_lines'] = $screen_lines;
        return true;
    }
}

//
//  main process....
//
//

$uid = $_SESSION['user_id'];
if($uid == null )
{
    header('Location: OlutMainMenu.php');
}

$pwc =& new UserRegistration();
//
if(isset($_POST['password_change']))
{
    if(!$pwc->addUser($_POST))
    {
        // in order to show errors.
        $pwc->renderScreen($_POST);
    }
    else
    {
        header("Location: OlutMainMenu.php");
    }
}
else
{
    $pwc->renderScreen($_POST);
}
?>