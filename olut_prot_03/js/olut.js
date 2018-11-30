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
  if( event.keyCode<48 || event.keyCode>57) event.returnValue=false;
}

function set_readonly(b)
{
  document.All['id'].readonly = b;
  alert(b);
}

// end of olut.js

