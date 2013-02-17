<div>
    <?foreach($this->conf_dirs AS $val):
    if($val['group'] AND $val['group']<>$this->user['mm_group'] AND $this->user['mm_group']<>'root'){
   continue;
    }
    ?>
    <?if($settings_dir == $val['name']):?>
    <p><strong><?=$val['title'];?></strong></p>
    <?else:?>
    <p><a href="<?=H;?>/panel/settings/<?=$val['name'];?>"><?=$val['title'];?></a></p>
    <?endif;?>
    <?endforeach;?>


    <?if($configs):?>
    <form method="post" action="#" >
        <table class="sys">
            <tr><th>Название</th><th>Значение</th></tr>
            <?foreach($configs AS $val):?>
            <?if(!isset($val['value'])):?>
            <?foreach($val AS $v):?>
            <tr>
                <td title="<?=$v['name']?>">[<span class="green"><?=$v['mother']?></span>] <?=$v['title']?></td>
                <td>
                    <?if($v['type']=='checkbox'):?>
                    <input type="checkbox" name="conf[<?=$v['name'];?>]" value="1" <?=$v['value']?'checked="checked"':''?>/>
                           <?elseif($v['type']=='int'):?>
                           <input type="number" name="conf[<?=$v['name'];?>]" value="<?=$v['value']?>" />
                    <?else:?>
                    <input type="text" name="conf[<?=$v['name'];?>]" value="<?=$v['value']?>" />
                    <?endif?>
                </td>
            </tr>
            <?endforeach;?>
            <?else:?>
            <tr>
                <td title="<?=$val['name']?>"><?=$val['title']?></td>
                <td>
                    <?if($val['type']=='checkbox'):?>
                    <input type="checkbox" name="conf[<?=$val['name'];?>]" value="1" <?=$val['value']?'checked="checked"':''?>/>
                           <?elseif($val['type']=='int'):?>
                           <input type="number" name="conf[<?=$val['name'];?>]" value="<?=$val['value']?>" />
                    <?else:?>
                    <input type="text" name="conf[<?=$val['name'];?>]" value="<?=$val['value']?>" />
                    <?endif?>
                </td>
            </tr>
            <?endif?>
            <?endforeach;?>
        </table>
        <p><input type="submit" name="save" value="Сохранить" /></p>
    </form>
    <?endif;?>
</div>