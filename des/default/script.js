jQuery(function() {
    //Отправка форм по ctrl+enter    
    $('form[class!=ajax]').on('keypress',function(e){
        if((e.which == 13||e.which == 10) && e.ctrlKey){
            this.submit();
        }
    }); 
    
    //Смайлы--
    $('.smiles img').on('click',function(){
        var alt = $('#'+this.id).attr('alt');
        var text = $("textarea")[0].value+' '+alt;
        $("textarea")[0].value = text;
    });
    //bb коды
    $('.bb span').on('click',function(){
        var alt;
        if($('#'+this.id).attr('title')==null){
            alt = $('#'+this.id).attr('tooltip');
        }else{
            alt = $('#'+this.id).attr('title');
        }
        var text = $("textarea")[0].value+' '+alt;
        $("textarea")[0].value = text;
    });
    
    //Открытие скрытых блоков--
    $(".open").on('click',function(){
        if(this.id==''){
            $(this).attr('id', 'openbutton'+Math.floor(Math.random()*1001));
        }     
        var parId;
        var openid = this.id; 
        $("*:has(#"+openid+")").each(function(){
            if(this.id==''){
                $(this).attr('id', 'randomid'+Math.floor(Math.random()*1001));
            }
            parId = this.id; 
        });
        $("#"+parId+">.close").toggle("fast");
    });
});

function remove_message(id){
    $("#"+id).remove();
}
    
function hide_message(id){
    $("#"+id).hide("slow");
    setTimeout('remove_message("'+id+'")', 1000);
}

//Выводит окошко с сообщением
//liveTime - время автоматического закрытия окошка в ms
//Если liveTime == -1 , время устанавливается автоматом с учётом длины сообщения
function message(message, liveTime){
    var id = 'js_message_'+parseInt(Math.random()*1000);
    $("body").append('<div id="'+id+'" class="js_message"><a class="close" href="">[X]</a>'+message+'</div>');
    $("#"+id).show("slow");
    $("#"+id+" a.close").click(function(){
        hide_message(id);
        return false;
    });
        
    if(liveTime!=null){
        if(liveTime ==-1){
            liveTime = (5+parseInt(message.length/10))*500;
        }
        setTimeout('hide_message("'+id+'")', liveTime);
    }
}