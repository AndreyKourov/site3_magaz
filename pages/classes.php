<?php

class Tools {
    static function connect($host="localhost:3306", $user="root", $pass="123456", $dbname="shop") {
        // PDO (PHP data object) - механизм взаимодействия с СУБД(система управле базами данных)
        // PDO позвояет облегчить рутинные задачи при выполнии запросов и содержит защиту при работе с СУБД

        // формировка строки для создания обьекта PDO
        // определим DSN (Data Source Name) — сведения для подключения к базе, представленные в виде строки.

        $cs = 'mysql:host='.$host.';dbname='.$dbname.';charset=utf8';

        // массив опций для создания PDO
        $options = array(
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES UTF8'
        );

        try {
            // пробуем создать PDO
            $pdo = new PDO($cs, $user, $pass, $options);
            return $pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
    static function register($login, $pass, $path) {
        $login = trim(htmlspecialchars($login));
        $pass = trim(htmlspecialchars($pass));
        $imagepath = trim(htmlspecialchars($path));

        if($login == '' || $pass == '' ) {
            echo '<h3 class="text-danger">Fill all fields</h3>';
            return false;
        }
        if(mb_strlen($login) < 3 || mb_strlen($login) > 30 || mb_strlen($pass) < 3 || mb_strlen($pass) > 30 ) {
            echo '<h3 class="text-danger">Inconnect Lenght</h3>';
            return false;
        }
        Tools::connect();
        //создаем экземпляр класса Customer и передаем в его конструкотр логин пароль и путь изображеия
        $customer = new Customer($login, $pass, $imagepath);
        //после того как мы передали их в конструктор внутри него значения будут записаны в св-ва класса
        // и мы можем вызвать метод занесения этих жанных в таблицу customer  через метод intoDb()
        $customer->intoDb();
        return true;
    }

    static function login($login, $pass) {
        $name = trim(utf8_encode(htmlspecialchars($login)));
        $pass = trim(utf8_encode(htmlspecialchars($pass)));
    
        if ($name == "" || $pass == "") {
            echo "<h3 class='text-danger'>Заполните все поля</h3>";
            return false;
        }
        
        if(strlen($name) < 3 || strlen($name) > 30 || strlen($pass) < 3 || strlen($pass) > 30) {
            echo "<h3 class='text-danger'>От 0 до 30 символов</h3>";
            return false;
        }
    
        $pdo = Tools::connect();
        $ps = $pdo->prepare("SELECT login, pass, roleid FROM customers WHERE login='$name'");
        $ps->execute();      
        while($row = $ps->fetch()) {
            if($name == $row['login'] && $pass == $row['pass']) {
                $_SESSION['ruser'] = $name;
                if($row['roleid'] == 1) { 
                    $_SESSION['radmin'] = $name; 
                } 
                return true;
            } else {
                return false;
            } 
        }
    }
}

class Customer {
    public $id;
    public $login;
    public $pass;
    public $roleid;
    public $discount;
    public $total;
    public $imagepath;

    function __construct($login, $pass, $imagepath, $id=0) {
        $this->login = $login;
        $this->pass = $pass;
        $this->imagepath = $imagepath;
        $this->id = $id;

        $this->total = 0;
        $this->discount = 0;
        $this->roleid = 2;
    }
    // ORM (Object Relational Mapping) - обьктно реалиционное отображение. Это механизм работы сущности в связи с БД.

    //внести покупателя в таблицу
    function intoDb() {
        try {
            $pdo = Tools::connect();
            //выполнение запроса через PDO на внесение данных
            $ps = $pdo->prepare("INSERT INTO customers(login, pass, roleid, discount, total, imagepath) VALUES (:login, :pass, :roleid, :discount, :total, :imagepath)");

            //разименование массива. Мы преобразуем обьект класса $this в массив
            $ar = (array) $this;
            //:id, :login, :pass, :roleid, :discount, :total, :imagepath      содержит id

            // var_dump($ar);
            array_shift($ar); //удаляем первый элемент массива т.е id
            //:id, :login, :pass, :roleid, :discount, :total, :imagepath      НЕ содержит id

            // выплним запрос
            $ps->execute($ar);
        } catch(PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
    // получаем данные о созданном пользователе из таблицы
    static function fromDb($id) {
        $customer = null;
        try {
            $pdo = Tools::connect();
            $ps=$pdo->prepare("SELECT * FROM customers WHERE id=?");
            // выполняем выбор всех данных о пользователе по $id получаемого в качестве параметра в фцию fromDb
            // и заносим его в массив, ибо execute этого требует. При выполнении execute $id будет подставлен 
            // вместо символа ? при подготовке (метод prepare)
            $res = $ps->execute(array($id));
            //перебираем анные о полученном пользователе и заносим его в ассоциативный массив $row
            $row = $res->fetch();
            $customer = new Customer($row['login'], $row['pass'], $row['imagepath'], $row['id'] );
            return $customer;

        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}

class Item {
    public $id;
    public $itemname;
    public $catid;
    public $pricein;
    public $pricesale;
    public $info;
    public $rate;
    public $imagepath;
    public $action;

    function __construct($itemname, $catid, $pricein, $pricesale, $info, $imagepath, $rate=0, $action=0, $id=0) {
        $this->id = $id;
        $this->itemname = $itemname;
        $this->catid = $catid;
        $this->pricein = $pricein;
        $this->pricesale = $pricesale;
        $this->info = $info;
        $this->rate = $rate;
        $this->imagepath = $imagepath;
        $this->action = $action;
    }

    function intoDb() {
        try {
            $pdo = Tools::connect();
            //выполнение запроса через PDO на внесение данных
            $ps = $pdo->prepare("INSERT INTO items( itemname, catid, pricein, pricesale, info, imagepath, rate, action) VALUES (:itemname, :catid, :pricein, :pricesale, :info, :imagepath, :rate, :action)");
        
            $ar = (array) $this;
            array_shift($ar);
            $ps->execute($ar);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    static function fromDb($id) {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("SELECT * FROM items WHERE id=?");
            $ps->execute([$id]);
            $row = $ps->fetch();
            $item = new Item($row['itemname'], $row['catid'], $row['pricein'], $row['pricesale'], $row['info'], $row['imagepath'], $row['rate'], $row['action'], $row['id']);
            return $item;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    //полуение товаров
    static function getItems($catid = 0) {
        try {
            $pdo = Tools::connect();
            if($catid == 0) {
                //выбираем все товары
                $ps=$pdo->prepare("SELECT * FROM items");
                $ps->execute();
            } else {
                //выбираем товары определенной категории
                $ps=$pdo->prepare("SELECT * FROM items WHERE catid=?");
                $ps->execute([$catid]);
            }
            while($row = $ps->fetch()) {
                // создаем экземпляр класа Item
                $item = new Item($row['itemname'], $row['catid'], $row['pricein'], $row['pricesale'], $row['info'], $row['imagepath'], $row['rate'], $row['action'], $row['id']);
                //массив который хранит все экземплры класса товаров. т.е данные о товарах из таблицы
                $items[] = $item;
            }
            //возвращаем вссе товары в точку вызова (странница catalog.php)
            return $items;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    function drawItem() {

        echo '<div class="col-sm-6 col-md-4 col-lg-3 item-card mb-3">';
            echo '<img class="card-img-top item-card__img" src="'.$this->imagepath.'" alt="Card image cap">';
            echo '<div class="card-body">';
                echo '<h4 class="card-title">'.$this->pricesale.'&nbsp;$</h4>';
                    
                    echo "<div class='row item-card__title'>
                        <a href='pages/item_info.php?name=".$this->id."' class='ml-2 float-left' target='_blank'>".$this->itemname."</a>";
                    echo "<span class='float-right mr-0 ml-auto'>".$this->rate."&nbsp;rate</span>";
                    echo '</div>';
                
                    echo '<p class="card-text">'.$this->info.'</p>';
                
                echo '<div class="my-1 text-justify item-card__cart">';
                $ruser = '';
                if(!isset($_SESSION['ruser'])) {
                    $ruser = 'cart_'.$this->id;
                } else {$ruser = $_SESSION['ruser']."_".$this->id;}
                echo "<button class='btn btn-outline-primary btn-block' onclick=createCookie('".$ruser."','".$this->id."')>Add to cart</button>";
                echo '</div>';

            echo '</div>';
        echo '</div>';

        /*
        echo '<div class="col-sm-6 col-md-3 item-card border mb-4">';
            //верхушка товара
            echo '<div class="mt-1 bg-dark">';
            echo "<a href='pages/item_info.php?name=".$this->id."' class='ml-2 float-left' target='_blank'>".$this->itemname."</a>";
            echo "<span class='mr-2 float-right'>".$this->rate."&nbsp; rate </span>";  // &nbsp это просто пробел
            echo '</div>';
        
            //изображение товара
            echo '<div clss="row">';
            echo "<div class='mt-1 item-card__img'>";
            echo "<img src='".$this->imagepath."' class='img-fluid'>";
            echo '</div>';
            echo '</div>';

            //цена товара
            echo "<div class='mt-1 text-center item-card__price'>";
            echo '<span class="mr-3 float-right">'.$this->pricesale.' </span>';
            echo '</div>';

            //описание товара
            echo "<div class='mt-1 text-justify bg-primary item-card__title'>";
            echo "<span>".$this->info." </span>";
            echo '</div>';
            
            // кнопка добавления в корзину
            echo "<div class='mt-1 text-justify bg-primary item-card__basket'>";
            $ruser = '';
            //проверка на зареистрированного пользователя 
            if(!isset($_SESSION['ruser'])) {
                $ruser = 'cart_'.$this->id;
            } else {$ruser = $_SESSION['ruser']."_".$this->id;}

            /*
            if(!isset($_SESSION['reg'] || $_SESSION['reg'] == '')) {
                $ruser = 'cart_'.$this->id;
            } else {
                $ruser = $_SESSION['reg'] . "_" . $this->id;
            }         
            $ruser = 'cart_'.$this->id; // потом убрать после раскомента выше
            */
            //echo "<button class='btn btn-primary btn-lg btn-block' onclick=createCookie('".$ruser."','".$this->id."')>Add to cart</button>";
            //echo '</div>';


        //echo '</div>';
    
    }

    // меод для отрисовки выбранных товаров в корзине
    public function drawForCart() {
        echo "<div class='row m-2'>";
        echo "<img src='".$this->imagepath."' class='col-1 img-fluid'>";
        echo "<span class='col-3'>$this->itemname</span>";
        echo "<span class='col-3'>$this->pricesale</span>";
        //$ruser = "cart_".$this->id;
        if(!isset($_SESSION['ruser'])) {
            $ruser = 'cart_'.$this->id;
        } else {$ruser = $_SESSION['ruser']."_".$this->id;}
        echo "<button class='btn btn-danger' onclick=eraseCookie('".$ruser."')>x</button>";
        echo "</div>";
    }

    // метод для оформления заказа
    function sale() {
        try {
            $pdo = Tools::connect();
            //$ruser = 'admin';
            //if(isset($_SESSION['reg'] && $_SESSION['reg'] !== '')) {
            //    $ruser = $_SESSION['reg'];
            //}
            $ruser = 'cart';
            if(isset($_SESSION['ruser'])) {
                $ruser = $_SESSION['ruser'];
            }
            //изменить у покупатедя общу стоимость купленных товароов
            $upd = "UPDATE customers SET total=total+? WHERE login=?";
            $ps = $pdo->prepare($upd);
            $ps->execute([$this->pricesale, $ruser]);

            //создаем данные о покупке товара с занесением в таблицу sales
            $ins = "INSERT INTO sales(customername, itemname, pricein, pricesale, datesale) VALUES(?,?,?,?,?)";
            $ps = $pdo->prepare($ins);
            $ps->execute([$ruser, $this->itemname, $this->pricein, $this->pricesale, @date("Y/m/d H:i:s")]);
            return $this->id;

        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    function SMTP($id_result) {
        //подключить модуль PHPMailer
        
        require_once("PHPMailer/PHPMailerAutoload.php");
        require_once("private/private_data.php");

        $mail = new PHPMailer;
        $mail->CharSet = "UTF-8";

        //настраиваем параметры SMTP (почтовый протокол пердачи данных)
        $mail->isSMTP();
        $mail->SMTPAuth = true;

        $mail->Host = 'ssl://smtp.mail.ru';
        $mail->Port = 465;
        $mail->Username = MAIL;
        $mail->Password = PASS; 

        //от кого
        $mail->setFrom('andreykourov@mail.ru', 'SHOP ANDREY');

        // кому petrovski_a@itstep.org
        $mail->addAddress('petrovski_a@itstep.org', 'ADMIN');

        // тема письма
        $mail->Subject = 'Решение ДЗ 38 от Коурова Андрея';

        // Тело письма
        $body = "<table cellspacing='0' cellpadding='0' border='2' width='800' style='background-color: green!important'>";
        $i=0;
        

        $arrItem = [];
        
        foreach ($id_result as $id) {
            $item = self::fromDb($id);
            $path = $item->imagepath;
            $cid = md5($path);
            array_push($arrItem, $item->itemname, $item->pricesale, $item->info);
            $mail->AddEmbeddedImage($path, $cid, 'item_'.$i);
            //$mail->AddEmbeddedImage($item->imagepath, 'item'.++$i);
            $body .= "<tr>
                        <th>$item->itemname</th>
                        <td>$item->pricesale</td>
                        <td>$item->info</td>
                        <td><img src='cid:$cid' alt='item_$i' height='100'></td>
                        </tr>";
                        ++$i;
        } 
        $body .= '</table>';

        $mail->msgHTML($body);
        $mail->send();

        //CSV
        try {
            $csv = new CSV("table/exel_file.csv");
            $csv->setCSV($arrItem);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

class CSV {
    private $csv_file = null;

    public function __construct($csv_file) {
        $this->csv_file = $csv_file;  //
    }

    function setCSV($arrItem) {
        $items = array_chunk($arrItem, 3);
        //открываем csv фаил для дозаписи
        // +- если фаил не создан то создать его
        // a(append) дозаписать в конец файла
        $file = fopen($this->csv_file, 'a+');
        /*foreach ($arrItem as $item) {
            
            fputcsv($file, [$item]);
        }
        */
        foreach($items as $item) {
            $itemcsv = implode("; ", $item);
            fputcsv($file, $item);
        }
        fclose($file);
    }
}