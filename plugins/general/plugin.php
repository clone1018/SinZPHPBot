<?php

/**
 * General
 *
 * A simple plugin to help opers with their tedious tasks.
 *
 * @author		clone1018
 * @copyright           Copyright (c) 2008 - 2011, Axxim
 * @link		http://axxim.net/
 * @since		Version 1.0
 */
class General extends Bot {

    public function __construct() {
        $this->config = $config;
        $this->bot = $bot;
        $this->user = $user;
        //$this->plugin = json_decode(file_get_contents('plugin.json'));
    }

    /**
     * Defcon Command
     *
     * @return	void
     */
    public function test($channel, $user, $command) {
        echo "Hello World";
        Bot::privmsg($channel, "Hello World!");
    }

}