<?php
SiteWrite::me()->action_add('guestbook-moder','Модерато гостевой');
SiteWrite::me()->action_add('set-guestbook','Настройки гостевой');

$res = $this->db->prepare("INSERT INTO mm_config (mother, name, title, type, value) VALUES ('0', 'guestbook', ?, 'text', 'directory');");
$res->execute(Array('Гостевая книга'));
$res = $this->db->prepare("INSERT INTO mm_config (mother, name, title, type, value) VALUES ('guestbook', 'on', ?, 'checkbox', '1');");
$res->execute(Array('Включена'));
$res = $this->db->prepare("INSERT INTO mm_config (mother, name, title, type, value) VALUES ('guestbook', 'on_page', ?, 'int', '20');");
$res->execute(Array('Записей на странице'));


$this->db->query("
CREATE TABLE mm_guestbook (
  id      int IDENTITY(1,1) NOT NULL,
  [name]  nvarchar(20) NOT NULL,
  text    nvarchar(1500) NOT NULL,
  time    int NOT NULL,
  PRIMARY KEY (id)
)
");

$menu = new Menu();
$menu -> set('guestbook',0,'Гостевая книга','/guestbook');
$menu -> set('set-guestbook','settings','Гостевая книга','/panel/settings/guestbook');
?>