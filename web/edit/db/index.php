<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

$TAB = 'DB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

if (empty($_GET['database'])) {
    header("Location: /list/db/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

$v_database = escapeshellarg($_GET['database']);
exec (VESTA_CMD."v-list-database ".$user." ".$v_database." 'json'", $output, $return_var);
check_return_code($return_var,$output);
if (empty($_SESSION['error_msg'])) {
    $data = json_decode(implode('', $output), true);
    unset($output);
    $v_username = $user;
    $v_database = $_GET['database'];
    $v_dbuser = $data[$v_database]['DBUSER'];
    $v_password = "••••••••";
    $v_host = $data[$v_database]['HOST'];
    $v_type = $data[$v_database]['TYPE'];
    $v_charset = $data[$v_database]['CHARSET'];
    $v_date = $data[$v_database]['DATE'];
    $v_time = $data[$v_database]['TIME'];
    $v_suspended = $data[$v_database]['SUSPENDED'];
    if ( $v_suspended == 'yes' ) {
        $v_status =  'suspended';
    } else {
        $v_status =  'active';
    }

    // Action
    if (!empty($_POST['save'])) {
        $v_username = $user;

        // Change database username
        if (($v_dbuser != $_POST['v_dbuser']) && (empty($_SESSION['error_msg']))) {
            $v_dbuser = preg_replace("/^".$user."_/", "", $_POST['v_dbuser']);
            $v_dbuser = escapeshellarg($v_dbuser);
            if ($v_password != $_POST['v_password']) {
                // Change username and password
                $v_password = escapeshellarg($_POST['v_password']);
                exec (VESTA_CMD."v-change-database-user ".$v_username." ".$v_database." ".$v_dbuser." ".$v_password, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                $v_dbuser = $user."_".preg_replace("/^".$user."_/", "", $_POST['v_dbuser']);
                $v_password = "••••••••";
                $v_pw_changed = 'yes';
            } else {
                // Change only username
                exec (VESTA_CMD."v-change-database-user ".$v_username." ".$v_database." ".$v_dbuser, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                $v_dbuser = $user."_".preg_replace("/^".$user."_/", "", $_POST['v_dbuser']);
            }
        }

        // Change only database password
        if (($v_password != $_POST['v_password']) && (!isset($v_pw_changed)) && (empty($_SESSION['error_msg']))) {
            $v_password = escapeshellarg($_POST['v_password']);
            exec (VESTA_CMD."v-change-database-password ".$v_username." ".$v_database." ".$v_password, $output, $return_var);
            check_return_code($return_var,$output);
            $v_password = "••••••••";
            unset($output);
        }
        if (empty($_SESSION['error_msg'])) {
            $_SESSION['ok_msg'] = __('Changes has been saved.');
        }
    }
}

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_db.html');
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
