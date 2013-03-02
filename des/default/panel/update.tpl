<div>
    <p><b>Версия вашей системы:</b> <?=$vers;?></p>
    <p><b>Последняя доступная версия:</b> <?=$last . ' ' .$last_type;?></p>
    <?if((real)$version < (real)$last):?>
    <form method="post" action="#">
        <input type="hidden" name="update" value="1" />
        <p><label><input name="merge" type="checkbox" value="1" /> Автоматически установить и слить с системой</label></p>
        <p><input type="submit" value="Скачать модуль обновления" /></p>
    </form>
    <?endif;?>
</div>