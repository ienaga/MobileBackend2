<?php

namespace MobileBackend\PhpSdk;

class MobileBackend
{

    /**
     * @var string
     */
    const DOMAIN = "mb.api.cloud.nifty.com";

    /**
     * @var string
     */
    const VERSION = "2013-09-01";

    /**
     * @var string
     */
    const SIGNATURE_STRING =
        "SignatureMethod=HmacSHA256"
        . "&SignatureVersion=2"
        . "&X-NCMB-Application-Key=%s"
        . "&X-NCMB-Timestamp=%s"
        . "%s";

    /**
     * @var string
     */
    protected $domain = "";

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $end_point = "";

    /**
     * @var string
     */
    protected $application_key = "";

    /**
     * @var string
     */
    protected $client_key = "";

    /**
     * @var string
     */
    protected $method = "GET";

    /**
     * @var string
     */
    protected $time_stamp = "";

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $options = [];


    /**
     * MobileBackend constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = array())
    {
        // exception
        if (!isset($config["client_key"]) || !isset($config["application_key"])) {
            throw new \Exception("can not find client_key or application_key");
        }

        // domain
        $domain = (isset($config["domain"])) ? $config["domain"] : self::DOMAIN;

        // version
        $version = (isset($config["version"])) ? $config["version"] : self::VERSION;

        // config
        $this
            ->setDomain($domain)
            ->setVersion($version)
            ->setClientKey($config["client_key"])
            ->setApplicationKey($config["application_key"])
            ->setTimeStamp(date("c"));

        // init
        $this
            // HEADER
            ->addHeader("X-NCMB-Application-Key:". $this->getApplicationKey())
            ->addHeader("X-NCMB-Timestamp:". $this->getTimeStamp())
            ->addHeader("Content-Type:application/json")
            // OPTIONS
            ->addOptions(CURLOPT_TIMEOUT, 10)
            ->addOptions(CURLOPT_CONNECTTIMEOUT, 5)
            ->addOptions(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * @param $timeStamp
     * @return $this
     */
    public function setTimeStamp($timeStamp)
    {
        $this->time_stamp = $timeStamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeStamp()
    {
        return $this->time_stamp;
    }

    /**
     * @param $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getEndPoint()
    {
        return $this->end_point;
    }

    /**
     * @param string $api_path
     * @return $this
     */
    public function setEndPoint($api_path)
    {
        if (substr($api_path, 0, 1) !== "/") {
            $api_path = "/". $api_path;
        }
        $this->end_point = $api_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $url = "https://". $this->getDomain() ."/". $this->getVersion() . $this->getEndPoint();



        if ($this->isQuery() && $this->getMethod() === "GET")
            $url .= "?". $this->getQuery();

        return $url;
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function addHeader($value)
    {
        $this->headers[] = $value;
        return $this;
    }

    /**
     * clear
     */
    public function clearHeader()
    {
        $this->headers = [];
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method = "GET")
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return http_build_query(["where" => json_encode($this->query)]);
    }

    /**
     * @return string
     */
    public function getPostQuery()
    {
        return json_encode($this->query);
    }

    /**
     * @return bool
     */
    public function isQuery()
    {
        return ($this->query) ? true : false;
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function addQuery($key, $value)
    {
        $this->query[$key] = $value;
        return $this;
    }

    /**
     * @param  int $key
     * @param  string $value
     * @return $this
     */
    public function addOptions($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * clear options
     */
    public function cleaOptions()
    {
        $this->options = [];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getClientKey()
    {
        return $this->client_key;
    }

    /**
     * @param $client_key
     * @return $this
     */
    public function setClientKey($client_key)
    {
        $this->client_key = $client_key;
        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationKey()
    {
        return $this->application_key;
    }

    /**
     * @param $application_key
     * @return $this
     */
    public function setApplicationKey($application_key)
    {
        $this->application_key = $application_key;
        return $this;
    }

    /**
     * @return $this
     */
    public function addHeaderSignature()
    {
        $signature_string  = '';
        $signature_string .= $this->getMethod() . "\n";
        $signature_string .= $this->getDomain() . "\n";
        $signature_string .= "/". $this->getVersion() . $this->getEndPoint() . "\n";
        $signature_string .= sprintf(
            self::SIGNATURE_STRING,
            $this->getApplicationKey(),
            $this->getTimeStamp(),
            ($this->isQuery() && $this->getMethod() === "GET") ? "&".$this->getQuery() : ""
        );

        $signature = hash_hmac('sha256', $signature_string, $this->getClientKey(), true);
        $signature = base64_encode($signature);

        $this->addHeader('X-NCMB-Signature:'. $signature);

        return $this;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        // init
        $curl = curl_init();

        $this
            ->addOptions(CURLOPT_URL, $this->getUrl())
            ->addOptions(CURLOPT_CUSTOMREQUEST, $this->getMethod())
            ->addHeaderSignature()
            ->addOptions(CURLOPT_HTTPHEADER, $this->getHeader());

        if ($this->getMethod() === "POST" || $this->getMethod() === "PUT") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getPostQuery());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }

        // option set
        curl_setopt_array($curl, $this->getOptions());

        return json_decode(curl_exec($curl));
    }

}
