<?php

$server='172.17.82.118';
$user='root';
$dbname='openemr';
$password='root';
echo "<br>$server<br>$user<br>$password<br>$port<br>$dbname<br>";

if (mysqli_connect($server, $user, $password, $dbnamei, $port)) {
    echo 'I connected';
} else {
    echo 'I could not connect';
}


echo "<br><hr><br>";
$server='172.17.82.118';
$user='openemr';
$dbname='openemr';
$password='openemr';
echo "<br>$server<br>$user<br>$password<br>$port<br>$dbname<br>";

if (mysqli_connect($server, $user, $password, $dbnamei, $port)) {
    echo 'I connected';
} else {
    echo 'I could not connect';
}
