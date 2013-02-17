<div>
    <form method="post" action="#">
        <table class="sys">
            <tr><th>Доступ\Группа</th>
                <?foreach($this->groups AS $val):
                if($val['name']=='root'){continue;}?>
                <th><?=$val['title']?> (<?=$val['name']?>)</th>
                <?endforeach;?>
            </tr>

            <?foreach($this->actions AS $act):?>
            <tr>
                <td title="<?=$act['name'];?>"><?=$act['title'];?></td>
                <?foreach($this->groups AS $gr):
                if($gr['name']=='root'){continue;}?>
                <td><input type="checkbox" name="<?=$gr['name'];?>[<?=$act['name'];?>]" <?=isset($gr['actions_arr'][$act['name']])?'checked="checked"':'';?> value="1" /></td>
                <?endforeach;?>
            </tr>
            <?endforeach;?>
        </table>
        <p><input type="submit" name="save" value="Сохранить" /></p>
    </form>
</div>