<!DOCTYPE html>
<html>
<body>

<?php

$data = json_decode(file_get_contents("php://input"));
$data = (array) $data;

$first_name = $data["first_name"];
$last_name = $data["last_name"];
$email = $data["email"];
$country = $data["country"];
$phone = $data








?>

</body>
</html>