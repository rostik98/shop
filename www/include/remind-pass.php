<?php
if($_SERVER["REQUEST_METHOD"] == "POST") {
    define('myshop', true);
    include("db_connect.php");
    include("../functions/functions.php");

    $email = clear_string($_POST["email"]);

    if ($email != "") {

        $result = mysqli_query($link,"SELECT email FROM reg_user WHERE email='$email'");
        if (mysqli_num_rows($result) > 0)
        {


            // Генерация пароля.
            $newpass = fungenpass();

            // Шифрование пароля.
            $password   = md5($newpass);
            $password   = strrev($password);
            $password   = strtolower("9nm2rv8q".$password."2yo6z");


            // Обновление пароля на новый.
            $update = mysqli_query($link,"UPDATE reg_user SET password='$password' WHERE email='$email'");

            /*
            // Отправка нового пароля.
            send_mail( 'noreply@shop.ru',
                $email,
                'Новый пароль для сайта MyShop.ru',
                'Ваш пароль: '.$newpass);

            */
            echo true;

        }else {
            echo 'Данный E-mail не найден!';
        }

    }
    else {
        echo 'Укажите свой E-mail';
    }

}