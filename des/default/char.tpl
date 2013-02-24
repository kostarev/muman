<div>
<?if($char):?>
<img src="<?=H;?>/des/<?=$theme?>/img/chars/<?=$char['class_title']['img'];?>.gif" alt="<?=$char['class_title']['name'];?>"/>
<table class="sys" style="width:500px;">
<tr><th>Параметр</th><th>Значение</th></tr>
<tr><td>Класс</td><td> <?=$char['class_title']['name'];?></td></tr>
<tr><td>Ресет</td><td> <?=$char['reset'];?></td></tr>
<tr><td>Уровень</td><td> <?=$char['clevel'];?></td></tr>
<tr><td>Деньги</td><td> <?=$char['money'];?></td></tr>
<tr><td>Strength</td><td> <?=$char['strength'];?></td></tr>
<tr><td>Vitality</td><td> <?=$char['vitality'];?></td></tr>
<tr><td>Agility</td><td> <?=$char['dexterity'];?></td></tr>
<tr><td>Energy</td><td> <?=$char['energy'];?></td></tr>
<?if($char['leadership']):?><tr><td>Command</td><td> <?=$char['leadership'];?></td></tr><?endif;?>
<tr><td>Свободные очки</td><td> <?=$char['LevelUpPoint'];?></td></tr>
<tr><td>Убийства</td><td> <?=$char['pklevel'];?></td></tr>
<tr><td>Опыт</td><td> <?=$char['experience'];?></td></tr>
</table>
<?endif;?>
</div>