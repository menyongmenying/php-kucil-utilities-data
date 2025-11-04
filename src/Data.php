<?php

namespace Kucil\Utilities;

use AllowDynamicProperties;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Countable;
use Traversable;


/**
 * A flexible data object class that acts as an upgrade to stdClass.
 *
 * This class can be instantiated with an array or an object. Its properties
 * can be accessed as object properties (e.g., $data->key) or as array
 * keys (e.g., $data['key']). It supports selective recursive instantiation,
 * converting nested associative arrays/objects into nested Data objects,
 * while keeping numeric arrays as arrays but processing their contents.
 *
 * It implements ArrayAccess, IteratorAggregate, and Countable to provide
 * comprehensive array-like behavior.
 */
#[AllowDynamicProperties]
class Data implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * Data constructor.
     *
     * @param null|array|object $data      The initial data to populate the object. Default null.
     * @param bool|null         $recursive If true, recursively converts nested arrays and objects. Default false.
     */
    public function __construct($data = null, ?bool $recursive = false)
    {
        if ($data === null) {
            return;
        }

        $sourceData = (array) $data;
        $isTopLevelNumeric = !$this->isAssociativeArray($sourceData);
        $invalidKeyCounter = 1;

        foreach ($sourceData as $key => $value) {
            // For the top-level only, if we get a numeric array, convert its keys
            // to 'data1', 'data2', etc. to make them valid properties.
            $propKey = $isTopLevelNumeric ? 'data' . $invalidKeyCounter++ : $key;

            if ($recursive && (is_array($value) || is_object($value))) {
                // If the value is an object or an associative array, convert it to a Data instance.
                if (is_object($value) || (is_array($value) && $this->isAssociativeArray($value))) {
                    $this->{$propKey} = new self($value, true);
                }
                // If it's a numeric array (a list), keep it as an array,
                // but process each of its items recursively.
                else if (is_array($value)) {
                    $processedList = [];
                    foreach ($value as $item) {
                        if (is_array($item) || is_object($item)) {
                            $processedList[] = new self($item, true);
                        } else {
                            $processedList[] = $item;
                        }
                    }
                    $this->{$propKey} = $processedList;
                }
            } else {
                // Not recursive, or the value is not an array/object, so just assign it.
                $this->{$propKey} = $value;
            }
        }
    }

    /**
     * Checks if an array is associative.
     * An array is considered associative if its keys are not a sequential
     * numeric sequence starting from 0.
     *
     * @param array $arr The array to check.
     * @return bool True if the array is associative, false otherwise.
     */
    private function isAssociativeArray(array $arr): bool
    {
        if ([] === $arr) {
            return false; // Treat empty array as non-associative
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Magic method to get a property's value.
     * Returns null if the property does not exist.
     *
     * @param string $name The name of the property.
     * @return mixed|null The value of the property or null if not found.
     */
    public function __get(string $name)
    {
        return $this->{$name} ?? null;
    }

    /**
     * Magic method to set a property's value.
     *
     * @param string $name  The name of the property.
     * @param mixed  $value The value to set.
     */
    public function __set(string $name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * Magic method to check if a property is set.
     *
     * @param string $name The name of the property.
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->{$name});
    }

    /**
     * Magic method to unset a property.
     *
     * @param string $name The name of the property.
     */
    public function __unset(string $name)
    {
        unset($this->{$name});
    }

    // --- Implementation of ArrayAccess ---

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            // This case is tricky for dynamic properties. PHP doesn't have a clean
            // way to handle $data[] = $value for objects without a predefined array.
            // For now, we can avoid creating an empty property name.
            // A more robust implementation might push to an internal array if one exists.
        } else {
            $this->{$offset} = $value;
        }
    }

    /**
     * Whether an offset exists.
     *
     * @param mixed $offset An offset to check for.
     * @return bool True on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset): void
    {
        unset($this->{$offset});
    }

    /**
     * Returns the value at specified offset.
     * Returns null if the offset does not exist, as per the requirement.
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->{$offset} ?? null;
    }

    // --- Implementation of IteratorAggregate ---

    /**
     * Returns an iterator for the object's public properties.
     * Allows the object to be used in `foreach` loops.
     *
     * @return Traversable An instance of an object implementing Iterator or Traversable.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this);
    }
    
    // --- Implementation of Countable ---

    /**
     * Counts the number of public properties in the object.
     * Allows the object to be used with the `count()` function.
     *
     * @return int The number of properties.
     */
    public function count(): int
    {
        return count(get_object_vars($this));
    }
}

