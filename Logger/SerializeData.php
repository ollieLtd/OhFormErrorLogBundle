<?php

namespace Oh\FormErrorLogBundle\Logger;

trait SerializeData
{
    private function serialize($data)
    {
        if (is_object($data)) {
            return $this->serializeObject($data);
        } elseif (is_resource($data)) {
            return $this->serializeResource($data);
        } elseif (is_array($data)) {
            return $this->serializeArray($data);
        } else {
            return $this->serializeNonObject($data);
        }
    }

    private function serializeObject($object)
    {
        $data = '';

        // JsonSerializable is for php 5.4
        if (class_exists('\JsonSerializable', false) && $object instanceof \JsonSerializable) {
            $data = json_encode($object);

        // otherwise we could just see if that method exists
        } elseif (method_exists($object, 'jsonSerialize')) {
            $data = json_encode($object->jsonSerialize());

        // some people create a toArray() method
        } elseif (method_exists($object, 'toArray') && is_array($array = $object->toArray())) {
            // JSON_PRETTY_PRINT is > PHP 5.4
            if (defined('JSON_PRETTY_PRINT')) {
                $data = json_encode($array, JSON_PRETTY_PRINT);
            } else {
                $data = json_encode($array);
            }

        // lets try to serialize
        // this could be risky if the object is too large or not implemented correctly
        } elseif (method_exists($object, '__sleep') || $object instanceof Serializable) {
            $data = @serialize($object);
        }

        return $data;
    }

    /**
     * @param resource $resource
     * @return string
     */
    private function serializeResource($resource)
    {
        // we cann't serialize PHP resources
        return '';
    }

    /**
     * @param array $array
     * @return string
     */
    private function serializeArray($array)
    {
        foreach ($array as &$value) {
            if (is_object($value)) {
                $value = $this->serializeObject($value);
            } elseif (is_resource($value)) {
                $value = $this->serializeResource($value);
            }
        }

        return $this->serializeNonObject($array);
    }

    /**
     * @param int|string|array|null $nonObject
     * @return string
     */
    private function serializeNonObject($nonObject)
    {
        $data = '';
        try {
            $data = serialize($nonObject);
        } catch (\Throwable $t) {
            // do nothing, will catch in PHP >= 7.0
        } catch (\Exception $e) {
            // do nothing, will catch in PHP <= 5.6
        } finally {
            return $data;
        }
    }
}
