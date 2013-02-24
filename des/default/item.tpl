<div>
<p><b><?=$item['KOR']['name'];?></b></p>
<p><b>Тип:</b> <?=$item['type_name'];?></p>
<p><b>Level:</b> <?=$item['level'];?></p>
<p><b>Attack speed:</b> <?=$item['KOR']['attspeed'];?></p>
<p><b>Durability:</b> <?=$item['durability'];?> / <?=$item['KOR']['dur'];?></p>
<p><b>Strength Requirement:</b> <?=$item['KOR']['strreq'];?></p>
<p><b>Agility Requirement:</b> <?=$item['KOR']['agireq'];?></p>
<p><b>Можно одеть на :</b> 
    <?=$item['KOR']['dw/sm']?'<br />Dark Wizard, Soul Master':'';?>
    <?=$item['KOR']['dk/bk']?'<br />Dark Knight, Blade Knight, Blade Master':'';?>
    <?=$item['KOR']['elf/me']?'<br />Fairy Elf, Muse Elf, High Elf':'';?>
    <?=$item['KOR']['mg']?'<br />Magic Gladiator, Duel Master':'';?>
    <?=$item['KOR']['dl']?'<br />Dark Lord, Lord Emperor':'';?>
    <?=$item['KOR']['sum']?'<br />Summoner, Bloody Summoner, Dimension Master':'';?>
</p>

<?if($item['is_option']):?>
    <?if($item['addoption'][0]):?>
        <b><?=$item['addoption'][0]['option_kat'];?></b><br />
        <?=$item['addoption'][0]['opt1_name'];?>:  <?=$item['addoption'][0]['val1'];?><br />
        <?=$item['addoption'][0]['opt2_name'];?>:  <?=$item['addoption'][0]['val2'];?><br />
    <?endif;?>
<?endif;?>

<?if($item['is_skill']):?>
 <p>Skill: <b><?=$item['skill']['name'];?></b>
    <?foreach($item['skill']['opt'] AS $key => $val):?>
    <br /><?=$key;?>: <?=$val;?>
    <?endforeach;?>
 </p>
<?endif;?>

<p><b>Luck:</b> <?=$item['luck']?'Да':'Нет';?></p>

<?if($item['option']):?>
 <p><b>Опция:</b> <?=$item['option_str'];?></p>
<?endif;?>
<?if($item['excellent_str']):?>
 <p><b>Excellent опции:</b>
     <?foreach($item['excellent_str'] AS $val):?>
    <br /><?=$val;?>
    <?endforeach;?>
 </p>
<?endif;?>

<?if($item['h_type']):?>
 <p><b>Harmony:</b> <?=$item['harmonys']['name'];?> +<?=$item['harmonys']['lvl'.$item['h_val']];?></p>
<?endif;?>

<?if($item['ancient']):?>
 <p><b>Ancient:</b> <?=$item['ancient'];?></p>
<?endif;?>
</div>
