<?php
session_start();
require "remember.php";
global $conn;

if (!isset($_SESSION["accountType"]) || $_SESSION["accountType"] != "admin") {
    header("Location: index.php");
    exit;
}

    if (isset($_GET['id'])) {
        $accountId = $_GET['id'];

        $getData = $conn->prepare("SELECT u.id AS userProfileId, u.profilePicture FROM userprofile u JOIN accounts a ON u.id = a.userProfileId WHERE a.id = ?");
        $getData->bind_param("i", $accountId);
        $getData->execute();
        $resData = $getData->get_result();

        if ($resData->num_rows == 1) {
            $row = $resData->fetch_assoc();
            $userProfileId = $row['userProfileId'];
            $profilePic = $row['profilePicture'];

            $delAcc = $conn->prepare("DELETE FROM accounts WHERE id = ?");
            $delAcc->bind_param("i", $accountId);
            $delAcc->execute();
            $delAcc->close();

            $delProf = $conn->prepare("DELETE FROM userprofile WHERE id = ?");
            $delProf->bind_param("i", $userProfileId);
            $delProf->execute();
            $delProf->close();

            if ($profilePic !== 'profilePictures/default.jpg' && file_exists($profilePic)) {
                unlink($profilePic);
            }

            header("Location: adminAccounts.php");
            exit;
        }
        else {
            echo "User not found.";
        }

        $getData->close();
    }
$conn->close();