/* jquery.form.js */
(function($){$.fn.ajaxSubmit=function(options){if(typeof options=='function')options={success:options};options=$.extend({url:this.attr('action')||window.location.toString(),type:this.attr('method')||'GET'},options||{});var veto={};this.trigger('form-pre-serialize',[this,options,veto]);if(veto.veto)return this;var a=this.formToArray(options.semantic);if(options.data){for(var n in options.data)a.push({name:n,value:options.data[n]});}if(options.beforeSubmit&&options.beforeSubmit(a,this,options)===false)return this;this.trigger('form-submit-validate',[a,this,options,veto]);if(veto.veto)return this;var q=$.param(a);if(options.type.toUpperCase()=='GET'){options.url+=(options.url.indexOf('?')>=0?'&':'?')+q;options.data=null;}else
options.data=q;var $form=this,callbacks=[];if(options.resetForm)callbacks.push(function(){$form.resetForm();});if(options.clearForm)callbacks.push(function(){$form.clearForm();});if(!options.dataType&&options.target){var oldSuccess=options.success||function(){};callbacks.push(function(data){$(options.target).html(data).each(oldSuccess,arguments);});}else if(options.success)callbacks.push(options.success);options.success=function(data,status){for(var i=0,max=callbacks.length;i<max;i++)callbacks[i](data,status,$form);};var files=$('input:file',this).fieldValue();var found=false;for(var j=0;j<files.length;j++)if(files[j])found=true;if(options.iframe||found){if($.browser.safari&&options.closeKeepAlive)$.get(options.closeKeepAlive,fileUpload);else
fileUpload();}else
$.ajax(options);this.trigger('form-submit-notify',[this,options]);return this;function fileUpload(){var form=$form[0];var opts=$.extend({},$.ajaxSettings,options);var id='jqFormIO'+(new Date().getTime());var $io=$('<iframe id="'+id+'" name="'+id+'" />');var io=$io[0];var op8=$.browser.opera&&window.opera.version()<9;if($.browser.msie||op8)io.src='javascript:false;document.write("");';$io.css({position:'absolute',top:'-1000px',left:'-1000px'});var xhr={responseText:null,responseXML:null,status:0,statusText:'n/a',getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){}};var g=opts.global;if(g&&!$.active++)$.event.trigger("ajaxStart");if(g)$.event.trigger("ajaxSend",[xhr,opts]);var cbInvoked=0;var timedOut=0;setTimeout(function(){var encAttr=form.encoding?'encoding':'enctype';var t=$form.attr('target'),a=$form.attr('action');$form.attr({target:id,method:'POST',action:opts.url});form[encAttr]='multipart/form-data';if(opts.timeout)setTimeout(function(){timedOut=true;cb();},opts.timeout);$io.appendTo('body');io.attachEvent?io.attachEvent('onload',cb):io.addEventListener('load',cb,false);form.submit();$form.attr({action:a,target:t});},10);function cb(){if(cbInvoked++)return;io.detachEvent?io.detachEvent('onload',cb):io.removeEventListener('load',cb,false);var ok=true;try{if(timedOut)throw'timeout';var data,doc;doc=io.contentWindow?io.contentWindow.document:io.contentDocument?io.contentDocument:io.document;xhr.responseText=doc.body?doc.body.innerHTML:null;xhr.responseXML=doc.XMLDocument?doc.XMLDocument:doc;xhr.getResponseHeader=function(header){var headers={'content-type':opts.dataType};return headers[header];};if(opts.dataType=='json'||opts.dataType=='script'){var ta=doc.getElementsByTagName('textarea')[0];xhr.responseText=ta?ta.value:xhr.responseText;}else if(opts.dataType=='xml'&&!xhr.responseXML&&xhr.responseText!=null){xhr.responseXML=toXml(xhr.responseText);}data=$.httpData(xhr,opts.dataType);}catch(e){ok=false;$.handleError(opts,xhr,'error',e);}if(ok){opts.success(data,'success');if(g)$.event.trigger("ajaxSuccess",[xhr,opts]);}if(g)$.event.trigger("ajaxComplete",[xhr,opts]);if(g&&!--$.active)$.event.trigger("ajaxStop");if(opts.complete)opts.complete(xhr,ok?'success':'error');setTimeout(function(){$io.remove();xhr.responseXML=null;},100);};function toXml(s,doc){if(window.ActiveXObject){doc=new ActiveXObject('Microsoft.XMLDOM');doc.async='false';doc.loadXML(s);}else
doc=(new DOMParser()).parseFromString(s,'text/xml');return(doc&&doc.documentElement&&doc.documentElement.tagName!='parsererror')?doc:null;};};};$.fn.ajaxForm=function(options){return this.ajaxFormUnbind().bind('submit.form-plugin',function(){$(this).ajaxSubmit(options);return false;}).each(function(){$(":submit,input:image",this).bind('click.form-plugin',function(e){var $form=this.form;$form.clk=this;if(this.type=='image'){if(e.offsetX!=undefined){$form.clk_x=e.offsetX;$form.clk_y=e.offsetY;}else if(typeof $.fn.offset=='function'){var offset=$(this).offset();$form.clk_x=e.pageX-offset.left;$form.clk_y=e.pageY-offset.top;}else{$form.clk_x=e.pageX-this.offsetLeft;$form.clk_y=e.pageY-this.offsetTop;}}setTimeout(function(){$form.clk=$form.clk_x=$form.clk_y=null;},10);});});};$.fn.ajaxFormUnbind=function(){this.unbind('submit.form-plugin');return this.each(function(){$(":submit,input:image",this).unbind('click.form-plugin');});};$.fn.formToArray=function(semantic){var a=[];if(this.length==0)return a;var form=this[0];var els=semantic?form.getElementsByTagName('*'):form.elements;if(!els)return a;for(var i=0,max=els.length;i<max;i++){var el=els[i];var n=el.name;if(!n)continue;if(semantic&&form.clk&&el.type=="image"){if(!el.disabled&&form.clk==el)a.push({name:n+'.x',value:form.clk_x},{name:n+'.y',value:form.clk_y});continue;}var v=$.fieldValue(el,true);if(v&&v.constructor==Array){for(var j=0,jmax=v.length;j<jmax;j++)a.push({name:n,value:v[j]});}else if(v!==null&&typeof v!='undefined')a.push({name:n,value:v});}if(!semantic&&form.clk){var inputs=form.getElementsByTagName("input");for(var i=0,max=inputs.length;i<max;i++){var input=inputs[i];var n=input.name;if(n&&!input.disabled&&input.type=="image"&&form.clk==input)a.push({name:n+'.x',value:form.clk_x},{name:n+'.y',value:form.clk_y});}}return a;};$.fn.formSerialize=function(semantic){return $.param(this.formToArray(semantic));};$.fn.fieldSerialize=function(successful){var a=[];this.each(function(){var n=this.name;if(!n)return;var v=$.fieldValue(this,successful);if(v&&v.constructor==Array){for(var i=0,max=v.length;i<max;i++)a.push({name:n,value:v[i]});}else if(v!==null&&typeof v!='undefined')a.push({name:this.name,value:v});});return $.param(a);};$.fn.fieldValue=function(successful){for(var val=[],i=0,max=this.length;i<max;i++){var el=this[i];var v=$.fieldValue(el,successful);if(v===null||typeof v=='undefined'||(v.constructor==Array&&!v.length))continue;v.constructor==Array?$.merge(val,v):val.push(v);}return val;};$.fieldValue=function(el,successful){var n=el.name,t=el.type,tag=el.tagName.toLowerCase();if(typeof successful=='undefined')successful=true;if(successful&&(!n||el.disabled||t=='reset'||t=='button'||(t=='checkbox'||t=='radio')&&!el.checked||(t=='submit'||t=='image')&&el.form&&el.form.clk!=el||tag=='select'&&el.selectedIndex==-1))return null;if(tag=='select'){var index=el.selectedIndex;if(index<0)return null;var a=[],ops=el.options;var one=(t=='select-one');var max=(one?index+1:ops.length);for(var i=(one?index:0);i<max;i++){var op=ops[i];if(op.selected){var v=$.browser.msie&&!(op.attributes['value'].specified)?op.text:op.value;if(one)return v;a.push(v);}}return a;}return el.value;};$.fn.clearForm=function(){return this.each(function(){$('input,select,textarea',this).clearFields();});};$.fn.clearFields=$.fn.clearInputs=function(){return this.each(function(){var t=this.type,tag=this.tagName.toLowerCase();if(t=='text'||t=='password'||tag=='textarea')this.value='';else if(t=='checkbox'||t=='radio')this.checked=false;else if(tag=='select')this.selectedIndex=-1;});};$.fn.resetForm=function(){return this.each(function(){if(typeof this.reset=='function'||(typeof this.reset=='object'&&!this.reset.nodeType))this.reset();});};$.fn.enable=function(b){if(b==undefined)b=true;return this.each(function(){this.disabled=!b});};$.fn.select=function(select){if(select==undefined)select=true;return this.each(function(){var t=this.type;if(t=='checkbox'||t=='radio')this.checked=select;else if(this.tagName.toLowerCase()=='option'){var $sel=$(this).parent('select');if(select&&$sel[0]&&$sel[0].type=='select-one'){$sel.find('option').select(false);}this.selected=select;}});};})(jQuery);

/* jquery.ufvalidator.js */
(function($){$.fn.formValidator=function(options){$(this).click(function(){var result=$.formValidator(options);if(result&&jQuery.isFunction(options.onSuccess)){options.onSuccess();return false;}else if(!result&&jQuery.isFunction(options.onError)){options.onError();return false;}else{return result;}});};$.formValidator=function(options){var merged_options=$.extend({},$.formValidator.defaults,options);var boolValid=true;var errorMsg='';var multiErrorMsg=new Array();var multiOutput='';$(merged_options.scope+' .error-both, '+merged_options.scope+' .error-same, '+merged_options.scope+' .error-input').removeClass('error-both').removeClass('error-same').removeClass('error-input');$(merged_options.scope+' .req-email, '+merged_options.scope+' .req-string, '+merged_options.scope+' .req-same, '+merged_options.scope+' .req-both, '+merged_options.scope+' .req-numeric, '+merged_options.scope+' .req-date, '+merged_options.scope+' .req-min').each(function(){thisValid=$.formValidator.validate($(this),merged_options);boolValid=boolValid&&thisValid.error;if(!thisValid.error)errorMsg=thisValid.message;if(thisValid.message!='')multiErrorMsg[multiErrorMsg.length]=thisValid.message;});multiErrorMsg=array_unique(multiErrorMsg);for(id in multiErrorMsg){multiOutput+=' - '+multiErrorMsg[id]+'<br />';}if(!merged_options.extraBool()&&boolValid){boolValid=false;errorMsg=merged_options.extraBoolMsg;}if((merged_options.scope!='')&&boolValid){$(merged_options.errorDiv).fadeOut();}if(!boolValid&&errorMsg!=''){var tempErr=(merged_options.customErrMsg!='')?merged_options.customErrMsg:errorMsg;errorShow(multiOutput);}return boolValid;};$.formValidator.validate=function(obj,opts){var valAttr=obj.val();var css=opts.errorClass;var mail_filter=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;var url_filter=/^https?:\/\/[a-z0-9-]{2,63}(?:\.[a-z0-9-]{2,})*(?::[0-9]{0,5})?(?:\/|$)\S*$/;var numeric_filter=/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)|(^-?\d*$)/;var phone_filter=/[0-9]+/;var tmpresult=true;var result=true;var errorTxt='';if(obj.hasClass('req-string')){tmpresult=(valAttr!='');if(!tmpresult)errorTxt=opts.errorMsg.reqString;result=result&&tmpresult;}if(obj.hasClass('req-same')){tmpresult=true;group=obj.attr('rel');tmpresult=true;$(opts.scope+' .req-same[rel="'+group+'"]').each(function(){if($(this).val()!=valAttr||valAttr==''){tmpresult=false;}});if(!tmpresult){$(opts.scope+' .req-same[rel="'+group+'"]').parent().parent().addClass('error-same');errorTxt=opts.errorMsg.reqSame;}else{$(opts.scope+' .req-same[rel="'+group+'"]').parent().parent().removeClass('error-same');}result=result&&tmpresult;}if(obj.hasClass('req-both')){tmpresult=true;if(valAttr!=''){group=obj.attr('rel');$(opts.scope+' .req-both[rel="'+group+'"]').each(function(){if($(this).val()==''){tmpresult=false;}});if(!tmpresult){$(opts.scope+' .req-both[rel="'+group+'"]').parent().parent().addClass('error-both');errorTxt=opts.errorMsg.reqBoth;}else{$(opts.scope+' .req-both[rel="'+group+'"]').parent().parent().removeClass('error-both');}}result=result&&tmpresult;}if(obj.hasClass('req-email')){if(!obj.hasClass('req-string')&&valAttr==''){result=true;}else
{tmpresult=mail_filter.test(valAttr);if(!tmpresult)errorTxt=(valAttr=='')?opts.errorMsg.reqMailEmpty:opts.errorMsg.reqMailNotValid;result=result&&tmpresult;}}if(obj.hasClass('req-url')){if(!obj.hasClass('req-string')&&valAttr==''){result=true;}else
{tmpresult=url_filter.test(valAttr);if(!tmpresult)errorTxt=(valAttr=='')?opts.errorMsg.reqUrlEmpty:opts.errorMsg.reqUrlNotValid;result=result&&tmpresult;}}if(obj.hasClass('req-phone')){tmpresult=phone_filter.test(valAttr);if(!tmpresult)errorTxt=(valAttr=='')?opts.errorMsg.reqPhoneEmpty:opts.errorMsg.reqPhoneNotValid;result=result&&tmpresult;}if(obj.hasClass('req-date')){tmpresult=true;var arr=valAttr.split(opts.dateSeperator);var curDate=new Date();if(valAttr==''){tmpresult=true;}else{if(arr.length<3){tmpresult=false;}else{tmpresult=(arr[2]<=curDate.getFullYear()+3)&&(arr[1]<=12)&&(arr[0]<=31);}}if(!tmpresult)errorTxt=opts.errorMsg.reqDate;result=result&&tmpresult;}if(obj.hasClass('req-min')){tmpresult=(valAttr.length>=obj.attr('minlength'));if(!tmpresult)errorTxt=opts.errorMsg.reqMin.replace('%1',obj.attr('minlength'));result=result&&tmpresult;}if(obj.hasClass('req-numeric')){tmpresult=numeric_filter.test(valAttr);if(!tmpresult)errorTxt=opts.errorMsg.reqNum;result=result&&tmpresult;}if(obj.attr('rel')){if(result){$('#'+obj.attr('rel')).removeClass(css);}else{$('#'+obj.attr('rel')).addClass(css);}}else{if(result){obj.removeClass(css);}else{obj.addClass(css);}}return{error:result,message:errorTxt};};$.formValidator.defaults={onSuccess:null,onError:null,scope:'',errorClass:'error-input',errorDiv:'#warn',errorMsg:{reqString:'Fill in the required fields',reqDate:'Not valid date',reqNum:'You can only enter numbers',reqMailNotValid:'Not valid E-mail',reqMailEmpty:'Enter your E-mail',reqUrlNotValid:'Not valid Url',reqUrlEmpty:'Enter your Url',reqPhoneNotValid:'Not valid Phone number',reqPhoneEmpty:'Enter your Phone number',reqSame:'Not repeat the same',reqBoth:'You must fill in the appropriate fields!',reqMin:'You must enter a minimum of %1 characters'},customErrMsg:'',extraBoolMsg:'Check your form carefully!',dateSeperator:'-',extraBool:function(){return true;}};})(jQuery);function array_unique(inputArr){var key='',tmp_arr2={},val='';var __array_search=function(needle,haystack){var fkey='';for(fkey in haystack){if(haystack.hasOwnProperty(fkey)){if((haystack[fkey]+'')===(needle+'')){return fkey;}}}return false;};for(key in inputArr){if(inputArr.hasOwnProperty(key)){val=inputArr[key];if(false===__array_search(val,tmp_arr2)){tmp_arr2[key]=val;}}}return tmp_arr2;}

/* date.js */
function date(format,timestamp){var that=this,jsdate,f,formatChr=/\\?([a-z])/gi,formatChrCb,_pad=function(n,c){if((n=n+"").length<c){return new Array((++c)-n.length).join("0")+n;}else{return n;}},txt_words=["Sun","Mon","Tues","Wednes","Thurs","Fri","Satur","January","February","March","April","May","June","July","August","September","October","November","December"],txt_ordin={1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};formatChrCb=function(t,s){return f[t]?f[t]():s;};f={d:function(){return _pad(f.j(),2);},D:function(){return f.l().slice(0,3);},j:function(){return jsdate.getDate();},l:function(){return txt_words[f.w()]+'day';},N:function(){return f.w()||7;},S:function(){return txt_ordin[f.j()]||'th';},w:function(){return jsdate.getDay();},z:function(){var a=new Date(f.Y(),f.n()-1,f.j()),b=new Date(f.Y(),0,1);return Math.round((a-b)/864e5)+1;},W:function(){var a=new Date(f.Y(),f.n()-1,f.j()-f.N()+3),b=new Date(a.getFullYear(),0,4);return 1+Math.round((a-b)/864e5/7);},F:function(){return txt_words[6+f.n()];},m:function(){return _pad(f.n(),2);},M:function(){return f.F().slice(0,3);},n:function(){return jsdate.getMonth()+1;},t:function(){return(new Date(f.Y(),f.n(),0)).getDate();},L:function(){var y=f.Y(),a=y&3,b=y%4e2,c=y%1e2;return 0+(!a&&(c||!b));},o:function(){var n=f.n(),W=f.W(),Y=f.Y();return Y+(n===12&&W<9?-1:n===1&&W>9);},Y:function(){return jsdate.getFullYear();},y:function(){return(f.Y()+"").slice(-2);},a:function(){return jsdate.getHours()>11?"pm":"am";},A:function(){return f.a().toUpperCase();},B:function(){var H=jsdate.getUTCHours()*36e2,i=jsdate.getUTCMinutes()*60,s=jsdate.getUTCSeconds();return _pad(Math.floor((H+i+s+36e2)/86.4)%1e3,3);},g:function(){return f.G()%12||12;},G:function(){return jsdate.getHours();},h:function(){return _pad(f.g(),2);},H:function(){return _pad(f.G(),2);},i:function(){return _pad(jsdate.getMinutes(),2);},s:function(){return _pad(jsdate.getSeconds(),2);},u:function(){return _pad(jsdate.getMilliseconds()*1000,6);},e:function(){return'UTC';},I:function(){var a=new Date(f.Y(),0),c=Date.UTC(f.Y(),0),b=new Date(f.Y(),6),d=Date.UTC(f.Y(),6);return 0+((a-c)!==(b-d));},O:function(){var a=jsdate.getTimezoneOffset();return(a>0?"-":"+")+_pad(Math.abs(a/60*100),4);},P:function(){var O=f.O();return(O.substr(0,3)+":"+O.substr(3,2));},T:function(){return'UTC';},Z:function(){return-jsdate.getTimezoneOffset()*60;},c:function(){return'Y-m-d\\Th:i:sP'.replace(formatChr,formatChrCb);},r:function(){return'D, d M Y H:i:s O'.replace(formatChr,formatChrCb);},U:function(){return jsdate.getTime()/1000|0;}};this.date=function(format,timestamp){that=this;jsdate=((typeof timestamp==='undefined')?new Date():(timestamp instanceof Date)?new Date(timestamp):new Date(timestamp*1000));return format.replace(formatChr,formatChrCb);};return this.date(format,timestamp);}function in_array(needle,haystack,argStrict){var key='',strict=!!argStrict;if(strict){for(key in haystack){if(haystack[key]===needle){return true;}}}else{for(key in haystack){if(haystack[key]==needle){return true;}}}return false;}