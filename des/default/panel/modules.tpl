<div>
    <?if($install):?>
    <h2>Загрузка модуля</h2>
    <div>
        <form method="post" enctype="multipart/form-data" action="#">
            Выберите файл модуля (.smod)<br />
            <input type="file" name="file" required="required" />
            <input type="submit" value="Установить" />
        </form>
    </div>
    <?elseif($installed):?>
    <h2>Загруженные модули</h2>
    <div>
        <?if($modules):?>
        <table class="sys">
            <tr><th>Название</th><th style="width:50%;">Описание</th><th>Файл</th><th>Автор</th><th>Версия</th><th>Версия CMS</th><th>Вкл/выкл</th> <!-- <th class="red" title="Удалить">[X]</th> --></tr>
            <?foreach($modules AS $mod):?>
            <tr <?if($mod['installed']):?>class="back_green"<?endif?>>
                <td><?=$mod['title'];?></td>
                <td><?=$mod['description'];?></td>
                <td><?=$mod['fname'];?></td>
                <td><?=$mod['autor'];?></td>
                <td><?=$mod['version'];?></td>
                <td><?=$mod['cms_version'];?></td>
                <td>
                    <?if($mod['installed']):?>
                    <a href="<?=H;?>/panel/modules/uninstall/<?=$mod['fname'];?>">Выкл</a>
                    <?else:?>
                    <a href="<?=H;?>/panel/modules/info/<?=$mod['fname'];?>">Вкл</a>
                    <?endif;?>
                </td>
                <!-- 
                <td><?if(!$mod['installed']):?><a title="Удалить" class="red" href="<?=H;?>/panel/modules/del/<?=$mod['fname'];?>">[X]</a><?endif;?></td> 
                -->
            </tr>
            <?endforeach?>
        </table>
        <?else:?>
        Модули не найдены
        <?endif;?>
    </div>
    <?elseif($ok):?>
    <h3>Установка завершена</h3>
    <?=$install_str;?>
    <?elseif($info):?>
    <div>
        <h3>Проверка на конфликты с другими модулями</h3>
        <?foreach($files AS $file):?>
        <p><?=$file['name']?> 
            <?if($file['conflict']):?>[<span class="red">Конфликт с <?=$file['conflict'];?></span>]
            <?elseif($file['replace']):?>[<span class="siren">Замена</span>]
            <?else:?>[<span class="green">Ok</span>]<?endif;?>

        </p>
        <?endforeach;?>
        <p><a href="<?=H;?>/panel/modules/installed">Назад</a></p>

    </div>
    <?elseif($uninstall):?>
    <div>
        <h3>Удаление модуля</h3>
        <?=$uninstall_str;?>
    </div>
    <?else:?>
      <?=$menu->get_html('modules','<p><a href="{url}">{title}</a></p>');?>
    <?endif?>
</div>