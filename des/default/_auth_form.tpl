<form method="post" action="<?=H;?>/login">
    <p><input type="text" name="login" pattern="[A-Za-zА-Яа-яЁё0-9]{3,20}" required="required" placeholder="Логин" autocomplete="on"/></p>
    <p><input type="password" pattern="[^\s]{5,20}" name="pas" required="required" placeholder="Пароль" /></p> 
    <p><label><input type="checkbox" checked="checked" name="remember"  value="1"/> Запомнить</label> <input type="submit" value="Вход" /></p>
    <p><a href="<?=H;?>/login/registration" >Регистрация</a></p>
</form>