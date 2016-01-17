<!DOCTYPE html>
<html>
<body>

<?php

require('../rslibrary/helper.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/rslibrary/site_config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/rslibrary/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/registration/models/VoucherUser.php');
include_once $_SERVER['DOCUMENT_ROOT'] . '/rslibrary/salesforce.php';


$data = json_decode(file_get_contents("php://input"));
$data = (array) $data;

$first_name = $data["first_name"];
$last_name = $data["last_name"];
$email = $data["email"];
$user_country = $data["country"];
$phone = $data["phone"];
$age = $data["age"];
$gender = $data["gender"];
$industry = $data["industry"];
$experience = $data["experience"];
$lesson = $data["lesson"];

echo $first_name.$last_name.$lesson;

$create_date = filter_var($create_date, FILTER_SANITIZE_NUMBER_INT);
$first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
$last_name = filter_var($first_name, FILTER_SANITIZE_STRING);

$user_country = filter_var($user_country, FILTER_SANITIZE_STRING);
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

$countries = getCountries(true);
while($country = $countries->fetch_assoc()){
    if ($country['CountryName']==$user_country) {
    	$country_code = $country['CountryCode'] ;
    	break;
    }
}

$myData = array(
		'first_name' => $first_name,
		'last_name' => $last_name,
		'email' => $email,
		'email_confirm' => $email,
		'voucher_code' => 'ShawAcademy',
		'tel_number' => $phone,
		'UserCountry' => $country_code,
	);

$courseId = '';

$password = 'password';

$userId = createUpdateUser($email, $first_name, $last_name, $country_code, $password);

echo $userId."<br />"; 

$validCourse = false;
$courses = getCourses(true);
while($course = $courses->fetch_assoc()){
    if (!strcasecmp($course['CourseName'],$lesson) && !ctype_digit($course['CourseId']) && ($course['SalesforceFieldMapping']!= null || $course['SalesforceFieldMapping']!='')) {
    	$courseId = $course['CourseId'] ;
    	$validCourse = true;
    	break;
    }
}

echo $courseId;
echo assignCourseToUser($email, $courseId, null, 'GMT');


$voucherUser = new VoucherUser($myData);
	$voucherUser->validVoucher = true;
            $voucherUser->saveToSalesforce(); 

$partner_owner = "ShawAcademy";
echo "SSSS:".$courseId;

if (validCourse)
{
$sf_result = sfCheck($email);
			$sf_id = $sf_result['Id'];

			// Update or create lead in Salesforce
		if(!$sf_id || $sf_id == "0") {
			$sf_id = sfNewLead(
							array(
								"Email" => $email, 
								"Company" => "[not provided]", 
								"FirstName" => $first_name, 
								"LastName" => $last_name, 
								"Phone" => $phone,
								"Country" => $country_code,
								"Password__c" => $password,
								"Partner_Owner_".$courseId."__c" => "$partner_owner",
								"Timezone__c" => $us_timezone
								)
							);
		} else {
			sfUpdateLead($sf_id, 
							array(
								"FirstName" => $first_name, 
								"LastName" => $last_name, 
								"Company" => "[not provided]", 
								"Phone" => $phone,
								"Country" => $selected_country,
								"Password__c" => $password,
								"Partner_Owner_".$courseId."__c" => "$partner_owner",
								"Timezone__c" => $us_timezone
								)
						);
		}

echo "<br /> Sent to Salesforce! <br />";
}

require_once 'mandrill-api-php/src/Mandrill.php';


try {
    $mandrill = new Mandrill('C0q1Z9mx9A0YDraFeoRIlg');
    $template_name = 'books-free-course-test';
    $template_content = array(
        array(
            'name' => 'example name',
            'content' => 'example content'
        )
    );
    $message = array(
        'html' => '<p>Example HTML content</p>',
        'text' => 'Example text content',
        'subject' => 'example subject',
        'from_email' => 'noreply@shawacademy.com',
        'from_name' => 'Example Name',
        'to' => array(
            array(
                'email' => 'rohit.n@shawacademy.com',
                'name' => 'Recipient Name',
                'type' => 'to'
            )
        ),
        'headers' => array('Reply-To' => 'message.reply@example.com'),
        'important' => false,
        'track_opens' => null,
        'track_clicks' => null,
        'auto_text' => null,
        'auto_html' => null,
        'inline_css' => null,
        'url_strip_qs' => null,
        'preserve_recipients' => null,
        'view_content_link' => null,
        'bcc_address' => 'message.bcc_address@example.com',
        'tracking_domain' => null,
        'signing_domain' => null,
        'return_path_domain' => null,
        'merge' => true,
        'merge_language' => 'mailchimp',
        'global_merge_vars' => array(
            array(
                'name' => 'merge1',
                'content' => 'merge1 content'
            )
        ),
        'merge_vars' => array(
            array(
                'rcpt' => 'recipient.email@example.com',
                'vars' => array(
                    array(
                        'name' => 'merge2',
                        'content' => 'merge2 content'
                    )
                )
            )
        )
    );
    $async = false;
    $ip_pool = 'Main Pool';
    $send_at = 'example send_at';
    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool);
    print_r($result);
    /*
    Array
    (
        [0] => Array
            (
                [email] => recipient.email@example.com
                [status] => sent
                [reject_reason] => hard-bounce
                [_id] => abc123abc123abc123abc123abc123
            )
    
    )
    */
} catch(Mandrill_Error $e) {
    // Mandrill errors are thrown as exceptions
    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
    throw $e;
}


?>

</body>
</html>