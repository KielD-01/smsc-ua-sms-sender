<?php
namespace Cake\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Log\Log;
use GuzzleHttp\Client as Sender;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SmscUaComponent
 * @package Cake\Controller\Component
 */
class SmscUaComponent extends Component
{

    /**
     * HTTP requests Sender
     *
     * @var Sender
     */
    private $_sender;

    /**
     * Base URI to send SMS, MMS or/and E-mail
     *
     * @var string
     */
    private $_base_uri = 'https://smsc.ua/sys/';

    /**
     * Voices to send call with text
     *
     * @var array
     */
    private $_voices = [
        1 => 'm',
        2 => 'm2',
        3 => 'w',
        4 => 'w2',
        5 => 'w3',
        6 => 'w4',
    ];

    /**
     * Account login
     *
     * @var string
     */
    private $_login;

    /**
     * Account password
     *
     * @var string
     */
    private $_password;

    /**
     * Sending arguments
     *
     * @var array
     */
    private $_arguments = [];

    /**
     * Errors array
     *
     * @var array
     */
    private $_errors = [];

    /**
     * Receiver(-s) number(-s)
     *
     * @var array
     */
    private $_numbers = [];

    /**
     * Message to send
     *
     * @var string
     */
    private $_message_body = '';

    /**
     * Response formats array
     *
     * @var array
     */
    private $_response_formats = [
        'string' => 0,
        'digits' => 1,
        'xml' => 2,
        'json' => 3
    ];

    /**
     * Default response format - JSON
     *
     * @var string
     */
    private $_response_format = 'json';

    /**
     * Number Regex to check if number is correct
     *
     * @var string
     */
    private $_number_regex = '/^((\+?7|8)(?!95[4-79]|99[08]|907|94[^0]|336)([348]\d|9[0-6789]|7[0247])\d{8}|' .
    '\+?(99[^4568]\d{7,11}|994\d{9}|9955\d{8}|996[57]\d{8}|9989\d{8}|380[34569]\d{8}|' .
    '375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}))$/';

    /**
     * Rules array
     *
     * @var array
     */
    private $_rules = [];

    /**
     * Rules path
     *
     * @var string
     */
    private $_rules_path = ROOT . DS . 'vendor' . DS . 'kield-01' . DS .
    'smsc-ua-sms-sender' . DS . 'data' . DS;

    /** GETTERS **/

    /**
     * Get account login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Get account password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Getting arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Get all rules
     *
     * @return array
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /** SETTERS **/

    /**
     * Set account login
     *
     * @param string $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }

    /**
     * Set account password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->_password = md5($password);
        return $this;
    }

    /**
     * Setting arguments key and value
     *
     * @param string $key
     * @param null|string|array $value
     * @return SmscUaComponent
     */
    public function setArgument($key = null, $value = null)
    {
        $this->_arguments[$key] = $value;
        return $this;
    }

    /**
     * Write 1 number as string or n (n >= 2) as array
     *
     * @param array|string $numbers
     * @return SmscUaComponent
     */
    public function setNumbers($numbers)
    {
        if (is_array($numbers)) {
            foreach ($numbers as $number) {
                array_push($this->_numbers, $number);
            }
            return $this;
        }

        $this->_numbers[] = $numbers;
        return $this;
    }

    /**
     * Setting message body, that will be sent to receiver
     *
     * @param string $message_body
     * @return SmscUaComponent
     */
    public function setMessageBody($message_body)
    {
        $this->_message_body = $message_body;
        return $this;
    }

    /**
     * @param $response_format
     * @return $this
     * @throws \Exception
     */
    public function setResponseFormat($response_format)
    {
        $response_format = mb_strtolower($response_format);

        if (array_key_exists($response_format, $this->_response_formats)) {
            $this->_response_format = $response_format;
            return $this;
        }

        throw new \Exception("Response format {$response_format} does not exists");
    }

    /**
     * Sets custom rules
     *
     * @return SmscUaComponent
     */
    private function setRules()
    {
        $this->_rules = (new Configure\Engine\PhpConfig($this->_rules_path))->read('rules');
        return $this;
    }

    /** METHODS */

    /**
     * @param array $config
     */
    public function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->_sender = new Sender();
        $this->__getIp();
        $this->setRules();
    }

    /**
     * Checking receiver(-s) numbers
     *
     * @return bool|boolean|null
     */
    private function _checkNumbers()
    {
        foreach ($this->_numbers as $number) {
            if (!preg_match($this->_number_regex, $number)) {
                return $this->_errors['numbers'][] = "Number {$number} is not applicable regarding to pattern rules";
            }
        }

        if (!isset($this->_errors['numbers'])) {
            return $this->setArgument('phones', implode(',', array_unique($this->_numbers)));
        }

        return true;
    }

    /**
     * Checking message length
     */
    private function _checkMessage()
    {
        if (strlen($this->_message_body) == 0) {
            $this->_errors['message'][] = 'Message length cannot be less than 1 nor empty';
            throw new \Exception('Message length cannot be less than 1 nor empty');
        }

        return $this->setArgument('mes', $this->_message_body);
    }

    /**
     * Checking credentials
     */
    private function _checkCredentials()
    {
        if (!$this->_login && !$this->_password) {

            if ($credentials = Configure::read('smsc_ua')) {
                $this->setArgument('login', $credentials['login']);
                $this->setArgument('psw', $credentials['psw']);

                return true;
            }

            $this->_errors['credentials'][] = 'Credentials are wrong or not provided';
            throw new \Exception('Credentials are wrong or not provided');
        }

        $this->_checkArgument('login') ?: $this->setArgument('login', $this->_login);
        $this->_checkArgument('psw') ?: $this->setArgument('psw', $this->_password);

        return true;
    }

    /**
     * Checking if argument exists
     *
     * @param $argument
     * @return bool
     */
    private function _checkArgument($argument)
    {
        return array_key_exists($argument, $this->_arguments);
    }

    /**
     * Preparing before send
     *
     * @param null $uri
     * @return string
     * @throws \Exception
     */
    private function _beforeSend($uri = null)
    {
        $this->setArgument('fmt', $this->_response_formats[mb_strtolower($this->_response_format)]);

        $this->_checkCredentials();
        $this->_checkNumbers();
        $this->_checkMessage();

        if ($this->_errors) {
            Log::error($this->_errors);
            throw new \Exception('Errors occurred. Look over Log to see details');
        }

        return $this->_base_uri .= "{$uri}.php{$this->_glueArguments()}";
    }

    /**
     * Sending usual SMS
     *
     * @return mixed|string
     */
    public function sendPlainTextSMS()
    {
        return $this->_getResponse($this->_sender->get($this->_beforeSend('send')));
    }

    /**
     * Imploding arguments
     *
     * @return string
     */
    private function _glueArguments()
    {
        $main = [];

        foreach ($this->_arguments as $argument => $value) {
            $main[] = "{$argument}={$value}";
        }

        Log::info($main);

        return '?' . implode('&', $main);
    }

    /**
     * Getting response from smsc.ua
     *
     * @param ResponseInterface $response
     * @return mixed|string
     */
    private function _getResponse(ResponseInterface $response)
    {
        $response = $response
            ->getBody()
            ->getContents();

        return $this->_response_format == 'json' ? \GuzzleHttp\json_decode($response) : $response;
    }

    /**
     * Getting Sender IP
     */
    private function __getIp()
    {
        Log::info($this->_getResponse($this->_sender->get('https://api.ipify.org?format=json')));
    }
}
