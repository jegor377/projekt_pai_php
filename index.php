<?php
$title = "Strona główna";
require_once("templates/header.php");

if (!is_writable(session_save_path())) {
	echo 'Session path "'.session_save_path().'" is not writable for PHP!'; 
}
?>

<h1>Hej</h1>

<?php
require_once("templates/footer.php");