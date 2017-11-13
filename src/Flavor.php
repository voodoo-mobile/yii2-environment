<?php

namespace vr\environment;

use Closure;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class Flavor
 * @package vr\environment
 * @property bool $isActive
 */
abstract class Flavor extends Object
{
    /**
     * @var
     */
    public $name;

    /**
     * @var Closure | null
     */
    public $prerequisites;

    /**
     * @var
     */
    public $components = [];

    /**
     * @var array
     */
    public $params = [];

    /**
     * @return bool
     */
    public abstract function prepare(): bool;

    /**
     * @return bool
     */
    abstract public function getIsActive(): bool;

    /**
     *
     */
    public function apply()
    {
        \Yii::$app->components = ArrayHelper::merge(\Yii::$app->components, $this->components);
        \Yii::$app->params     = ArrayHelper::merge(\Yii::$app->params, $this->params);
    }
}