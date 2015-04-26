<?php

namespace Model;

use \Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity()
 * @Table(schema="public")
 */
class School {

	use \AutoProperty;

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @Column(type="string", unique=true)
	 */
	protected $alias;

	/**
	 * @Column(type="string")
	 */
	protected $name;

	/** @Column(type="datetime") */
	protected $ts;

	/** @OneToMany(targetEntity="\Model\User", mappedBy="school") */
	protected $users;

	public function __construct() {
		if (!isset($this->ts))
			$this->ts = new \DateTime;
		$this->users = new Collection();
	}

	public function getSchema() {
		return sprintf('school_%s', $this->getAlias());
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

}
