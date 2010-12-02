var f50Ary=new Array();

function f50InitTableHeader(id){
var f50args=f50InitTableHeader.arguments;
var f500,f50t;
for (f500=0;f500<f50args.length;f500++){
f50t=document.getElementById(f50args[f500]);
f50ttrs=f50t.getElementsByTagName('TR');
f50t.trs=[];
for (f501=0;f501<f50ttrs.length;f501++){
f50t.trs.push(f50ttrs[f501]);
}
f50t.hh=f50t.trs[0].offsetHeight;
f50t.trs[0].pn=f50t;
f50Ary.push(f50t);
}
}

function f50CkScroll(){
var f50t,f50o,f501;
if (document.all){ f50t=document.documentElement.scrollTop; }
else if (document.getElementById){ f50t=window.pageYOffset; }
for (f500=0;f500<f50Ary.length;f500++){
if (f50Pos(f50Ary[f500])-f50Ary[f500].hh*2<f50t){
for (f501=1;f501<f50Ary[f500].trs.length;f501++){
if ((f50Pos(f50Ary[f500].trs[f501]))>(f50t+f50Ary[f500].hh)){
if (f50Ary[f500].trs[0].pn==f50Ary[f500]){ f50Ary[f500].trs[0].parentNode.removeChild(f50Ary[f500].trs[0]); }
f50Ary[f500].insertBefore(f50Ary[f500].trs[0],f50Ary[f500].trs[f501]);
break;
}
}
}
}
}

function f50Pos(f50){
f50ObjTop = f50.offsetTop;
while(f50.offsetParent!=null){
f50ObjParent=f50.offsetParent;
f50ObjTop+=f50ObjParent.offsetTop;
f50=f50ObjParent;
}
return f50ObjTop;
}

window.onscroll=f50CkScroll;

