<?php
require 'vendor/autoload.php';

use Uro\TeltonikaFmParser\FmParser;
use Uro\TeltonikaFmParser\Reply;

// Créez une instance de FmParser pour TCP
$parser = new FmParser('tcp');

// Créez un serveur TCP sur le port 8043
$socket = stream_socket_server("tcp://0.0.0.0:8043", $errno, $errstr);

if (!$socket) {
    throw new \Exception("$errstr ($errno)");
} else {
    while ($conn = stream_socket_accept($socket)) {
        // Lire l'IMEI
        $payload = fread($conn, 1024);
        $imei = $parser->decodeImei($payload);

        // Accepter le paquet
        fwrite($conn, Reply::accept());

        // Lire les données
        $payload = fread($conn, 1024);
        $packet = $parser->decodeData($payload);

        // Envoyer un accusé de réception
        fwrite($conn, $parser->encodeAcknowledge($packet));

        // Fermer la connexion
        fclose($conn);
    }
    fclose($socket);
}
