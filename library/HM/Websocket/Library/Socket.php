<?php

/**
 * Socket class
 *
 * @author Moritz Wutz <moritzwutz@gmail.com>
 * @author Nico Kaiser <nico@kaiser.me>
 * @version 0.2
 */

/**
 * This is the main socket class
 *
 * @property HM_Websocket_Library_Socket $master
 */
class HM_Websocket_Library_Socket
{
    protected $master;

    /**
     * @var array Holds all connected sockets
     */
    protected $allsockets = array();
    protected $context = null;
    protected $ssl = false;

    public function __construct($host, $port, $ssl = false)
    {
        ob_implicit_flush(true);

        $this->ssl = (bool) $ssl;
        $this->createSocket($host, $port);
    }

    private function createSocket($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->_createSocket($host, $port);
        $this->allsockets[] = $this->master;
    }

    public function recreateSocket()
    {
        if (count($this->allsockets)) array_shift($this->allsockets);
        fclose($this->master);
        $this->_createSocket($this->host, $this->port);
        array_unshift($this->allsockets, $this->master);
    }

    /**
     * Create a socket on given host/port
     *
     * @param string $host The host/bind address to use
     * @param int $port The actual port to bind on
     */
    private function _createSocket($host, $port)
    {
        $protocol = ($this->ssl === true) ? 'tls://' : 'tcp://';
        $url = $protocol.$host.':'.$port;

        set_error_handler(array($this, "handleError1"));
        $this->context = stream_context_create();
        restore_error_handler();

        if($this->ssl === true)
        {
            $this->applySSLContext();
        }

        set_error_handler(array($this, "handleError2"));
        if(!$this->master = @stream_socket_server($url, $errno, $err, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $this->context))
        {
            die('Error creating socket: ' . $err);
        }
        restore_error_handler();

    }

    public function handleError1($errno, $errstr)
    {
        $message = 'ERR stream_context_create: ' . $errno . ' at line ' . $errstr;
        echo date('Y-m-d H:i:s') . ' [error] ' . $message . PHP_EOL;
    }

    public function handleError2($errno, $errstr)
    {
        $message = 'ERR stream_socket_server: ' . $errno . ' at line ' . $errstr;
        echo date('Y-m-d H:i:s') . ' [error] ' . $message . PHP_EOL;
    }

    public function handleError3($errno, $errstr)
    {
        $message = 'ERR stream_context_set_option: ' . $errno . ' at line ' . $errstr;
        echo date('Y-m-d H:i:s') . ' [error] ' . $message . PHP_EOL;
    }

    private function applySSLContext()
    {
        //$pem_file = './server.pem';
        //$pem_passphrase = 'shinywss';

        // Generate PEM file
        /*
		if(!file_exists($pem_file))
		{
			$dn = array(
				"countryName" => "DE",
				"stateOrProvinceName" => "none",
				"localityName" => "none",
				"organizationName" => "none",
				"organizationalUnitName" => "none",
				"commonName" => "foo.lh",
				"emailAddress" => "baz@foo.lh"
			);
			$privkey = openssl_pkey_new();
			$cert    = openssl_csr_new($dn, $privkey);
			$cert    = openssl_csr_sign($cert, null, $privkey, 365);
			$pem = array();
			openssl_x509_export($cert, $pem[0]);
			openssl_pkey_export($privkey, $pem[1], $pem_passphrase);
			$pem = implode($pem);
			file_put_contents($pem_file, $pem);
		} */
        $conf = Zend_Registry::get('config');

        $localCertRel = $conf->websocket->local_cert;
        $localPkRel = $conf->websocket->local_pk;

        // apply ssl context:
        $localCert = (file_exists($localCertRel))
            ? $localCertRel
            : APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $localCertRel;

        $localPk = (file_exists($localPkRel))
            ? $localPkRel
            : APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $localPkRel;

        $passphrase = $conf->websocket->passphrase ?: false;
        $allowSelfSigned = (bool) $conf->websocket->allow_self_signed;
        $verifyPeer = (bool) $conf->websocket->verify_peer;

        set_error_handler(array($this, "handleError3"));
        stream_context_set_option($this->context, 'ssl', 'local_cert', $localCert);
        stream_context_set_option($this->context, 'ssl', 'local_pk', $localPk);
        stream_context_set_option($this->context, 'ssl', 'passphrase', $passphrase);
        stream_context_set_option($this->context, 'ssl', 'allow_self_signed', $allowSelfSigned);
        stream_context_set_option($this->context, 'ssl', 'verify_peer', $verifyPeer);
        stream_context_set_option($this->context, 'ssl', 'crypto_method', STREAM_CRYPTO_METHOD_TLSv1_2_SERVER);
        restore_error_handler();
    }

    // method originally found in phpws project:
    protected function readBuffer($resource)
    {
        if($this->ssl === true)
        {
            $buffer = fread($resource, 8192);
            // extremely strange chrome behavior: first frame with ssl only contains 1 byte?!
            if(strlen($buffer) === 1)
            {
                $buffer .= fread($resource, 8192);
            }
            return $buffer;
        }
        else
        {
            $buffer = '';
            $buffsize = 8192;
            $metadata['unread_bytes'] = 0;
            do
            {
                if(feof($resource))
                {
                    return false;
                }
                $result = fread($resource, $buffsize);
                if($result === false || feof($resource))
                {
                    return false;
                }
                $buffer .= $result;
                $metadata = stream_get_meta_data($resource);
                $buffsize = ($metadata['unread_bytes'] > $buffsize) ? $buffsize : $metadata['unread_bytes'];
            } while($metadata['unread_bytes'] > 0);

            return $buffer;
        }
    }

    // method originally found in phpws project:
    public function writeBuffer($resource, $string)
    {
        $stringLength = strlen($string);
        for($written = 0; $written < $stringLength; $written += $fwrite)
        {
            $fwrite = @fwrite($resource, substr($string, $written));
            if($fwrite === false)
            {
                return false;
            }
            elseif($fwrite === 0)
            {
                return false;
            }
        }
        return $written;
    }
}