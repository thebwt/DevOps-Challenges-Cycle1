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
    $keyValue = fgets($file_handle);
    $keyName = "test-key";
}
else
{
    echo "Please enter ssh public key: ";
    $keyValue = trim(fgets(STDIN));
}

);

#create server
$compute = $client->computeService('cloudServersOpenStack', 'ORD');
$server = $compute->server();

#really, this is the nicest way to do this, so copy pasta

$server->addFile('/root/.ssh/authorized_keys', $keyValue);

try {
    $response = $server->create(array(
        'name'     => 'Challenege-2',
        'image'    => $compute->image('80fbcb55-b206-41f9-9bc2-2dd7aac6c061'),
        'flavor'   => $compute->flavor('2'),
        'networks' => array(
            $compute->network(Network::RAX_PUBLIC),
            $compute->network(Network::RAX_PRIVATE)
                )
    ));
} catch (\Guzzle\Http\Exception\BadResponseException $e) {

    // No! Something failed. Let's find out:

    $responseBody = (string) $e->getResponse()->getBody();
    $statusCode   = $e->getResponse()->getStatusCode();
    $headers      = $e->getResponse()->getHeaderLines();

    echo sprintf('Status: %s\nBody: %s\nHeaders: %s', $statusCode, $responseBody, implode(', ', $headers));
}

#wait for server to build and then get the ip address

#more I can't do it more copy pasta
$callback = function($server) {
    if (!empty($server->error)) {
        var_dump($server->error);
        exit;
    } else {
        echo sprintf(
            "Waiting on %s/%-12s %4s%%\n",
            $server->name(),
            $server->status(),
            isset($server->progress) ? $server->progress : 0
        );
    }
};

$server->waitFor(ServerState::ACTIVE, 600, $callback);
$server->refresh();
echo sprintf(
    "Admin Password: %s\nIPv4 Address: %s\n",
    $server->adminPass,
    $server->ip()
);

echo "\n";

		
?>