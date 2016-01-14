<!DOCTYPE html>
<html>
<body>

<?php

require('../rslibrary/helper.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/rslibrary/site_config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/rslibrary/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/registration/models/VoucherUser.php');

$data = json_decode(file_get_contents("php://input"));
$data = (array) $data;

$first_name = $data["first_name"];
$last_name = $data["last_name"];
$email = $data["email"];
$country = $data["country"];
$phone = $data["phone"];
$age = $data["age"];
$gender = $data["gender"];
$industry = $data["industry"];
$experience = $data["experience"];
$lesson = $data["lesson"];

$create_date = filter_var($create_date, FILTER_SANITIZE_NUMBER_INT);
$first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
$last_name = filter_var($first_name, FILTER_SANITIZE_STRING);

$country = filter_var($country, FILTER_SANITIZE_STRING);
$phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
$age = filter_var($age, FILTER_SANITIZE_NUMBER_INT);
$gender = filter_var($gender, FILTER_SANITIZE_STRING);
$industry = filter_var($industry, FILTER_SANITIZE_STRING);
$experience = filter_var($experience, FILTER_SANITIZE_STRING);
$lesson = filter_var($lesson, FILTER_SANITIZE_STRING);

if (isset($email)) {
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
		$email = "";
}

$phone = preg_replace("/[^0-9]/", '', $phone);
if (!strlen($phone) == 10) $phone = '';

$myData = array(
		'first_name' => $first_name,
		'last_name' => $last_name,
		'email' => $email,
		'email_confirm' => $email,
		'voucher_code' => 'ShawAcademy',
		'tel_number' => $phone,
		
	);

$courseId = '';

$userId = createUpdateUser($email, $first_name, $last_name, $country, $password);

echo $userId."<br />"; 

$voucherUser = new VoucherUser($myData);
	$voucherUser->validVoucher = true;
	$voucherUser->set__Partner('ShawAcademy');
$voucherUser->saveToSalesforce();

/*$courses = getCourses(true);
while($course = $courses->fetch_assoc()){
    if ($course['CourseName']==$lesson) {
    	$courseId = $course['CourseId'] ;
    	break;
    }
}

echo $courseId;
echo assignCourseToUser($email, $courseId, null, 'GMT');
*/
?>

</body>
</html>