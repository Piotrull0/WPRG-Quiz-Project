<?php

require "db_conn.php";
global $conn;

if (!isset($_SESSION['email']) && isset($_COOKIE['usrId'])) {

    $usrId = $_COOKIE['usrId'];

    $gval = $conn->prepare("SELECT a.email, a.password, a.accountType, u.nickname FROM accounts a JOIN userProfile u on u.id = a.userProfileId WHERE a.id = ?;");
    $gval->bind_param("i", $usrId);
    $gval->execute();
    $result = $gval->get_result();
    $row = mysqli_fetch_array($result);

    $_SESSION['email'] = $row['email'];
    $_SESSION['password'] = $row['password'];
    $_SESSION['nickname'] = $row['nickname'];
    $_SESSION['accountType'] = $row['accountType'];

    $gval->close();
}

