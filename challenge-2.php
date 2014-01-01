<?php

require 'vendor/autoload.php';

use OpenCloud\Rackspace;
use OpenCloud\Compute\Constants\Network;
use OpenCloud\Compute\Constants\ServerState;


#initialize credentials

$credfile = $_SERVER['HOME'] . '/' . '.rackspace_cloud_credentials';

if (file_exists($credfile)) #cred file is there
{
    $file_handle = fopen($credfile, 'r');
    $username = fgets($file_handle);
    $apikey = fgets($file_handle);
    fclose($file_handle);
}
else #no cred file
{
    echo "Please enter your username: ";
    $username = trim(fgets(STDIN));
    echo "Please enter your API key: ";
    $apikey = trim(fgets(STDIN));

}

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => $username,
    'apiKey'   => $apikey
	));
    
#initialize public key
$pubkey = $_SERVER['HOME'] . '/' . '.ssh/id_rsa.pub';
if (file_exists($pubkey)) #cred file is there
{
    $file_handle = fopen($pubkey, 'r');
    $line = explode(" ",fgets($file_handle));
    $keyValue = $line[1];
    $keyName = $line[0];
}
else
{

    echo "Please enter your publickey name (ssh-rsa?; NO DEFAULT): ";
    $keyName = trim(fgets(STDIN));
    echo "Please enter ssh public key: ";
    $keyValue = trim(fgets(STDIN));
}

$keypair = (array(
    'name' => $keyName,
    'publcKey' => $keyValue
));



		
?>