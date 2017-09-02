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
     * @var string|null
     */
    private $source;

    /**
     * @var string|null
     */
    private $content;

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
        $this->source = null;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param bool $applied
     */
    public function setApplied($applied)
    {
        $this->applied = $applied;
    }

    public function getContent()
    {
        if ($this->source === null) {
            if ($this->script !== null) {
                $this->source = file_get_contents($this->script);
            };
        }
        if ($this->source !== false && $this->source !== null) {
            return $this->source;
        } else {
            return $this->content;
        }
    }

    /**
     * ファイルでもDBでもどっちでも良いのでスクリプトコンテンツがあるかどうか返す
     */
    public function hasContent()
    {
        return $this->getContent() !== null;
    }

    /**
     * マイグレーションスクリプトが存在するかどうか返す
     *
     * @return bool
     */
    public function isMissing()
    {
        return $this->script === null;
    }

    public function isApplied()
    {
        return $this->applied;
    }
}
