// olut.js

//
// should be called from onkeypress event.
//
function digitonly()
{
  if( event.keyCode<48 || event.keyCode>57) event.returnValue=false;
}

function moneyonly()
{
  if( event.keyCode==45 ) return; // - sign.
  if( event.keyCode==46 ) return; // . dot.
  if( event.keyCode<48 || event.keyCode>57) event.returnValue=false;
}

function digit_and_sign()
{
  if( event.keyCode==45 ) return; // - sign.
  if( event.keyCode==9 ) return;
  if( event.keyCode<48 || event.keyCode>57) event.returnValue=false;
}

function set_readonly(b)
{
  document.All['id'].readonly = b;
  alert(b);
}

function new_print_window(url)
{
    window.open(url,'','toolbar=no,resizable=yes,menubar=no,scrollbars=yes');
    event.returnValue = false;
    return false;
}

function print_statement_of_delivery(form)
{
    var url;
    var code;
    
    code = form.slip_no.value;
    
    if(code != '')
    {
        //alert(code);
        url = "StatementOfDelivery.php?code=" + code;
        window.open(url,'','toolbar=no,resizable=yes,menubar=no,scrollbars=yes');
        event.returnValue = false;
    }
    return false;    
}

function do_login(form)
{
    window.open('OlutMainMenu.php','main_menu','resizable=yes,toolbar=no,menubar=no,scrollbars=yes');
    form.target = 'main_menu';
    form.submit();
    event.returnValue=false;
    //window.close();  // close myself
    // event.returnValue = false;
}

function blur_submit(form,txt)
{
    if(txt.value != '')
    {
        form.blur_flag.value = "1";
        form.submit();
    }
}

function blur_submit_for_ship(form,txt)
{
    if(txt.value == '')
    {
        form.blur_flag.value = "1";
        form.submit();
    }
}

function refer_store(form,store_code)
{
    var ajax;
    var url;
    var obj;
    
    if(store_code.value == "")
    {
        obj = document.getElementById("store_name"); //  All['store_name'];
        obj.innerHTML = "";
        return;
    }

    if(window.XMLHttpRequest) {
        ajax = new XMLHttpRequest();       // other than IE.
    }
    else if(window.ActiveXObject) 
    {
　　   try {
           ajax = new ActiveXObject("Msxml2.XMLHTTP");
　　　 } catch(e) {
           ajax = new ActiveXObject("Microsoft.XMLHTTP");
　　　 }
    }
    
    url = "GetStoreName.php?code=" + store_code.value;
    
    // alert(url);
    // code = 
    ajax.open("GET", url);

    ajax.onreadystatechange = function() {
      if (ajax.readyState == 4 && ajax.status == 200) {
          obj = document.getElementById("store_name"); //  All['store_name'];
          obj.innerHTML = ajax.responseText;
       }
    }
    ajax.send(null);    
    // ajax.send(null);
}

// added 2005/10/15
function check_menuback_for_ship(form)
{
    if( form.store_code.value != "" || form.commodity_code_0.value != "" || form.commodity_code_1.value != ""
     || form.commodity_code_2.value != "" || form.commodity_code_3.value != ""|| form.commodity_code_4.value != ""
     || form.commodity_code_5.value != "" )
    {
       if( confirm('メニューに戻って宜しいですか？') == false)
       {
           return false;
       }
    }
    
    // 
    form.action='OlutShipmentMenu.php';
    form.submit();
    
    return false;
}

// added 2005/10/15
function check_menuback_for_arrival(form)
{
    if( form.commodity_code_0.value != "" || form.commodity_code_1.value != ""
     || form.commodity_code_2.value != "" || form.commodity_code_3.value != "" || form.commodity_code_4.value != ""
     || form.commodity_code_5.value != "" || form.commodity_code_6.value != "" || form.commodity_code_7.value != ""
     || form.commodity_code_8.value != "" || form.commodity_code_9.value != "")
    {
        if( confirm('メニューに戻って宜しいですか？') == false)
        {
            return false;
        }
    }
    
    form.action='OlutArrivalOfGoodsMenu.php';
    form.submit();
    
    return false;    
}

// end of olut.js

