<?php
   $host = "localhost"; // Menyiapkan variabel 'host' untuk mendefinisikan nama server
   $user = "root"; // Menyiapkan variabel 'user' untuk mendefinisikan nama user database MySQL
   $password = ""; // Menyiapkan variabel 'password' untuk mendefinisikan password database MySQL
   $database = "id16080459_sumbawa"; // Menyiapkan variabel 'database' untuk mendefinisikan nama database MySQL
  
   $connect = mysql_connect($host,$user,$password); // Melakukan koneksi
   $selectdb = mysql_select_db($database,$connect); // Memilih database yang sudah didefinisikan dengan perintah 'mysql_select_db'
 
   if($connect){
      echo "Koneksi host database berhasil.<br/>";
   }else{
      echo "Koneksi host database gagal.<br/>";
   }
 
   if($selectdb){
      echo "Koneksi database berhasil.";
   }else{
      echo "Koneksi database gagal.";
   }
?>