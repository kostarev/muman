/*
JQuery Ajax отправка форм и ссылок с классом "ajax"
Возвращаемые сервером данные обрабатываются как javascript
Автор: V. Kostarev
http://scades.ru
 */

jQuery(function() {
    //отправка формы ctrl+enter
    $('form.ajax').on('keypress',function(e){
        if((e.which == 13||e.which == 10) && e.ctrlKey){
            if(this.id==''){
                $(this).attr('id', 'ajaxForm'+Math.floor(Math.random()*1001));
            }
       
            var action = $(this).attr('action');
            var post = "ajax=script"; 
            $('#'+this.id+' input[name],textarea[name],select[name]').each(function(){
                if(($(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio') || $(this).attr('checked')=='checked'){		
                    post = post + "&" + encodeURIComponent(this.name) + "=" + encodeURIComponent($(this).val());
                }
            });
    
            $.ajax({
                type: "POST",
                url: action,
                data: post,
                dataType: "script",
                success: function(msg){                   
                // alert(msg);
                }
            });
     
            $(this)[0].reset();     
            return false;
        }
    });
    //Отправка формы по нажатию кнопки
    $('form.ajax').submit(function(){
        if(this.id==''){
            $(this).attr('id', 'ajaxForm'+Math.floor(Math.random()*1001));
        }
            
        var action = $(this).attr('action');
        var post = "ajax=script"; 
        $('#'+this.id+' input[name],textarea[name],select[name]').each(function(){
            if(($(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio') || $(this).attr('checked')=='checked'){		
                post = post + "&" + encodeURIComponent(this.name) + "=" + encodeURIComponent($(this).val());
            }
        });
    
        $.ajax({
            type: "POST",
            url: action,
            data: post,
            dataType: "script",
            success: function(msg){                   
            // alert(msg);
            }
        });   
        $(this)[0].reset();     
        return false;
    });
     
      
    //Ajax ссылка
    $('a.ajax').on('click',function(){
        if(this.id==''){
            $(this).attr('id', 'ajaxLink'+Math.floor(Math.random()*1001));
        }
      
        var action = $(this).attr('href');
        action = action + '&ajax=script';
           
        $.ajax({
            type: "get",
            url: action,                   
            dataType: "script",
            success: function(msg){
             //alert(msg);
            }
        });   
        return false;
    });
//------------------------------
});