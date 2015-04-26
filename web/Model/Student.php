<?php

namespace Model;

/**
 * @Entity
 */
class Student extends User {

	use \AutoProperty;

	/**
	 * @Column(type="string", length=45, nullable=true)
	 */
	protected $registrationNr;

	/**
	 * @Column(type="string", length=11, nullable=true) 
	 */
	protected $pesel;

	/**
	 * @Column(type="date", nullable=true)
	 */
	protected $birthdate;

	/**
	 * @Column(type="string", length=45, nullable=true) 
	 */
	protected $birthplace;

	/**
	 * @Column(type="integer", nullable=false) 
	 */
	protected $numberInDaily;

}
