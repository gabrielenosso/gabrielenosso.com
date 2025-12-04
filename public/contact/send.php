<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo '{"success":false,"error":"Method not allowed"}';
    exit;
}

$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$message = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if ($email === "" || $message === "") {
    echo '{"success":false,"error":"All fields are required"}';
    exit;
}

if (strpos($email, "@") === false) {
    echo '{"success":false,"error":"Invalid email address"}';
    exit;
}

// Clean input
$email = str_replace("\n", "", str_replace("\r", "", $email));
$message = strip_tags($message);

// Send email
$to = "gabrielenosso@gmail.com";
$subject = "Contact from gabrielenosso.com";
$body = "New message from your website:\n\nFrom: " . $email . "\n\nMessage:\n" . $message;
$headers = "From: noreply@gabrielenosso.com\r\nReply-To: " . $email . "\r\nContent-Type: text/plain; charset=UTF-8";

$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo '{"success":true}';
} else {
    echo '{"success":false,"error":"Could not send email"}';
}
