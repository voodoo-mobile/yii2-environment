<?php

namespace vr\environment;

/**
 * Class InlineFlavor
 * @package vr\environment
 */
class InlineFlavor extends Flavor
{
    /**
     *
     */
    public function prepare(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return file_exists(\Yii::getAlias($this->path . '/' . $this->name));
    }
}