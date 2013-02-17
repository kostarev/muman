<style type="text/css" scoped="scoped">
div.menu_tree div{margin:3px;margin-left:30px;padding:5px; border:solid 1px #cccccc; border-left:dotted 1px red;}
</style>

<?if($menu_arr):?>
<form method="post" action="#">
    <p><input name="name" value="<?=$menu_arr['name'];?>" placeholder="Системное имя" /></p>
    <p><input name="title" value="<?=$menu_arr['title'];?>" placeholder="Заголовок" /></p>
    <p><input name="url" value="<?=$menu_arr['url'];?>" placeholder="URL" /></p>
    <p><input name="access" value="<?=$menu_arr['access'];?>" placeholder="Доступ" /></p>
    <p><input type="submit" value="Сохранить" /></p>
</form>
<?endif;?>


<div>
<div class="menu_tree">
<?=$menu_tree['html'];?>
</div>
</div>