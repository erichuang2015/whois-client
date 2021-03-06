<?php
namespace MallardDuck\Whois;

use Hoa\Socket\Client as SocketClient;
use MallardDuck\Whois\WhoisClientInterface;

/**
 * The Whois Client Class.
 *
 * @author mallardduck <dpock32509@gmail.com>
 *
 * @copyright lucidinternets.com 2018
 *
 * @version 0.4.0
 */
abstract class AbstractWhoisClient implements WhoisClientInterface
{

    /**
     * The carriage return line feed character comobo.
     * @var string
     */
    protected $clrf = "\r\n";

    /**
     * The timeout duration used for WhoIs server lookups.
     * @var int
     */
    const TIMEOUT = 10;

    /**
     * The input domain provided by the user.
     * @var SocketClient
     */
    protected $connection;

    /**
     * Perform a Whois lookup.
     *
     * Performs a Whois request using the given input for lookup and the Whois
     * server values.
     *
     * @param  string $lookupValue  The domain or IP being looked up.
     * @param  string $whoisServer  The whois server being queried.
     *
     * @return string               The raw text results of the query response.
     */
    public function makeWhoisRequest(string $lookupValue, string $whoisServer)
    {
        $this->createConnection($whoisServer);
        $this->makeRequest($lookupValue);
        return $this->getResponseAndClose();
    }

    /**
     * Creates a socket connection to the whois server and activates it.
     *
     * @param string $whoisServer The whois server domain or IP being queried.
     */
    final public function createConnection(string $whoisServer) : void
    {
        // Form a TCP socket connection to the whois server.
        $this->connection = new SocketClient('tcp://'.$whoisServer.':43', self::TIMEOUT);
        $this->connection->connect();
    }

    /**
     * Makes a whois request
     *
     * @param string $lookupValue The cache item to save.
     *
     * @return bool True if all not-yet-saved items were successfully saved or
     * there were none. False otherwise.
     */
    final public function makeRequest(string $lookupValue) : bool
    {
        // Send the domain name requested for whois lookup.
        return $this->connection->writeString($lookupValue.$this->clrf);
    }

    /**
     * A function for making a raw Whois request.
     *
     * @return string   The raw results of the query response.
     */
    final public function getResponseAndClose()
    {
        // Read the full output of the whois lookup.
        $response = $this->connection->readAll();
        // Disconnect the connections after use in order to prevent observed
        // network & performance issues. Not doing this caused mild trottling.
        $this->connection->disconnect();
        return $response;
    }
}
