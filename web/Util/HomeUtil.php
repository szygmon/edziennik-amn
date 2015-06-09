<?php

use \Model\School;

class HomeUtil {

	/** @var \User\Me */
	protected $Me;

	/**
	 * @param \User\Me $Me
	 */
	function __construct() {
		$this->Me = Di::get('Me');
	}

	public function registerForm($post) {
		$check = Di::get('em')->getRepository('\Model\School')->findOneBy(array('alias' => $_POST['schoolAlias']));
		if ($check)
			die('Alias zajęty');
		$school = new School();
		// ++ unikalność
		$school->setName($_POST['schoolAlias']);
		(new \UserManager)->setup($school);

		$user = new \Model\User();
		$user->setUsername($_POST['username']);
		$user->setEmail($_POST['email']);
		$user->setPassword($_POST['pass']);
		$user->setSchool($school);

		$this->em->persist($school);
		$this->em->persist($user);
		$this->em->flush();

		$msg = "Zarejestrowano pomyślnie";
		return;
	}

	public function loginForm($post) {
		$login = $this->Me->login($_POST['username'], $_POST['password']);

		if ($login) {
			$this->Me->getModel()->setLastLogin(new DateTime);
			Di::get('em')->flush();
			Di::get('Router')->redirect('Home/index');
		}

		return false;
	}

}