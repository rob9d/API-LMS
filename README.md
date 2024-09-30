# API-LMS

GitHub project ini bisa di-clone di folder htdocs (XAMPP local) / di folder www (Laragon)

1. SETUP
   Sehingga kurang lebih struktur path akan seperti ini:
   XAMPP: C:\xampp\htdocs\api
   Laragon: C:\laragon\www\api

   Untuk mengkonfigurasi koneksi ke database ada di folder 'includes/Database.php'

2. SQL
   Pada folder 'api' tersebut ada SQL yang bisa di-import ke DBMS local anda. Di dalam SQL tersebut telah disediakan create database.

3. POSTMAN
   Pada folder 'api' tersebut ada Postman Collection yang bisa di-import ke aplikasi Postman untuk mencoba API tersebut.

   Pada Postman bisa membuat variable 'URL' dengan value 'http://localhost/api/'

4. Unit Test
   Di dalam folder 'api' tersebut bisa dijalankan Terminal, lalu lakukan 2 perintah berikut untuk melakukan testing pada Books dan Authors

   Authors: vendor/bin/phpunit --bootstrap vendor/autoload.php test/AuthorsTest.php
   Books: vendor/bin/phpunit --bootstrap vendor/autoload.php test/BooksTest.php
