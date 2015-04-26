<?php

namespace Model;

/**
 * @Entity()
 * @Table(schema="public", name="roles")
 */
class Role {

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/** @Column(type="string") */
	protected $name;

}
