<?php

use \Model\School;

class DevReset {

	/** @var \Doctrine\ORM\EntityManager */
	protected $em;

	public function __construct() {
		$this->em = Di::get('em');
	}

	public function create() {
		Db::exec('set search_path to public');
		return Db::install('public');
	}

	public function update() {
		$schools = $this->em->getRepository('Model\\School')->findAll();
		$um = new UserManager;
		$r = array();
		foreach ($schools as $school) {
			$r += $um->setup($school);
		}
		return $r;
	}

	public function dummyData() {
		$userManager = new UserManager;
		$school = new School();
		$school->setAlias('test');
		$school->setName('Szkoła testowa');
		$this->em->persist($school);
		$r = $userManager->setup($school);

		if (count($r))
			return $r;

		$user = new \Model\User();
		$user->setEmail('student@put.poznan.pl');
		$user->setUsername('admin');
		$user->setPassword('qwerty');
		$user->setGivenName('Adam');
		$user->setFamilyName('Test');
		$user->setSchool($school);
		$this->em->persist($user);
		$this->em->flush();

		return $r;
	}

}
