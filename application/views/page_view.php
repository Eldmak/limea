<?php
//    вся процедура работает на сессиях. Именно в ней хранятся данные пользователя,    пока он находится на сайте. Очень важно запустить их в самом начале    странички!!!
//session_start();
require("php/db.php");// файл bd.php должен быть в той же папке, что и все    остальные, если это не так, то просто измените путь

if (isset($_SESSION['id'])) {$id =$_SESSION['id']; } //id "хозяина" странички
else
{ exit("Вы зашли на    страницу без параметра!");} //если не    указали id, то выдаем ошибку
if (!preg_match("|^[\d]+$|", $id))    {
    exit("<p>Неверный    формат запроса! Проверьте URL</p>");//если id не число, то выдаем    ошибку
}
if (!empty($_SESSION['login']) and    !empty($_SESSION['password']))
{
    //если    существует логин и пароль в сессиях, то проверяем, действительны ли они
    $login = $_SESSION['login'];
    $password = $_SESSION['password'];
    $result2 = mysql_query("SELECT id FROM    users WHERE login='$login' AND password='$password'", $db);
    $myrow2 = mysql_fetch_array($result2);
    if (empty($myrow2['id']))
    {
        //Если не действительны (может мы удалили    этого пользователя из базы за плохое поведение)

        exit("Вход на эту страницу разрешен    только зарегистрированным пользователям!");
    }
}
else {
    //Проверяем,    зарегистрирован ли вошедший
    exit("Вход на эту    страницу разрешен только зарегистрированным пользователям!"); }
$result = mysql_query("SELECT * FROM    users WHERE id='$id'",$db);
$myrow =    mysql_fetch_array($result);//Извлекаем все данные    пользователя с данным id
if (empty($myrow['login'])) {    exit("Пользователя не существует! Возможно он был удален.");} //если такого не существует
?>
    <html>
    <head>
        <title><?php echo $myrow['login'];    ?></title>
    </head>
<body>

<h2>Пользователь "<?php echo    $myrow['login']; ?>"</h2>
<?php
    $yandexTokenStatus = $dbManager->checkYandexToken($_SESSION["id"]);
    $link_text = null;
    if($yandexTokenStatus != "ok")
        $link_text = "Подключить yandex";
?>


            |<a href='page.php?id=$myrow2[id]'>Моя страница</a>|<a href='index.php'>Главная страница</a>|<a href='all_users.php'>Список пользователей</a>|<a href='exit.php'>Выход</a><br><br>
<?php

    if(empty($link_text))
        echo '<a href="https://oauth.yandex.ru/authorize?response_type=code&client_id=06441eef0fa841478f8b2f73d6519117" target="_self">$link_text</a>'
?>


<?php
            //выше вывели меню 
if ($myrow['login'] == $login) { ?>
            //Если    страничка принадлежит вошедшему, то предлагаем изменить данные и выводим    личные сообщения

<form action='/update_user'    method='post'>
            Ваш логин    <strong><?$myrow[login]?></strong>. Изменить логин:<br>
            <input name='login' type='text'>
            <input type='submit' name='submit' value='изменить'>
            </form>
            <br>
<form action='/update_user'    method='post'>
            Изменить пароль:<br>
            <input name='password' type='password'>
            <input type='submit' name='submit' value='изменить'>
            </form>
            <br>
<form action='/update_user'    method='post' enctype='multipart/form-data'>
            Ваш аватар:<br>
            <img alt='аватар' src='<?$myrow[avatar]?>'><br>
            Изображение должно быть    формата jpg, gif или png. Изменить аватар:<br>
            <input type="FILE"    name="fupload">
            <input type='submit' name='submit' value='изменить'>
            </form>
            <br>
<h2>Личные    сообщения:</h2>
<?
$tmp = mysql_query("SELECT * FROM    messages WHERE poluchatel='$login' ORDER BY id DESC",$db); 
            $messages =    mysql_fetch_array($tmp);//извлекаем сообщения    пользователя, сортируем по идентификатору в обратном порядке, т.е. самые    новые сообщения будут вверху
if (!empty($messages['id'])) {
    do //выводим    все сообщения в цикле
    {
        $author = $messages['author'];
        $result4 = mysql_query("SELECT avatar,id    FROM users WHERE login='$author'",$db); //извлекаем аватар автора
        $myrow4 = mysql_fetch_array($result4);
        if (!empty($myrow4['avatar']))    {//если такового нет, то выводим стандартный (может    этого пользователя уже давно удалили)
            $avatar = $myrow4['avatar'];
        }
        else {$avatar =    "avatars/net-avatara.jpg";}?>

                 <table>
                 <tr>

                 <td><a href='page.php?id=%s'><img alt='аватар'    src='%s'></a></td>

                 <td>Автор:    <a href='page.php?id=%s'>%s</a><br>
                  Дата:    %s<br>
                                 Сообщение:<br>

                             %s<br>
                             <a href='drop_post.php?id=%s'>Удалить</a>

              
                 </td>  
                 </tr>
                 </table><br>
<?

    }
    while($messages = mysql_fetch_array($tmp));
}
else    {
    //если сообщений не найдено
    echo    "Сообщений нет";
}
                                                                                         

            }
else
            {
                //если    страничка чужая, то выводим только некторые данные и форму для отправки    личных сообщений
                ?>
            <img alt='аватар' src='<?$myrow[avatar]?>'><br>
            <form action='post.php' method='post'>
            <br>
            <h2>Отправить Ваше    сообщение:</h2>
            <textarea cols='43' rows='4'    name='text'></textarea><br>
            <input type='hidden' name='poluchatel'    value='<?$myrow[login]?>'>
            <input type='hidden' name='id'    value='<?$myrow[id]?>'>
            <input type='submit' name='submit' value='Отправить'>

            </form>
           <?
            }
?>
            </body>
            </html>