<div>
<p>Имя: <b><?=$user['memb_name'];?></b></p>
<p>Группа: <b><?=$user['group_title'];?></b> <? if(SiteRead::me()->is_access('change-group')):?> <a title="Изменить группу" href="<?=H;?>/panel/users/change_group/<?=$user['id'];?>">&gt;&gt;</a><?endif;?></p>
<p>Зарегистрирован: <?=$user['reg_date'];?></p>
</div>