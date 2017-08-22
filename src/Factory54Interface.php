<?php
namespace KS;

/**
 * A class that provides construction and creation of arbitrary classes
 *
 * This is to be passed to nearly all Skel objects, and should provide a robust dependency injection system
 * that allows users to optionally override class types for a variety of different contexts
 */
interface Factory54Interface {
  /**
   * Create a new instance of a class using the `new` keyword.
   *
   * Note that you can pass extra instantiation variables in after the `$subtype` varible
   *
   * This method has been named "neew" because of incompatibilities in php 5.4 with the "new"
   * keyword.
   *
   * @param string $class - a string description of the class you want
   * @param string|null $subtype - an optional descriptor to further specify which class you want
   * @return mixed - a new instance of the class you want to instantiate
   */
  function neew($class, $subtype=null);

  /**
   * Create a new instance of a class using the class's static `create` method.
   *
   * Note that you can pass extra instantiation variables in after the `$subtype` varible
   *
   * @param string $class - a string description of the class you want
   * @param string|null $subtype - an optional descriptor to further specify which class you want
   * @return mixed - a new instance of the class you want to instantiate
   */
  function create($class, $subtype=null);

  /**
   * Get a string that can be used to instantiate the class indicated by `$class` and `$subtype`
   *
   * @param string $class - a string description of the class you want
   * @param string|null $subtype - an optional descriptor to further specify which class you want
   * @return string - a string representation of an instantiatable class
   */
  function getClass($class, $subtype=null);
}

