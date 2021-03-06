<?php
    session_start();
  if(!$_SESSION)
  {
    echo "<script> alert ('ADMINE ÖZELDIR!'); </script>";
    header("refresh:0;url=index.php");
  }
  else{ include 'database.php';
  $type=array();
  $sqlsorgula=mysqli_query($db,"select * from usertypes where User_UserName='".$_SESSION["oturumacan"]."'");
  while($row = mysqli_fetch_array($sqlsorgula)){$type[] = $row['UserType_Name'];}
  ?>
<!DOCTYPE html>
<html>
<head>
<title>ÜRÜN ONAYLAMA</title>
<link rel="stylesheet"  href="css/reset.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet"  href="bootstrap/css/bootstrap.css">
<link rel="stylesheet"  href="css/style.css">
</head>
<body>
<div id="nav">
  <div class="container">
    <div id="user">
      <label><?php echo $_SESSION["oturumacan"];?></label>
    </div>
    <div id="navbar">
      <ul>
        <a href="#"><li id="mobile">MENU</li></a>
        <?php //kullanıcı tipine göre menü görseli ayarlanır.
        for($x=0;$x<count($type);$x++){ 
          if($type[$x]=='Admin'){?>
            <li id="down">Admin Onay
              <ul class="dropdown">
                <a href="item-approval.php" class="active"><li>Ürün Onay</li></a>
                <a href="money-approval.php"><li>Para Onay</li></a>
              </ul>
            </li>
          <?php } elseif($type[$x]=='Alıcı'){?>
            <a href="add-money.php"><li>Para Ekle</li></a>
            <a href="get-item.php"><li>Ürün Alma</li></a>
            <a href="report.php"><li>Rapor</li></a>
          <?php } elseif($type[$x]=='Satıcı'){?>
            <a href="add-item.php"><li>Ürün Ekle</li></a>
            <?php if(count($type)<2){?> <a href="report.php"><li>Rapor</li></a> <?php } 
          } ?>
      <?php } ?>
      <a href="home.php"><li>Profil</li></a>
      </ul>
    </div>
  </div>
</div>
<div class="container">
  <div class="col-sm-12 add top">
    <h1>Onaylanacak Ürünler</h1>
    <?php $sqlürün=mysqli_query($db,"select * from useritems where Position_Id=3");
    if(mysqli_num_rows ($sqlürün)!=0){?>
    <table class="table table-hover">
    <thead>
      <tr >
        <th>Ürün Sahibi</th>
        <th>Ürün Adı</th>
        <th>Ürün Miktarı</th>
        <th>Ürün Birim Fiyatı</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row=mysqli_fetch_array($sqlürün)) {
        $sqlitem_name=mysqli_fetch_array(mysqli_query($db,"select Item_Name from items where Item_Id='".$row["Item_Id"]."'"));
        ?>
      <form method="post" >
        <tr class="active">
          <td><?php echo $row["User_UserName"]; ?></td>
          <td><?php echo $sqlitem_name[0]; ?></td>
          <td><?php echo $row["UserItem_Amount"]; ?></td>
          <td><?php echo $row["UserItem_Unit_Price"]; ?></td>
          <input type="text" name="yaz" value="<?php echo $row['UserItem_Id'];?>" style="display:none;">
          <td><input type="submit" name="onay" value="Onay"><input type="submit" name="ret" value="Ret"></td>
        </tr>
      </form>
    <?php }?>
    </tbody>
    </table>
    <?php } else{?>
      <label>Onaylanacak ürün bulunmamakta.</label>
    <?php }?>
  </div>
</div>
<div id="footer">
  <div class="container">
     <div id="footertext">
       <p>© Copyright 2021 Ceyda Özgenç Tüm Hakları Saklıdır. </p>
     </div>
  </div>
</div>
<?php 
  if(p("onay")){
    $id=p("yaz");
    $urun=mysqli_fetch_array(mysqli_query($db,"select * from useritems where UserItem_Id=$id"));
    $bekleyen=mysqli_fetch_array(mysqli_query($db,"select * from loginuseritem where Item_Id=".$urun["Item_Id"]." and   LoginUserItem_Unit_Price=".$urun["UserItem_Unit_Price"]));
    if($bekleyen[0]){
      if($urun["UserItem_Amount"]>=$bekleyen["LoginUserItem_Amount"]){
         $newamount=$urun["UserItem_Amount"]-$bekleyen["LoginUserItem_Amount"];
         mysqli_query($db,"insert into useritems (User_UserName,Item_Id,UserItem_Amount,UserItem_Unit_Price,UserItem_Type,UserItem_Time,Position_Id) values('".$bekleyen["User_UserName"]."',".$bekleyen["Item_Id"].",".$bekleyen["LoginUserItem_Amount"].",".$bekleyen["LoginUserItem_Unit_Price"].",'Alındı',CURDATE(),1);");
         mysqli_query($db,"insert into useritems (User_UserName,Item_Id,UserItem_Amount,UserItem_Unit_Price,UserItem_Type,UserItem_Time,Position_Id) values('".$urun["User_UserName"]."',".$bekleyen["Item_Id"].",".$bekleyen["LoginUserItem_Amount"].",".$bekleyen["LoginUserItem_Unit_Price"].",'Satıldı',CURDATE(),1);");
         if ($newamount!=0) {
            mysqli_query($db,"update useritems set Position_Id=1, UserItem_Amount=$newamount  where UserItem_Id=$id");
            mysqli_query($db,"delete from loginuseritem where LoginUserItem_Id=".$bekleyen["LoginUserItem_Id"]);
          }
      }
      else{
        $newamount=$bekleyen["LoginUserItem_Amount"]-$urun["UserItem_Amount"];
        mysqli_query($db,"insert into useritems (User_UserName,Item_Id,UserItem_Amount,UserItem_Unit_Price,UserItem_Type,UserItem_Time,Position_Id) values('".$bekleyen["User_UserName"]."',".$urun["Item_Id"].",".$urun["UserItem_Amount"].",".$urun["UserItem_Unit_Price"].",'Alındı',CURDATE(),1);");
        mysqli_query($db,"update useritems set UserItem_Type='Satıldı', Position_Id=1, UserItem_Time=CURDATE() where UserItem_Id=$id");
        mysqli_query($db,"update loginuseritem set UserItem_Amount=$newamount  where UserItem_Id=$id");
      }
    }
    else{
      mysqli_query($db,"update useritems set Position_Id=1 where UserItem_Id=$id");
      header("refresh:0;url=item-approval.php");
    }
  }
  if(p("ret")){
    $id=p("yaz");
    mysqli_query($db,"update useritems set Position_Id=2 where UserItem_Id=$id");
    header("refresh:0;url=item-approval.php");
  }
  ?>
</body>
</html>
<?php } ?>