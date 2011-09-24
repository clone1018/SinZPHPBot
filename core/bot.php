<?php

/**
 * Bot
 *
 * The main file.
 *
 * @author		ylt, SinZ, clone1018
 * @version             2.0
 */
class Bot {

    private $sock = null;
    var $lock = false;

    public function _construct($config) {
        
    }

    function __autoload($class) {
        include $class . '.php';
    }

    /*
     * Init Function
     * 
     * @returns void
     */

    public function init() {
        global $config;
        $this->plugin = new Plugin();
        $this->plugin->register(new Core($this->config));
        $this->plugin->register(new CTCP($this->config));
        $this->plugin->register(new Colors($this->config));

        /*
         * foreach ($this->config['plugins'] as $plugin) {
         * include "../plugins/$plugin/plugin.php";
         *      $plugin->register(new $plugin($this->config));
         *  }
         * 
         */
        if (count($config['plugins']) == 0) {
            echo "Loading 0 plugins. \n";
        } elseif (count($config['plugins']) == 1) {
            include "./plugins/" . $config['plugins'][0] . "/plugin.php";
            $this->$config['plugins'][0] = new $config['plugins'][0];
            $this->plugin->register($config['plugins'][0]);
            echo "Loading " . count($config['plugins']) . " plugins.\n";
        } else {
            foreach ($config['plugins'] as $plugin) {
                include "../plugins/" . $plugin . "/plugin.php";
                $this->plugin->register(new $plugin($this->config));
                $count++;
            }
            echo "Loading " . $count . " plugins.\n";
        }
    }

    /*
     * Startup Functions
     * 
     * Commands to run after bot is connected to network.
     * 
     * @return bool
     */

    public function startup() {
        global $config;

        if ($config['ns_enabled'])
            $this->raw("PRIVMSG " . $config['ns_nickserv'] . " IDENTIFY " . $config['ns_pass']);
        if ($config['channels']) {
            foreach ($config['channels'] as $channel) {
                $this->raw("JOIN " . $channel);
            }
        }
        if ($config['startup']) {
            foreach ($config['startup'] as $cmd) {
                $this->raw($cmd);
            }
        }
    }

    /*
     * RAW
     * 
     * Sends RAW messages to the server
     * 
     * @return void
     */

    public function raw($cmd) {
        $this->colors = new Colors();
        $this->buffer .= $cmd . "\n";
        echo $this->colors->getColoredString("RAW: ", "blue");
        echo $cmd . "\r\n";
    }

    public function connect() {
        global $config;
        $this->colors = new Colors();
        echo $this->colors->getColoredString("Connecting to " . $config['network'] . " on port " . $config['port'] . "\n", "red");
        $this->sock = fsockopen($config['network'], $config['port'], $errno, $errstr, 32768);
        stream_set_timeout($this->sock, 2);
        $errorlevel = stream_get_meta_data($this->sock);
        $this->raw("NICK " . $config['nick']);
        $this->raw('USER ' . $config['ident'] . ' 8 * :' . $config['realname']);

        if ($errorinfo['timed_out']) {
            echo 'Connection timed out!';
        }
    }

    public function flush_message() {
        if (strlen($this->buffer) > 0) {
            fwrite($this->sock, $this->buffer);
            $this->buffer = "";
        }
    }

    public function privmsg($who, $msg) {
        $this->raw("PRIVMSG " . $who . " : " . $msg);
    }

    /*
     * Parse Message
     * 
     * Converts the message to .class function args
     */

    private function parse_message($line) {
        $line = explode(" ", $line);
        return $line;
    }

    /*
     * Start
     * 
     * Starts the server
     */

    public function start() {
        global $config;
        $this->plugin = new Plugin();
        $this->colors = new Colors();
        if (!$this->sock) {
            $this->connect();
        }
        while (!feof($this->sock)) {
            $line = fgets($this->sock);
            if (isset($line))
                echo $line;
            $command = $this->parse_message($line);
            if (strpos($line, 'End of /MOTD') !== false)
                $this->startup();

            //var_dump($command);

            if (strpos($line, 'PING ') !== false) {
                $this->raw('PONG ' . $command[1]);
            } elseif (strstr($command[3], $config['command'])) {
                // This is a command, or possibly a command, send it to it's plugin.
                $class = str_replace($config['command'], '', $command[3]);
                $class = str_replace(':', '', $class);
                $user = explode('!', $command[0]);
                $user = str_replace(':', '', $user);
                $args = array(
                    'channel' => $command[2],
                    'user' => $user[0]
                );
                if (class_exists($class, false)) {
                    $this->plugin->event($class, $command[4], $args);
                    //echo $class, $command[4], $args;
                }
            } else {
                if ($command != null) //echo json_encode($command)."\n";
                    continue;
            }
            $this->flush_message();
        }
    }

}
