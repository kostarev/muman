<div>
    <?if($change_group):
    if($user['mm_group']=='root'):?>
    Группу <b>Супер админ</b> изменить нельзя
    <?else:?>

    <form method="post" action="#">
        <p>
            <select name="group">
                <?foreach($this->groups AS $gr):
                if($gr['name']=='root'){continue;}
                ?>
                <option value="<?=$gr['name'];?>" <?=($gr['name']==$user['mm_group'])?'selected="selected"':'';?>><?=$gr['title'];?></option>
                <?endforeach;?>
            </select>
            <input type="submit" value="Сохранить" />
        </p>
    </form>
    <?endif;?>

    <?elseif($pers):?>
    <?if($chars):?>
    <?foreach($chars AS $char):?>
    <p><a href="/char/<?=$char['name'];?>"><?=$char['name'];?></a></p>
    <?endforeach;?>
    <?else:?>
    У пользователя нет персонажей
    <?endif;?>
    <?else:?>
<script type="text/javascript">
jQuery(function() {
    $('a.show_pers').live('click',function(){
    action = this + '&ajax';
    $("#chars_cont").hide("fast");
    $("#chars").show();
    $('#chars').load(action);
    $("#chars_cont").show("fast");
    return false;
    });
});
</script>
    <?if($pages):?><div class="pages"><?=$pages;?></div><?endif;?>
    <table class="sys">
        <tr><th>№</th><th>ID</th><th>Логин</th><th>Имя</th><th>Группа</th><th>Смотреть персоражей</th></tr>
        <?foreach($users AS $key => $val):?>
        <tr><td><?=($page-1)*$on_page+$key+1;?></td>
		<td><?=$val['memb_guid'];?></td>
            <td><a href="<?=H;?>/user/<?=$val['memb_guid'];?>"><?=$val['memb___id'];?></a></td>
            <td><?=$val['memb_name'];?></td>
            <td><?=$val['group_title'];?></td>
            <td><a class="show_pers" href="/panel/users/pers/<?=$val['memb_guid'];?>">Персонажи</a></td></tr>
        <?endforeach;?>
    </table>
    <?if($pages):?><div class="pages"><?=$pages;?></div><?endif;?>
    <?endif;?>

    <div id="chars_cont" style="z-index:10;font-size:xx-small; display:none;position: fixed; right: 200px; top:200px; border:solid 1px #cccccc; width:auto;background-color: #ffffff;padding:5px;">
        <span class="open" style="float:right;" title="Закрыть">X</span>
        <div class="close" id="chars" style="max-height: 400px;overflow: auto;"></div>
    </div>
</div>