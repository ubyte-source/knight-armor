<?PHP

namespace Knight\armor\output;

use stdClass;

class Data
{
    protected $status;

    public function setStatus(bool $status) :  self
    {
        $this->status = $status;
        return $this;
    }

    public function out() : array
    {
        $output = get_object_vars($this);
        $output_filtered = array_filter($output, function ($item) {
            return is_array($item) || is_bool($item) || $item instanceof stdClass || is_string($item) && strlen($item);
        });
        return $output_filtered;
    }
}