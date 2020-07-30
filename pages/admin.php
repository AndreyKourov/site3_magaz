<?php

if(!isset($_POST['addbtn'])) {

?>

<h3>Admin Forms</h3>
<hr>


<form action="index.php?page=4" method="post" enctype="multipart/form-data">
    
    <label for="catid">Category:
        <select name="catid" id="">
            <?php
             $pdo = Tools::connect();
             $list = $pdo->query("SELECT * FROM categories");
             while($row = $list->fetch()) {
                 echo "<option value='".$row['id']."'>".$row['category']."</option>";
             }
             
            
            ?>
        </select>
        </label>
    

    
        <div class="form-group">
            <label for="name">
                <input type="text" placeholder="input name"  name="name"> 
            </label>
        </div>
    

    
        <div class="form-group">
             <p>Incoming price and sale price</p>
            <div>
                <input type="number" name="pricein"> 
            </div>
            
            
        </div>
     

      
        <div class="form-group">
            <div>
                <input type="text" placeholder="pricesale" name="pricesale"> 
            </div>
        </div>
         

           
        <div class="form-group">
            <label for="info">
                <textarea class="d-block" name="info" id="info" cols="30" rows="10" placeholder="enter a description"></textarea>
            </label>
        </div>
         

              
        <div class="form-group">
            <label for="imagepath">
                <input type="file" name="imagepath"> 
            </label>
        </div>
            
        <button type="submit" class="btn btn-outline-primary" name="addbtn">Add good</button>

    
</form>


<?php
} else {
    if(is_uploaded_file($_FILES['imagepath']['tmp_name'])) {
        $path = "images/goods/" . $_FILES['imagepath']['name'];
        move_uploaded_file($_FILES['imagepath']['tmp_name'], $path); 
    }

    $name = trim($_POST['name']);
    $info = trim($_POST['info']);
    $catid = $_POST['catid'];
    $pricein = $_POST['pricein'];
    $pricesale = $_POST['pricesale'];


    //передаем значение в конструктор класса item
    $item = new Item($name, $catid, $pricein, $pricesale, $info, $path);
    $item->intoDb(); 

}
?>