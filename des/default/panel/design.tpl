<div class="designes">
    <?foreach($designes AS $des):?>
    <div>
        <table class="sys" style="width:100%;">
            <tr><td style="width:30%;"><a href="<?=H;?>/des/<?=$des['dir'];?>/screen.jpg"><img style="width:200px;" src="<?=H;?>/des/<?=$des['dir'];?>/screen.jpg" alt="<?=$des['title'];?>"/></a></td>
                <td>
                    <b><?=$des['title'];?></b><br />
                    Папка: <?=$des['dir'];?><br />
                    Автор: <?=$des['autor'];?><br />
                    Описание: <?=$des['description'];?><br />
                    <?if($theme == $des['dir']):?>
                    <span class="green">Выбрана</span>
                    <?else:?>
                    <form method="post" action="#">
                        <input type="hidden" name="des" value="<?=$des['dir'];?>" />
                    <input type="submit" value="Выбрать" />
                    </form>
                    <?endif;?>
                </td></tr>
        </table>
    </div>
    <?endforeach;?>


</div>