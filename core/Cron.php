<?php

namespace core;

use \Conf;

class Cron {

	public static function init($token) {
		if (Conf::get('cron.token') != $token)
			return false;

		//Do cron jobs
		require ABSPATH . WEB . 'Cron.php';
		new \Cron;
		var_dump("Cron SUCCESS!");
	}

}
