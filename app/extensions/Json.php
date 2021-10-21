<?php
/**
 * This is an extension of Jsonq in order to use custom conditionals
 * Adds truematch conditional (based on match) to use regex across the fill key
 * Adds the year/month/day formatted conditionals
 * Condition_model below Json contains the conditions functions
 *
 */
namespace Rmcc;
use Nahid\JsonQ\Jsonq as Jsonq;
 
class Json extends Jsonq {
  
  // custom conditions map array; adds custom conditionals to it
  protected static $_conditionsMap = [
    '=' => 'equal',
    'eq' => 'equal',
    '==' => 'strictEqual',
    'seq' => 'strictEqual',
    '!=' => 'notEqual',
    'neq' => 'notEqual',
    '!==' => 'strictNotEqual',
    'sneq' => 'strictNotEqual',
    '>' => 'greaterThan',
    'gt' => 'greaterThan',
    '<' => 'lessThan',
    'lt' => 'lessThan',
    '>=' => 'greaterThanOrEqual',
    'gte' => 'greaterThanOrEqual',
    '<=' => 'lessThanOrEqual',
    'lte' => 'lessThanOrEqual',
    'in'    => 'in',
    'notin' => 'notIn',
    'inarray' => 'inArray',
    'notinarray' => 'notInArray',
    'null' => 'isNull',
    'notnull' => 'isNotNull',
    'exists' => 'exists',
    'notexists' => 'notExists',
    'startswith' => 'startWith',
    'endswith' => 'endWith',
    'match' => 'match',
    'truematch' => 'truematch',
    'contains' => 'contains',
    'dates' => 'dateEqual',
    'year' => 'yearEqual',
    'month' => 'monthEqual',
    'day' => 'dayEqual',
    'instance'  => 'instance',
    'any'  => 'any',
    'notany'  => 'notany',
  ];
  
  /**
   * For custom ConditionalFactory.php
   * Build or generate a function for applies condition from operator
   * @param $condition
   * @return array
   * @throws ConditionNotAllowedException
   */
  protected function makeConditionalFunctionFromOperator($condition)
  {
    
    if (!isset(self::$_conditionsMap[$condition])) {
      throw new \Nahid\QArray\Exceptions\ConditionNotAllowedException("Exception: {$condition} condition not allowed");
    }

    $function = self::$_conditionsMap[$condition];
    if (!is_callable($function)) {
      if (!method_exists(Condition_model::class, $function)) {
        throw new \Nahid\QArray\Exceptions\ConditionNotAllowedException("Exception: {$condition} condition not allowed");
      }

      $function = [Condition_model::class, $function];
    }

    return $function;
  }
  
}

use Nahid\QArray\Exceptions\KeyNotPresentException;

final class Condition_model {
  
  /**
   * Simple equals
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function equal($value, $comparable)
  {
      return $value == $comparable;
  }

  /**
   * Strict equals
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function strictEqual($value, $comparable)
  {
      return $value === $comparable;
  }

  /**
   * Simple not equal
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function notEqual($value, $comparable)
  {
      return $value != $comparable;
  }

  /**
   * Strict not equal
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function strictNotEqual($value, $comparable)
  {
      return $value !== $comparable;
  }

  /**
   * Strict greater than
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function greaterThan($value, $comparable)
  {
      return $value > $comparable;
  }

  /**
   * Strict less than
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function lessThan($value, $comparable)
  {
      return $value < $comparable;
  }

  /**
   * Greater or equal
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function greaterThanOrEqual($value, $comparable)
  {
      return $value >= $comparable;
  }

  /**
   * Less or equal
   *
   * @param mixed $value
   * @param mixed $comparable
   *
   * @return bool
   */
  public static function lessThanOrEqual($value, $comparable)
  {
      return $value <= $comparable;
  }

  /**
   * In array
   *
   * @param mixed $value
   * @param array $comparable
   *
   * @return bool
   */
  public static function in($value, $comparable)
  {
      return (is_array($comparable) && in_array($value, $comparable));
  }

  /**
   * Not in array
   *
   * @param mixed $value
   * @param array $comparable
   *
   * @return bool
   */
  public static function notIn($value, $comparable)
  {
      return (is_array($comparable) && !in_array($value, $comparable));
  }

  public static function inArray($value, $comparable)
  {
      if (!is_array($value)) return false;

      return in_array($comparable, $value);
  }

  public static function inNotArray($value, $comparable)
  {
      return !static::inArray($value, $comparable);
  }
  
  public static function notany($value, $comparable)
  {
      if (is_array($value)) {
          return !in_array($comparable, $value);
      }

      return false;
  }

  /**
   * Is null equal
   *
   * @param mixed $value
   *
   * @return bool
   */
  public static function isNull($value, $comparable)
  {
      return is_null($value);
  }

  /**
   * Is not null equal
   *
   * @param mixed $value
   *
   * @return bool
   */
  public static function isNotNull($value, $comparable)
  {
      return !$value instanceof KeyNotExists && !is_null($value);
  }

  public static function notExists($value, $comparable)
  {
      return $value instanceof KeyNotExists;
  }

  public static function exists($value, $comparable)
  {
      return !static::notExists($value, $comparable);
  }

  /**
   * Start With
   *
   * @param mixed $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function startWith($value, $comparable)
  {
      if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
          return false;
      }

      if (preg_match("/^$comparable/", $value)) {
          return true;
      }

      return false;
  }

  /**
   * End with
   *
   * @param mixed $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function endWith($value, $comparable)
  {
      if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
          return false;
      }

      if (preg_match("/$comparable$/", $value)) {
          return true;
      }

      return false;
  }

  /**
   * Match with pattern
   *
   * @param mixed $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function match($value, $comparable)
  {
      if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
          return false;
      }
  
      $comparable = trim($comparable);
  
      if (preg_match("/^$comparable$/", $value)) {
          return true;
      }
  
      return false;
  }
  
  /**
   * Match with pattern*** OVERWRITE from Qarray
   *
   * @param mixed $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function truematch($value, $comparable)
  {
      if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
          return false;
      }

      $comparable = trim($comparable);

      if (preg_match("/$comparable/", $value)) {
          return true;
      }

      return false;
  }

  /**
   * Contains substring in string
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function contains($value, $comparable)
  {
      return (strpos($value, $comparable) !== false);
  }

  /**
   * Dates equal
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function dateEqual($value, $comparable, $format = 'Y-m-d')
  {
      $date = date($format, strtotime($value));
      return $date == $comparable;
  }
  
  /**
   * CUSTOM
   * Year equal
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function yearEqual($value, $comparable, $format = 'Y')
  {
      $date = date($format, strtotime($value));
      // print_r($date == $comparable);
      return $date == $comparable;
  }
  
  /**
   * CUSTOM
   * Month equal
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function monthEqual($value, $comparable, $format = 'm')
  {
      $date = date($format, strtotime($value));
      return $date == $comparable;
  }
  
  /**
   * CUSTOM
   * Day equal
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function dayEqual($value, $comparable, $format = 'd')
  {
      $date = date($format, strtotime($value));
      return $date == $comparable;
  }

  /**
   * is given value instance of value
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function instance($value, $comparable)
  {
      return $value instanceof $comparable;
  }

  /**
   * is given value exits in given key of array
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function any($value, $comparable)
  {
      if (is_array($value)) {
          return in_array($comparable, $value);
      }

      return false;
  }

  /**
   * is given value exits in given key of array
   *
   * @param string $value
   * @param string $comparable
   *
   * @return bool
   */
  public static function execFunction($value, $comparable)
  {
      if (is_array($value)) {
          return in_array($comparable, $value);
      }

      return false;
  }
  
}