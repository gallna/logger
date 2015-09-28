<?php
namespace {

    use Monolog\Logger as Monolog;

    function e($string, $foreground = null, $background = null) {

        $logger = new Kemer\Logger\LoggerOld();
        echo $logger->e($string, $foreground, $background);
    }

    class Log extends Kemer\Logger\Logger
    {
        /**
         * Adds a log record at the WARNING level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
        public static function warn($message, array $context = array())
        {
            return parent::warn($message, static::context($context));
        }

        protected static function context(array $context = array())
        {
            $data = [];
            foreach ($context as $key => $value) {
                $data[$key] = print_r($context, true);
            }
            return $data;
        }
    }

}
namespace Kemer\Logger {

    use Zend\Http\Request;
    use Zend\Http\Response;
    use Monolog\Logger as Monolog;
    use Monolog\Handler;
    use Monolog\Formatter;
    use Bramus\Monolog\Formatter\ColoredLineFormatter;

    class Logger
    {
        public static $logger;

        public static $channel = "DEFAULT";

        public function __construct($channel = "DEFAULT")
        {
            static::$channel = $channel;
        }

        public static function setLogger(Monolog $logger)
        {
            static::$logger = $logger;
        }

        public static function getLogger()
        {
            if (!static::$logger) {
                $log = new Monolog(static::$channel);
                $handler = new Handler\StreamHandler('php://stdout', Monolog::DEBUG);

                $handler->setFormatter(new ColoredLineFormatter(
                    null,
                    "%message% %context% %extra%\n",
                    null,
                    false,
                    true
                    ));
                $log->pushHandler($handler);
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);
                $handler = new Handler\RedisHandler($redis, "asda", Monolog::DEBUG);
                $handler->setFormatter(new Formatter\JsonFormatter(
                    Formatter\JsonFormatter::BATCH_MODE_JSON,
                    false
                ));

                $log->pushHandler($handler);
                static::$logger = $log;
            }
            return static::$logger;
        }

        public static function __callStatic($method, $args)
        {
            call_user_func_array([static::getLogger(), $method], $args);
        }

        public function __call($method, $args)
        {
            call_user_func_array([static::getLogger(), $method], $args);
        }
    }

     class LoggerOld
     {
         private $fColors = [
             'black' => '0;30',
             'dark_gray' => '1;30',
             'blue' => '0;34',
             'light_blue' => '1;34',
             'green' => '0;32',
             'light_green' => '1;32',
             'cyan' => '0;36',
             'light_cyan' => '1;36',
             'red' => '0;31',
             'light_red' => '1;31',
             'purple' => '0;35',
             'light_purple' => '1;35',
             'brown' => '0;33',
             'yellow' => '1;33',
             'light_gray' => '0;37',
             'white' => '1;37',
         ];
         private $bColors = [
            'transparent' => '',
            'black' => '40',
            'red' => '41',
            'green' => '42',
            'yellow' => '43',
            'blue' => '44',
            'magenta' => '45',
            'cyan' => '46',
            'light_gray' => '47',
         ];

         // Returns colored string
         public function getColoredString($string, $foreground = null, $background = null)
         {
            return sprintf(
                $background ? "\033[%sm\033[%sm%s\033[0m" : "\033[%sm%s%s\033[0m",
                $this->fColors[isset($this->fColors[$foreground]) ? $foreground : 'black'],
                $this->bColors[isset($this->bColors[$background]) ? $background : 'transparent'],
                $string
                );
         }

         public function e($data, $foreground = null, $background = null)
         {
            switch (true) {
                case $data instanceof Request:
                    e($data->toString(), 'green');
                    break;
                case $data instanceof Response:
                    list($headers, $content) = explode("\r\n\r\n", $data, 2);
                    e(sprintf("%s\n\n%s[...]%s", $headers, substr($content, 0, 30), substr($content, -30)), 'blue');
                    break;
                default:
                    echo $this->getColoredString((string)$data, $foreground, $background)."\n";
                    break;
            }
        }
     }
}
