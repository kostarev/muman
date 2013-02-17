<div>
    <div><?=$error;?></div>
<div style="white-space:pre"><?=$sql;?></div>
    <form method="post" action="#">
        <p><input name="title" /></p>
        <p><input name="name" /></p>
        <p><textarea name="sql" style="width:100%; height:200px;"><?=$sql;?></textarea></p>
        <p><input type="submit" /></p>
    </form>
</div>