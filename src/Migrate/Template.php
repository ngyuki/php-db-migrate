<?php
namespace ngyuki\DbMigrate\Migrate;

class Template
{
    private $delimiter = ';';

    /**
     * @param string $fn
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function render($fn, array $params)
    {
        extract(func_get_arg(1));
        ob_start();
        try {
            include func_get_arg(0);
            return ob_get_clean();
        } catch (\Exception $ex) {
            ob_end_clean();
            throw $ex;
        }
    }

    /**
     * @param string $delimiter
     * @return $this|string
     */
    public function delimiter($delimiter = null)
    {
        if (func_num_args() === 0) {
            return $this->delimiter;
        } else {
            $this->delimiter = $delimiter;
            return $this;
        }
    }
}
