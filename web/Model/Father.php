<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 * @HasLifecycleCallbacks()
 */
class Father extends \Model\User {

    /** @Column(type="string") */
    protected $phone;

    /**
     * @OneToOne(targetEntity="\Model\Student", mappedBy="father")
     */
    protected $student;

}
