<?php

echo '<h3>Registration Form</h3>';

if(!isset($_POST['regbtn'])) {

?>

<form action="index.php?page=3" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="login">Login:
            <input type="text" class="form-control" name="login">
        </label>
    </div>

    <div class="form-group">
            <label for="pass1">Pass:
                <input type="password" class="form-control" name="pass1">
            </label>
    </div>
    <div class="form-group">
            <label for="pass2">Confirm Pass:
                <input type="password" class="form-control" name="pass2">
            </label>
    </div>
    <div class="form-group">
            <label for="imagepath">Select image:
                <input type="file" class="form-control" name="imagepath">
            </label>
    </div>
    <button type="submit" class="btn btn-primary" name="regbtn">Register</button>
</form>

<?php
}else {
    // обработка выбранного избражения и перенос в папку images
    if(is_uploaded_file($_FILES['imagepath']['tmp_name'])) {
        $path = "images/users/".$_FILES['imagepath']['name'];
        move_uploaded_file($_FILES['imagepath']['tmp_name'], $path);
    }
    //передаем данные из полей в ф-ию регистрации
    if(Tools::register($_POST['login'], $_POST['pass1'], $path)) {
        echo '<h3 class="text-success">NEW USER ADDED</h3>';
    }
}

?>