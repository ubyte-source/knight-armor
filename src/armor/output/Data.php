<?PHP

namespace Knight\armor\output;

use stdClass;

use Knight\armor\Request;

/* The Data class is a helper class that provides a few static methods to help with data validation */

class Data
{
    const ONLY = 'only';

    protected $status;

    /* A static method that returns an array of fields that are only in the request. */
    
    static public function only(string ...$mandatory) : array
    {
        $fields = Request::get(static::ONLY);
        if (null === $fields) return array();

        $fields = explode(chr(44), $fields);
        $fields = array_map('trim', $fields);
        $fields = array_filter($fields);
        $fields = array_merge($fields, $mandatory);
        $fields = array_unique($fields);

        return $fields;
    }

    /**
     * * Set the status of the object to the given status
     * 
     * @param bool status The status of the user.
     * 
     * @return The object itself.
     */
    
    public function setStatus(bool $status) :  self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns an array of the object's properties that are not arrays, booleans, objects, or empty
     * strings
     * 
     * @return An array of the object's properties.
     */
    
    public function out() : array
    {
        $output = get_object_vars($this);
        $output_filtered = array_filter($output, function ($item) {
            return is_array($item)
                || is_bool($item)
                || $item instanceof stdClass
                || is_string($item) && strlen($item);
        });
        return $output_filtered;
    }
}