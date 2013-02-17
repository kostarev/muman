<div><h3>Персонажи <a href="<?=H;?>/user/<?=$user['memb_guid'];?>"><?=$user['memb_name'];?></a></h3>
    <?if($chars):?>
    <table class="sys">
        <tr>
            <th>Характеристика</th>
        <?foreach($chars AS $char):?>
        <th><a href="<?=H;?>/char/<?=$char['name'];?>"><?=$char['name'];?></a></th>
        <?endforeach;?>
        </tr>
        <tr><td>Класс</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['class_title']['name'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Ресет</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['reset'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Уровень</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['clevel'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Деньги</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['money'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Strength</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['strength'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Vitality</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['vitality'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Agility</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['dexterity'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Energy</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['energy'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Command</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['leadership'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Свободные очки</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['LevelUpPoint'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Убийства</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['pklevel'];?></td>
        <?endforeach;?>
        </tr>
        <tr><td>Опыт</td>
        <?foreach($chars AS $char):?>
        <td><?=$char['experience'];?></td>
        <?endforeach;?>
        </tr>
    </table>
    <?else:?>
    У пользователя нет персонажей
    <?endif;?>
</div>