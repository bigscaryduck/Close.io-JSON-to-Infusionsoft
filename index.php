<?php
session_start();

require_once 'vendor/autoload.php';
$infusionsoft = new \Infusionsoft\Infusionsoft(array(
    'clientId' => 'dtps5t2tkvwwstrfnv535qc7',
    'clientSecret' => 'U9Z7MT7NSY',
    'redirectUri' => 'http://192.168.33.10/sync/',
));

$string = file_get_contents("import.json");
$close = json_decode($string);

?>
<html>
<head>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <style>
    table .collapse.in {
      display:table-row;
    }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <?php
          $z = 0;
          foreach($close as $x):
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><?=$x->display_name?></h3>
          </div>
          <div class="panel-body">
            <table class="table table-bordered">

              <?php
                foreach($x->activities as $a):
              ?>
              <tr>
                <td><?=$a->_type?></td>
              </tr>
              <?php
                endforeach;
              ?>

            </table>
          </div>
        </div>
          <h3></h3>
        <?php
          $z++;
          endforeach;
        ?>
      </div>
    </div>
  </div>
</body>
</html>
<?php
echo "<pre>";
//print_r($close);
echo "</pre>";

die;
// By default, the SDK uses the Guzzle HTTP library for requests. To use CURL,
// you can change the HTTP client by using following line:
// $infusionsoft->setHttpClient(new \Infusionsoft\Http\CurlClient());
// If the serialized token is available in the session storage, we tell the SDK
// to use that token for subsequent requests.
if (isset($_SESSION['token'])) {
    $infusionsoft->setToken(unserialize($_SESSION['token']));
}
// If we are returning from Infusionsoft we need to exchange the code for an
// access token.
if (isset($_GET['code']) and !$infusionsoft->getToken()) {
    $infusionsoft->requestAccessToken($_GET['code']);
}
function addWithDupCheck($infusionsoft) {
    $contact = array('FirstName' => 'John', 'LastName' => 'Doe', 'Email' => 'johndoe@mailinator.com');
    return $infusionsoft->contacts->addWithDupCheck($contact, 'Email');
}
if ($infusionsoft->getToken()) {
    try {
        $cid = addWithDupCheck($infusionsoft);
    } catch (\Infusionsoft\TokenExpiredException $e) {
        // If the request fails due to an expired access token, we can refresh
        // the token and then do the request again.
        $infusionsoft->refreshAccessToken();
        $cid = addWithDupCheck($infusionsoft);
    }
    $contact = $infusionsoft->contacts->load($cid, array('Id', 'FirstName', 'LastName', 'Email'));
    var_dump($contact);
    // Save the serialized token to the current session for subsequent requests
    $_SESSION['token'] = serialize($infusionsoft->getToken());
} else {
    echo '<a href="' . $infusionsoft->getAuthorizationUrl() . '">Click here to authorize</a>';
}
