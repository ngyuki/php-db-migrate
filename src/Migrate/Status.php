<?php
namespace ngyuki\DbMigrate\Migrate;

class Status
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string|null
     */
    private $script;

    /**
     * @var bool
     */
    private $applied;

    public function __construct($version)
    {
        $this->version = $version;
        $this->applied = false;
    }

    /**
     * @param string $script
     */
    public function setScript($script)
    {
        $this->script = $script;
    }

    /**
     * @param bool $applied
     */
    public function setApplied($applied)
    {
        $this->applied = $applied;
    }

    public function getScript()
    {
        return $this->script;
    }

    public function hasScript()
    {
        return $this->script !== null;
    }

    public function isApplied()
    {
        return $this->applied;
    }
}
