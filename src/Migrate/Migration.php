<?php
namespace ngyuki\DbMigrate\Migrate;

use RuntimeException;

class Migration
{
    /**
     * @var string|null
     */
    private $script;

    /**
     * @var string|null
     */
    private $scriptContent;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var bool
     */
    private $applied = false;

    /**
     * @param string|null $script
     * @return $this
     */
    public function setScript($script)
    {
        $this->script = $script;
        $this->scriptContent = null;
        return $this;
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param bool $applied
     * @return $this
     */
    public function setApplied($applied)
    {
        $this->applied = $applied;
        return $this;
    }

    public function getContent()
    {
        if ($this->scriptContent === null && $this->script !== null) {
            $scriptContent = file_get_contents($this->script);
            if ($scriptContent === false) {
                throw new RuntimeException("Unable read \"$this->script\"");
            }
            $this->scriptContent = $scriptContent;
        }
        if ($this->scriptContent !== null) {
            return $this->scriptContent;
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
