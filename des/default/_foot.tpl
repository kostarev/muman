</div>
<hr/>
</div>

<div id="modules">

<div class="module_menu"><p class="block_name">Авторизация</p></div>
<div class="module_text">
<?if(!$this->user['id']):?>
                <?$this->display('_auth_form');?>  
                <?else:?>
                <p><strong><?=$this->user['memb_name'];?></strong> <?=$this->user['group_title'];?> [<a href="<?=H;?>/login/logout" >{Выход}</a>]</p>
                <?endif;?>
</div>
</div>

<!--menu -->
<div class="module_menu"><p class="block_name">Меню</p></div>
<div class="module_text">
<table width="90%" align="center" valign="center">
         <tr class="modules_tr">
<td>
<a href="/?p=main"><div class="urlblock">» На главную</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=statistic"><div class="urlblock">» О сервере</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=register"><div class="urlblock">» Регистрация</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=download"><div class="urlblock">» Скачать</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=online"><div class="urlblock">» Игроки онлайн</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=top100"><div class="urlblock">» Топ 100</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=forum"><div class="urlblock">» Форум</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=banlist"><div class="urlblock">» Забаненные</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/?p=topguild"><div class="urlblock">» Топ гильдий</div></a>
</td>
</tr>
         <tr class="modules_tr">
<td>
<a href="/faq/"><div class="urlblock"> »  Фаг</div></a>
</td>
</tr>
</table>
</div>
<!--menu off -->



<div class="module_menu"><p class="block_name">Сервер</p></div>
<div class="module_text">
|qinfo|
</div>

</tr></td>
</table>
</div>
<!--footer -->
<div id="footer"><div class="foot_text"> &copy; <?php echo date('Y'); ?> Powered by <a href="http://muman.ru/">Mu Manager</a></div></div>
<?

if(SiteRead::me()->is_access('panel')):?>
Time: <?=$gentime;?> с, SQL: <?=$sql_count;?> (<?=$sql_time;?> с.)
<?endif;?>
<!--footer off -->
</div>
</body>
</html>