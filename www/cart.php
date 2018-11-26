<?php
define('myshop', true);
session_start();

include("functions/functions.php");
include("include/auth_cookie.php");
include("include/db_connect.php");

if (isset($_GET['id'])) {
    $id = clear_string($_GET['id']);
} else {
    $id = "";
}
if (isset($_GET['action'])) {
    $action = clear_string($_GET['action']);
} else {
    $action = "";
}
switch ($action) {
    case 'clear':
        $clear = mysqli_query($link, "DELETE FROM cart WHERE cart_ip = '{$_SERVER['REMOTE_ADDR']}'");
        break;
    case 'delete':
        $delete = mysqli_query($link, "DELETE FROM cart WHERE cart_id = '$id' AND cart_ip = '{$_SERVER['REMOTE_ADDR']}'");
        break;
}

if (isset($_POST['submitdata'])) {
    $_SESSION['order_delivery'] = $_POST['order_delivery'];
    $_SESSION['order_fio'] = $_POST['order_fio'];
    $_SESSION['order_email'] = $_POST['order_email'];
    $_SESSION['order_phone'] = $_POST['order_phone'];
    $_SESSION['order_address'] = $_POST['order_address'];
    $_SESSION['order_note'] = $_POST['order_note'];

    header("location: cart.php?action=finishing");
}

$result = mysqli_query($link, "SELECT * FROM cart,table_products WHERE cart.cart_ip = '{$_SERVER['REMOTE_ADDR']}' AND table_products.product_id = cart.cart_id_product");
$totalprice = 0;
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);

    do {
        $totalprice += $totalprice + ($row["price"] * $row["cart_count"]);
    } while ($row = mysqli_fetch_array($result));

}

mysqli_set_charset($link, "UTF8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="css/reset.css" rel="stylesheet" type="text/css"/>
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
    <link href="trackbar/trackbar.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="js/jcarousellite_1.0.1.js"></script>
    <script type="text/javascript" src="js/shop-script.js"></script>
    <script type="text/javascript" src="js/jquery.cookie-1.4.1.min.js"></script>
    <script type="text/javascript" src="trackbar/jquery.trackbar.js"></script>
    <script type="text/javascript" src="js/TextChange.js"></script>
    <title>Корзина</title>
</head>
<body>
<div id="block-body">
    <?php
    include("include/block-header.php");
    ?>

    <div id="block-right">
        <?php
        include("include/block-category.php");
        include("include/block-parameter.php");
        //include("include/block-news.php");
        ?>
    </div>
    <div id="block-content">

        <?php
        if (isset($_GET['action'])) {
            $action = clear_string($_GET['action']);
            switch ($action) {
                case 'check':
                    echo '
                        <div id="block-step">
                            <div id="name-step">
                                <ul>
                                    <li><a class="active">1. Корзина товаров</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a>2. Контактная информация</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a>3. Завершение</a></li>
                                </ul>
                            </div>
                            
                            <p>шаг 1 из 3</p>
                            <a href="cart.php?action=clear">Очистить</a>
                        </div>
                        ';


                    $result = mysqli_query($link, "SELECT * FROM cart,table_products WHERE cart.cart_ip = '{$_SERVER['REMOTE_ADDR']}' AND table_products.product_id = cart.cart_id_product");
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_array($result);
                        echo '
                            <div id="header-list-cart">
                                <div id="head1">Изображение</div>
                                <div id="head2">Наименование товара</div>
                                <div id="head3">Количество</div>
                                <div id="head4">Цена</div>
                            </div>
                            ';
                        $all_price = 0;

                        do {
                            $int = $row["cart_price"] * $row["cart_count"];
                            $all_price = $all_price + $int;

                            if (strlen($row["image"]) > 0 && file_exists("./uploads/" . $row["image"])) {
                                $img_path = './uploads/' . $row["image"];
                                $max_width = 100;
                                $max_height = 100;
                                list($width, $height) = getimagesize($img_path);
                                $ratioh = $max_height / $height;
                                $ratiow = $max_width / $width;
                                $ratio = min($ratioh, $ratiow);

                                $width = intval($ratio * $width);
                                $height = intval($ratio * $height);
                            } else {
                                $img_path = "images/no-image.jpeg";
                                $width = 120;
                                $height = 105;
                            }

                            echo '
                                    <div class="block-list-cart">
                                        <div class="img-cart">
                                            <p align="center"><img src="' . $img_path . '" width="' . $width . '" height="' . $height . '"/></p>
                                        </div>
                                        <div class="title-cart">
                                            <p><a href="#"> ' . $row["title"] . ' </a></p>
                                            <p class="cart-mini_features">
                                                ' . $row["mini_features"] . '
                                            </p>
                                        </div>
                                        <div class="count-cart">
                                            <ul class="input-count-style">
                                               <li>
                                                <p align="center" class="count-minus" change="' . $row["cart_id"] . '"> - </p> 
                                               </li>
                                               <li>
                                                <p align="center"> <input class="count-input" id="input-id' . $row["cart_id"] . '" maxlength="3" type="text" value="' . $row["cart_count"] . '" change="' . $row["cart_id"] . '"> </p> 
                                               </li> 
                                               <li>
                                                <p align="center" class="count-plus" change="' . $row["cart_id"] . '"> + </p> 
                                               </li> 
                                            </ul>
                                        </div>
                                        <div class="price-product" id="tovar' . $row['cart_id'] . '">
                                            <h5> <span class="span-count">' . $row["cart_count"] . '</span> x <span>' . $row["cart_price"] . ' грн</span> </h5> <p price="' . $row['cart_price'] . '">' . $int . ' грн </p>
                                        </div>
                                        <div class="delete-cart">
                                            <a href="cart.php?id=' . $row["cart_id"] . '&action=delete"><img src="images/bsk_item_del.png"/></a>
                                        </div>
                                    </div>
                                    
                                    
                                    ';
                        } while ($row = mysqli_fetch_array($result));

                        echo '
                            <h2 class="total-price" align="right">Итого: <strong>' . $all_price . '</strong> грн</h2>
                            <p align="right" class="button-next"><a href="cart.php?action=confirm">Далее</a></p>
                            
                            ';

                    } else {
                        echo '<h3 align="center">Корзина пуста</h3>';
                    }


                    break;
                case 'confirm':
                    echo '
                        <div id="block-step">
                            <div id="name-step">
                                <ul>
                                    <li><a href="cart.php?action=check">1. Корзина товаров</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a class="active">2. Контактная информация</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a>3. Завершение</a></li>
                                </ul>
                            </div>
                            
                            <p>шаг 2 из 3</p>
                           
                        </div>
                        ';

                    $chck1 = "";
                    $chck2 = "";
                    $chck3 = "";
                    if (isset($_SESSION['order_delivery']) == "По почте")
                        $chck1 = "checked";
                    if (isset($_SESSION['order_delivery']) == "Курьером")
                        $chck2 = "checked";
                    if (isset($_SESSION['order_delivery']) == "Самовывоз")
                        $chck3 = "checked";

                    echo '
 
                        <h3 class="title-h3" >Способы доставки:</h3>
                        <form method="post">
                            <ul id="info-radio">
                                <li>
                                <input type="radio" name="order_delivery" class="order_delivery" id="order_delivery1" value="По почте" ' . $chck1 . '  />
                                <label class="label_delivery" for="order_delivery1">По почте</label>
                                </li>
                                <li>
                                <input type="radio" name="order_delivery" class="order_delivery" id="order_delivery2" value="Курьером" ' . $chck2 . ' />
                                <label class="label_delivery" for="order_delivery2">Курьером</label>
                                </li>
                                <li>
                                <input type="radio" name="order_delivery" class="order_delivery" id="order_delivery3" value="Самовывоз" ' . $chck3 . ' />
                                <label class="label_delivery" for="order_delivery3">Самовывоз</label>
                                </li>
                            </ul>
                            <h3 class="title-h3" >Информация для доставки:</h3>
                            <ul id="info-order">
                            ';
                    if (isset($_SESSION["order_fio"])) {
                        $order_fio = $_SESSION["order_fio"];
                    } else {
                        $order_fio = "";
                    }
                    if (isset($_SESSION["order_email"])) {
                        $order_email = $_SESSION["order_email"];
                    } else {
                        $order_email = "";
                    }
                    if (isset($_SESSION["order_phone"])) {
                        $order_phone = $_SESSION["order_phone"];
                    } else {
                        $order_phone = "";
                    }
                    if (isset($_SESSION["order_address"])) {
                        $order_address = $_SESSION["order_address"];
                    } else {
                        $order_address = "";
                    }
                    if ((isset($_SESSION['auth']) != 'yes_auth')) {
                        echo '
                                <li><label for="order_fio"><span>*</span>ФИО</label><input type="text" name="order_fio" id="order_fio" value="' . $order_fio . '" required /><span class="order_span_style" >Пример: Иванов Иван Иванович</span></li>
                                <li><label for="order_email"><span>*</span>E-mail</label><input type="email" name="order_email" id="order_email" value="' . $order_email . '" required /><span class="order_span_style" >Пример: ivanov@gmail.com</span></li>
                                <li><label for="order_phone"><span>*</span>Телефон</label><input type="text" name="order_phone" id="order_phone" value="' . $order_phone . '" required /><span class="order_span_style" >Пример: 380981234567 </span></li>
                                <li><label for="order_address"><span>*</span>Адрес<br /> доставки</label><input type="text" name="order_address" id="order_address" value="' . $order_address . '" /><span>Пример: г. Москва,<br /> ул Одесская 18, кв 58</span></li>
                                ';
                    }
                    if (isset($_SESSION['order_note'])) {
                        $order_note = $_SESSION['order_note'];
                    } else {
                        $order_note = "";
                    }
                    echo '
                            <li><label class="order_label_style" for="order_note">Примечание</label><textarea name="order_note" >' . $order_note . '</textarea><span>Уточните информацию о заказе.<br />  Например, удобное время для звонка<br />  нашего менеджера</span></li>
                            </ul>
                            <p align="right"><input type="submit" name="submitdata" id="confirm-button-next" value="Далее" /></p>
                        </form>
                         
                       
                         ';

                    break;
                case 'finishing':
                    echo '
                        <div id="block-step">
                            <div id="name-step">
                                <ul>
                                    <li><a href="cart.php?action=check">1. Корзина товаров</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a href="cart.php?action=confirm">2. Контактная информация</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a class="active">3. Завершение</a></li>
                                </ul>
                            </div>
                            
                            <p>шаг 3 из 3</p>
                            
                        </div>
                        <h2>Конечная информация</h2>
                        ';

                    if (isset($_SESSION['auth']) == 'yes_auth') {
                        echo '
                            <ul id="list-info">
                                <li><strong>Способ доставки:</strong>' . $_SESSION['order_delivery'] . '</li>
                                <li><strong>Email:</strong>' . $_SESSION['auth_email'] . '</li>
                                <li><strong>ФИО:</strong>' . $_SESSION['auth_surname'] . ' ' . $_SESSION['auth_name'] . ' ' . $_SESSION['auth_patronymic'] . '</li>
                                <li><strong>Адрес доставки:</strong>' . $_SESSION['auth_address'] . '</li>
                                <li><strong>Телефон:</strong>' . $_SESSION['auth_phone'] . '</li>
                                <li><strong>Примечание:</strong>' . $_SESSION['order_note'] . '</li>
                            </ul>
                            ';
                    } else {
                        echo '
                            <ul id="list-info" >
                                <li><strong>Способ доставки:</strong>' . $_SESSION['order_delivery'] . '</li>
                                <li><strong>Email:</strong>' . $_SESSION['order_email'] . '</li>
                                <li><strong>ФИО:</strong>' . $_SESSION['order_fio'] . '</li>
                                <li><strong>Адрес доставки:</strong>' . $_SESSION['order_address'] . '</li>
                                <li><strong>Телефон:</strong>' . $_SESSION['order_phone'] . '</li>
                                <li><strong>Примечание: </strong>' . $_SESSION['order_note'] . '</li>
                            </ul>
                            
                            ';
                    }

                    echo '
                        <h2 class="itog-price" align="right">Итого: <strong>' . $totalprice . '</strong> грн</h2>
                          <p align="right" class="button-next" ><a href="" >Оплатить</a></p> 
                          
                         ';

                    break;
                default:
                    echo '
                        <div id="block-step">
                            <div id="name-step">
                                <ul>
                                    <li><a class="active">1. Корзина товаров</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a>2. Контактная информация</a></li>
                                    <li><span>&rarr;</span></li>
                                    <li><a>3. Завершение</a></li>
                                </ul>
                            </div>
                            
                            <p>шаг 1 из 3</p>
                            <a href="cart.php?action=clear">Очистить</a>
                        </div>
                        ';


                    $result = mysqli_query($link, "SELECT * FROM cart,table_products WHERE cart.cart_ip = '{$_SERVER['REMOTE_ADDR']}' AND table_products.product_id = cart.cart_id_product");
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_array($result);
                        echo '
                            <div id="header-list-cart">
                                <div id="head1">Изображение</div>
                                <div id="head2">Наименование товара</div>
                                <div id="head3">Количество</div>
                                <div id="head4">Цена</div>
                            </div>
                            ';
                        $all_price = 0;

                        do {
                            $int = $row["cart_price"] * $row["cart_count"];
                            $all_price = $all_price + $int;

                            if (strlen($row["image"]) > 0 && file_exists("./uploads/" . $row["image"])) {
                                $img_path = './uploads/' . $row["image"];
                                $max_width = 100;
                                $max_height = 100;
                                list($width, $height) = getimagesize($img_path);
                                $ratioh = $max_height / $height;
                                $ratiow = $max_width / $width;
                                $ratio = min($ratioh, $ratiow);

                                $width = intval($ratio * $width);
                                $height = intval($ratio * $height);
                            } else {
                                $img_path = "images/no-image.jpeg";
                                $width = 120;
                                $height = 105;
                            }

                            echo '
                                    <div class="block-list-cart">
                                        <div class="img-cart">
                                            <p align="center"><img src="' . $img_path . '" width="' . $width . '" height="' . $height . '"/></p>
                                        </div>
                                        <div class="title-cart">
                                            <p><a href="#"> ' . $row["title"] . ' </a></p>
                                            <p class="cart-mini_features">
                                                ' . $row["mini_features"] . '
                                            </p>
                                        </div>
                                        <div class="count-cart">
                                            <ul class="input-count-style">
                                               <li>
                                                <p align="center" class="count-minus" change="' . $row["cart_id"] . '"> - </p> 
                                               </li>
                                               <li>
                                                <p align="center"> <input class="count-input" id="input-id' . $row["cart_id"] . '" maxlength="3" type="text" value="' . $row["cart_count"] . '" change="' . $row["cart_id"] . '"> </p> 
                                               </li> 
                                               <li>
                                                <p align="center" class="count-plus" change="' . $row["cart_id"] . '"> + </p> 
                                               </li> 
                                            </ul>
                                        </div>
                                        <div class="price-product" id="tovar' . $row['cart_id'] . '">
                                            <h5> <span class="span-count">' . $row["cart_count"] . '</span> x <span>' . $row["cart_price"] . ' грн</span> </h5> <p price="' . $row['cart_price'] . '">' . $int . ' грн </p>
                                        </div>
                                        <div class="delete-cart">
                                            <a href="cart.php?id=' . $row["cart_id"] . '&action=delete"><img src="images/bsk_item_del.png"/></a>
                                        </div>
                                    </div>
                                    
                                    
                                    ';
                        } while ($row = mysqli_fetch_array($result));

                        echo '
                            <h2 class="total-price" align="right">Итого: <strong>' . $all_price . '</strong> грн</h2>
                            <p align="right" class="button-next"><a href="cart.php?action=confirm">Далее</a></p>
                            
                            ';

                    } else {
                        echo '<h3 align="center">Корзина пуста</h3>';
                    }
                    break;
            }
        }
        ?>

    </div>

    <?php
    include("include/block-footer.php");
    ?>
</div>
</body>
</html>