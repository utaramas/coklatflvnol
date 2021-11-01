<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
class HttpOptions
{
    private $options = [];
    public function toArray() : array
    {
        return $this->options;
    }
    /**
     * @param string $user
     * @param string $password
     */
    public function setAuthBasic($user, $password = '')
    {
        $this->options['auth_basic'] = $user;
        if ('' !== $password) {
            $this->options['auth_basic'] .= ':' . $password;
        }
        return $this;
    }
    /**
     * @param string $token
     */
    public function setAuthBearer($token)
    {
        $this->options['auth_bearer'] = $token;
        return $this;
    }
    /**
     * @param mixed[] $query
     */
    public function setQuery($query)
    {
        $this->options['query'] = $query;
        return $this;
    }
    /**
     * @param mixed[] $headers
     */
    public function setHeaders($headers)
    {
        $this->options['headers'] = $headers;
        return $this;
    }
    public function setBody($body)
    {
        $this->options['body'] = $body;
        return $this;
    }
    public function setJson($json)
    {
        $this->options['json'] = $json;
        return $this;
    }
    public function setUserData($data)
    {
        $this->options['user_data'] = $data;
        return $this;
    }
    /**
     * @param int $max
     */
    public function setMaxRedirects($max)
    {
        $this->options['max_redirects'] = $max;
        return $this;
    }
    /**
     * @param string $version
     */
    public function setHttpVersion($version)
    {
        $this->options['http_version'] = $version;
        return $this;
    }
    /**
     * @param string $uri
     */
    public function setBaseUri($uri)
    {
        $this->options['base_uri'] = $uri;
        return $this;
    }
    /**
     * @param bool $buffer
     */
    public function buffer($buffer)
    {
        $this->options['buffer'] = $buffer;
        return $this;
    }
    /**
     * @param callable $callback
     */
    public function setOnProgress($callback)
    {
        $this->options['on_progress'] = $callback;
        return $this;
    }
    /**
     * @param mixed[] $hostIps
     */
    public function resolve($hostIps)
    {
        $this->options['resolve'] = $hostIps;
        return $this;
    }
    /**
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        $this->options['proxy'] = $proxy;
        return $this;
    }
    /**
     * @param string $noProxy
     */
    public function setNoProxy($noProxy)
    {
        $this->options['no_proxy'] = $noProxy;
        return $this;
    }
    /**
     * @param float $timeout
     */
    public function setTimeout($timeout)
    {
        $this->options['timeout'] = $timeout;
        return $this;
    }
    /**
     * @param string $bindto
     */
    public function bindTo($bindto)
    {
        $this->options['bindto'] = $bindto;
        return $this;
    }
    /**
     * @param bool $verify
     */
    public function verifyPeer($verify)
    {
        $this->options['verify_peer'] = $verify;
        return $this;
    }
    /**
     * @param bool $verify
     */
    public function verifyHost($verify)
    {
        $this->options['verify_host'] = $verify;
        return $this;
    }
    /**
     * @param string $cafile
     */
    public function setCaFile($cafile)
    {
        $this->options['cafile'] = $cafile;
        return $this;
    }
    /**
     * @param string $capath
     */
    public function setCaPath($capath)
    {
        $this->options['capath'] = $capath;
        return $this;
    }
    /**
     * @param string $cert
     */
    public function setLocalCert($cert)
    {
        $this->options['local_cert'] = $cert;
        return $this;
    }
    /**
     * @param string $pk
     */
    public function setLocalPk($pk)
    {
        $this->options['local_pk'] = $pk;
        return $this;
    }
    /**
     * @param string $passphrase
     */
    public function setPassphrase($passphrase)
    {
        $this->options['passphrase'] = $passphrase;
        return $this;
    }
    /**
     * @param string $ciphers
     */
    public function setCiphers($ciphers)
    {
        $this->options['ciphers'] = $ciphers;
        return $this;
    }
    public function setPeerFingerprint($fingerprint)
    {
        $this->options['peer_fingerprint'] = $fingerprint;
        return $this;
    }
    /**
     * @param bool $capture
     */
    public function capturePeerCertChain($capture)
    {
        $this->options['capture_peer_cert_chain'] = $capture;
        return $this;
    }
    /**
     * @param string $name
     */
    public function setExtra($name, $value)
    {
        $this->options['extra'][$name] = $value;
        return $this;
    }
}
