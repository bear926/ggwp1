<?php
include_once __DIR__ . '/google-api-php-client/vendor/autoload.php';
include_once "templates/base.php";

echo pageHeader("Google Drive");

/*************************************************
 * Ensure you've downloaded your oauth credentials
 ************************************************/
if (!$oauth_credentials = getOAuthCredentialsFile()) {
  echo missingOAuth2CredentialsWarning();
  return;
}

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/drive");
$service = new Google_Service_Drive($client);

// add "?logout" to the URL to remove a token from the session
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['upload_token']);
}

/************************************************
 * If we have a code back from the OAuth 2.0 flow,
 * we need to exchange that with the
 * Google_Client::fetchAccessTokenWithAuthCode()
 * function. We store the resultant access token
 * bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);

  // store in the session also
  $_SESSION['upload_token'] = $token;

  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

// set the access token as part of the client
if (!empty($_SESSION['upload_token'])) {
  $client->setAccessToken($_SESSION['upload_token']);
  if ($client->isAccessTokenExpired()) {
    unset($_SESSION['upload_token']);
  }
} else {
  $authUrl = $client->createAuthUrl();
}

// delete function
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  if($service->files->delete($id)) {
    $delete_id = TRUE;
  }
  else {
    $delete_id = FALSE;
  }
}

// download tmp file in google drive
$uploaddir = 'files/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
  $fname = $_FILES['userfile']['tmp_name'];
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && $client->getAccessToken()) {
    DEFINE("TESTFILE", $fname);
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($_FILES['userfile']['name']);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    $result = $service->files->create(
        $file,
        array(
          'data' => file_get_contents(TESTFILE),
          'mimeType' => $mime_type,
          'uploadType' => 'multipart'
        )
    );
    finfo_close($finfo);
  }
}

// get ftles list
if (!isset($authUrl)) {
  $files_list = $service->files->listFiles(array())->getFiles();
}

include_once "templates/upload.php";
?>