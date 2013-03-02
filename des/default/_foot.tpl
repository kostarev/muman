</div>
<hr/>
</div>

<div id="modules">

    <div class="module_menu"><p class="block_name">Авторизация</p></div>
    <div class="module_text">
        <?if(!$this->user['id']):?>
        <?$this->display('_auth_form');?>  
        <?else:?>
        <p><strong><?=$this->user['memb_name'];?></strong> <?=$this->user['group_title'];?> [<a href="<?=H;?>/login/logout" >Выход</a>]</p>
        <?endif;?>
    </div>


    <div class="module_menu"><p class="block_name">Меню</p></div>
    <div class="module_text">
       &nbsp;
    </div>
    



    <div class="module_menu"><p class="block_name">Сервер</p></div>
    <div class="module_text">
        &nbsp;
    </div>
</div>
</td></tr>
</table>
</div>

<div id="footer">
    <div class="foot_text"> 
        &copy; <?php echo date('Y'); ?> Powered by <a class="head_a" href="http://muman.ru/">Mu Manager</a>

        <?if(SiteRead::me()->is_access('panel')):?>
        <div>Time: <?=$gentime;?> с, SQL: <?=$sql_count;?> (<?=$sql_time;?> с.)</div>
        <?endif;?>
    </div>

</div>

</div>
</body>
</html>