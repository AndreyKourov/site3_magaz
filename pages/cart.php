<h3>Cart</h3>

<?php
echo '<form action="index.php?page=2" method="post">';

// проверка текущего имени пользователя
if(!isset($_SESSION['ruser'])) {
$ruser = 'cart';
} else {
    $ruser = $_SESSION['ruser'];
}

// формирем корзину и сумарню стоимость товаров
$total = 0;
foreach($_COOKIE as $k => $v) {
    $pos = strpos($k, "_");
    //делаем проверку по имени пользователя
    if(substr($k, 0, $pos) === $ruser) {
        // получить номер товара
        $id = substr($k, $pos+1);
        
        //получеие дданных о товаре по id 
        $item = Item::fromDb($id);
        //var_dump($item);
        // считаем общую стоимость всех товаров в корзине
        $total += $item->pricesale;
        // отрисовываем товар
        $item->drawForCart();
    }
}

echo '<hr>';
echo "<span class='ml-5 text-primary'>Total price: $total</span>";
echo '<button type="submit" class="btn btn-outline-primary ml-5" name="suborder" onclick=eraseCookie("'.$ruser.'")>Purchase order</button>';

echo '</form>';

// обработчик для оформленя заказа
if(isset($_POST['suborder'])) {
    $id_result = [];
    foreach($_COOKIE as $k => $v) {
        $pos = strpos($k, "_");
        //делаем проверку по имени пользователя
        if(substr($k, 0, $pos) === $ruser) {
            // получить номер товара
            $id = substr($k, $pos + 1);
            
            //получеие дданных о товаре по id 
            $item = Item::fromDb($id);
            //var_dump($item);
            // считаем общую стоимость всех товаров в корзине
            // отрисовываем товар
            array_push( $id_result, $item->sale()); // метод для оформления заказа
        }
    }
    $item->SMTP($id_result);
}
?>

<script>
    function eraseCookie(ruser) {
        $.removeCookie(ruser, { path: '/' });
    }
</script>