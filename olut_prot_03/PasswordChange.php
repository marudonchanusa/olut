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
 *    �ѥ�����ѹ���˥塼 -  PasswordChange.php
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
class PasswordChange_Smarty extends Smarty
{
    function PasswordChange_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

class PasswordChange
{
    var $user_id;
    var $password;
    var $screen_lines;

    function PasswordChange()
    {
        $this->screen_lines = $_SESSION['screen_lines'];

    }


    function renderScreen($formvars)
    {
        $this->tpl =& new PasswordChange_Smarty();

        $this->tpl->assign('user_id', $this->user_id);
        $this->tpl->assign('password',$this->password);
        $this->tpl->assign('screen_lines',$this->screen_lines);

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('PasswordChangeForm.tpl');
    }

    function updatePassword($formvars)
    {
        $new_password   = $formvars['new_password'];
        $verify_password = $formvars['verify_password'];
        $screen_lines    = $formvars['screen_lines'];

        if(strlen($new_password)==0)
        {
            $this->error = "���ѥ���ɤ����Ϥ��Ƥ�������";
            return false;
        }

        if(strlen($verify_password)==0)
        {
            $this->error = "��ǧ�ѥѥ���ɤ����Ϥ��Ƥ�������";
            return false;
        }

        if($new_password != $verify_password)
        {
            $this->error = "���Ϥ��줿��ǧ�ѥѥ���ɤ����פ��ޤ���";
            return false;
        }

        $pwd = md5($new_password);
        $uid = $_SESSION['user_id'];

        $sql =& new SQL();
        $sql->connect(OLUT_DSN);

        $_query = "update m_user set password='$pwd',screen_lines=$screen_lines,updated=now(),update_userid='$uid' where userid='$uid'"; 
        if(!$sql->query($_query))
        {
            $this->error = "�ǡ����١����ѹ����顼";
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
// password change main process....
//
//

$uid = $_SESSION['user_id'];
if($uid == null )
{
    header('Location: OlutMainMenu.php');
}

$pwc =& new PasswordChange();
//
if(isset($_POST['password_change']))
{
    if(!$pwc->updatePassword($_POST))
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
