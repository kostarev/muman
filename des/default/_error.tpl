<?if($ajax):?>
message('<div class="red">Ошибка!</div><?=$error;?>', -1);
$("#captcha").attr("src",$("#captcha").attr("src")+"1");
<?else:?>
<div class="error">
    <?if(is_array($error)):?>
    <span class="red">Ошибка!</span>
        <?foreach($error AS $val):?>
        <p><span><?=$val;?></span></p>
        <?endforeach;?>
    <?else:?>
    <span>Ошибка!<br /><?=$error;?></span>
    <?endif?>
    <?if($exit):?><div><a href="<?=H;?>/">На главную</a> <a href="<?=$this->back_url;?>">Назад</a></div><?endif;?>
</div>
<?endif;?>