<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>

        <link rel="stylesheet" href="<?=H;?>/des/<?=$theme?>/sys.css" type="text/css" />
        <link rel="stylesheet" href="<?=H;?>/des/<?=$theme?>/style.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?=H;?>/des/<?=$theme?>/tooltip.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?=H;?>/des/<?=$theme?>/menu/pro_dropdown_2.css" type="text/css" />
        <?=$this->head;?>
        <script type="text/javascript" src="<?=H;?>/open/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="<?=H;?>/des/<?=$theme?>/script.js"></script>
        <script type="text/javascript" src="<?=H;?>/des/<?=$theme?>/tooltip.js"></script>
        <script type="text/javascript" src="<?=H;?>/des/<?=$theme?>/ajax.js"></script>
    <title><?=$title;?></title>
	</head>
    <body>

<div id="all">

	<div id="navigation">
	<ul id="menu">
        <?=$menu->get_tree_html(0, 'class="top"');?>
        </ul>
	</div>
	
	
<div id="content">

<table style="width:100%;">
<tr><td>
<div id="textcontent">
<div class="center">
<h2><?=$title_html?$title_html:$title;?></h2>