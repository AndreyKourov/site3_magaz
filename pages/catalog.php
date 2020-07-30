<h3>Catalog page</h3>

<hr>

<form action="index.php?page=1" method="post">
    <div>
        <select name="catid" class="mb-3" onchange="getItemsCat(this.value)">
            <option value="0">Select category:</option>
            <?php
                $pdo=Tools::connect();
                $ps=$pdo->prepare("SELECT * FROM categories");
                $ps->execute();
                //Добавляем все категории в otion 
                while($row=$ps->fetch()) {
                    echo "<option value=".$row['id'].">".$row['category']."</option>";
                }
            ?>
        </select>
    </div>
    <?php
    //получаем все товары из метода getItems()
    echo '<div id="result" class="row">';
    $items = Item::getItems(); // получаем массив экземпляров товаров
    foreach ($items as $item) {
        //var_dump($item);
        // вызываем метод отрисвки крточки товара для текущего экземпляра товара
        $item->drawItem();        
    }
    echo '</div>';
    ?>
</form>

<script>
    function getItemsCat(cat) {
        if(window.XMLHttpRequest) {
            ao = new XMLHttpRequest();
        } else {
            ao = new ActiveXObject('Microsoft.XMLHTTP');
        }
        ao.onreadystatechange = function () {
            if(ao.readyState === 4 && ao.status === 200) {
                document.getElementById('result').innerHTML = ao.responseText;
            }
        }
        ao.open('POST', 'pages/lists.php', true);
        ao.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ao.send("cat="+cat);
    }

    //создаем функцию занесения товара в куки
    function createCookie(ruser, id) {
        $.cookie( ruser, id, { expires: 2, path: '/' });
    }

</script>


