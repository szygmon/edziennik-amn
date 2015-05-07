<?php

namespace Controller;

use Model\Object;
use Core\Response;

class Home {

	/** @var \Doctrine\ORM\EntityManager */
	protected $em;

	/** @var \HomeUtil */
	protected $homeUtil;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \HomeUtil $HomeUtil
	 */
	function __construct($em, $HomeUtil) {
		$this->em = $em;
		$this->homeUtil = $HomeUtil;
	}

	/**
	 * Index Site
	 * @Route()
	 * @param \User\Me $Me
	 * @param \Core\Router $Router
	 */
	public function index($Me, $Router) {
		\Notify::error('Notify ;-)');
		if ($Me->isLogged())
			return new Response([], 'Home/indexSchool');
		return array("salesPage" => !$Router->getSubdomain());
	}

	/**
	 * Register
	 * @Route(/register)
	 */
	public function register() {
		if (isset($_POST['username']))
			$msg = $this->homeUtil->registerForm($_POST);
		return array("salesPage" => true);
	}

	/**
	 * Login site
	 * @Route(/login)
	 * @param \User\Me $Me
	 * @param \Core\Router $Router
	 */
	public function login($Me, $Router) {
		$schools = $this->em->getRepository('Model\\School')->findAll();
		$s = array();
		foreach ($schools as $school) {
			$s[$school->getAlias()] = $school->getName();
		}

		$msg = null;
		if (isset($_POST['username']))
			$msg = $this->homeUtil->loginForm($_POST);

		return array("salesPage" => !$Router->getSubdomain(), 'schools' => $s, 'msg' => $msg);
	}

	/**
	 * Logout
	 * @Route("/logout")
	 * @param \User\Me $Me
	 * @param \Core\Router $Router
	 */
	public function logout($Me, $Router) {
		$Me->logout();
		$Router->redirect('Home/index');
	}

}
