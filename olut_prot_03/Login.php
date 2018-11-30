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
 *    ��������� - Login.php
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
class Login_Smarty extends Smarty
{
    function Login_Smarty()
    {
        $this->template_dir = OLUT_DIR . 'templates';
        $this->compile_dir = OLUT_DIR . 'templates_c';
        $this->config_dir = OLUT_DIR . 'configs';
        $this->cache_dir = OLUT_DIR . 'cache';
    }
}

class Login
{
    var $user_id;
    var $password;
    var $sql;
    var $tpl;
    var $error;

    function Login()
    {

    }

    function renderScreen($formvars)
    {
        $this->userid   = $formvars['user_id'];
        $this->password = $formvars['password'];

        $this->tpl =& new Login_Smarty();

        $this->tpl->assign('user_id', $this->user_id);
        $this->tpl->assign('password',$this->password);

        // assign error message
        if($this->sql->error != null){
            $this->tpl->assign('error', $this->sql->error);
        }
        else
        {
            $this->tpl->assign('error', $this->error);
        }
        $this->tpl->display('LoginForm.tpl');
    }

    function validateUser($formvars)
    {
        // debugging.....
        //return true;
        //
        if(isset($formvars['login']))
        {
            if(!$this->performLogin($formvars))
            {
                $this->renderScreen($formvars);
                return false;
            }
            return true;
        }
        else
        {
            //
            $current_user = $_SESSION['user_id'];
            if($current_user != null)
            {
                // already logged in.
                return true;
            }

            $this->renderScreen($formvars);
        }
        return false;
    }

    function performLogin($formvars)
    {
        $userid   = $formvars['user_id'];
        $password = $formvars['password'];

        if($userid == null || strlen($userid)==0)
        {
            $this->error = "�桼����ID����ꤷ�Ƥ�������";
            return false;
        }

        if($password == null || strlen($password)==0)
        {
            $this->error = "�ѥ���ɤ���ꤷ�Ƥ�������";
            return false;
        }

        $this->sql =& new SQL();
        $this->sql->connect(OLUT_DSN);

        $_query = "select password,screen_lines from m_user where userid='$userid'";
        if(!$this->sql->query($_query,SQL_INIT))
        {
            $this->error = "�ǡ����١����Υ��顼�Ǥ�";
            $this->sql->disconnect();
            return false;
        }

        //
        if($this->sql->record == null)
        {
            $this->error = "�桼������¸�ߤ��ޤ���";
            $this->sql->disconnect();
            return false;
        }

        //
        if(md5($password) != $this->sql->record[0])
        {
            $this->error = "�ѥ���ɤδְ㤤�Ǥ�";
            $this->sql->disconnect();
            return false;
        }

        // set to session
        $_SESSION['user_id'] = $userid;
        $_SESSION['screen_lines'] = $this->sql->record[1];

        $this->sql->disconnect();
        return true;
    }
}
?>