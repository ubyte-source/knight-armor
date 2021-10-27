<?PHP

namespace Knight\armor\output;

use stdClass;

use Knight\armor\Request;

class Data
{
    const ONLY = 'only';

    protected $status;

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

    public function setStatus(bool $status) :  self
    {
        $this->status = $status;
        return $this;
    }

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