<?php
namespace KS;

/**
 * An interface that specifies a method for handing this object a factory instance
 */

interface FactoryConsumerInterface {
    public function setFactory(Factory $f);
}

