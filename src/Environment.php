<?php

namespace vr\environment;

use vr\core\ArrayObject;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Environment
 * Complete new way to describe environment and modifications. Example of configuration
 * 'environment'  => [
 *           'class'   => '\vr\flavors\Environment',
 *          'default' => 'production',
 *          'flavors' => [
 *              'develop'    => [
 *                  'class'        => '\vr\flavors\InlineFlavor',
 *                  'components' => [
 *                      'db' => [
 *                          'username' => 'myusername'
 *                      ]
 *                  ]
 *              ],
 *              'production' => [
 *                  'class'         => '\vr\flavors\ExternalFlavor',
 *                  'filename'      => '@app/production.json',
 *                  'prerequisites' => function () {
 *                  }
 *              ]
 *          ]
 *      ],
 * @package vr\environment
 */
class Environment extends Component
{
    /**
     * @var Flavor []
     */
    public $flavors;

    /**
     * @var null
     */
    public $default = null;

    /**
     * @var Flavor
     */
    protected $activeFlavor = null;

    /**
     *
     */
    public function init()
    {
        foreach ($this->flavors as $name => $flavor) {
            $this->activeFlavor = $this->createInstance($flavor, $name);

            if ($this->activeFlavor->isActive) {
                break;
            }
        }

        if (!$this->activeFlavor && $this->default) {
            $this->activeFlavor =
                $this->createInstance(ArrayHelper::getValue($this->flavors, $this->default), $this->default);
        }

        if ($this->activeFlavor) {
            $this->apply($this->activeFlavor);
        }
    }

    /**
     * @param $flavor
     * @param $name
     *
     * @return Flavor
     * @throws \yii\base\InvalidConfigException
     */
    protected function createInstance($flavor, $name)
    {
        /** @var Flavor $instance */
        $instance = \Yii::createObject($flavor, [
            'name' => $name,
        ]);

        return $instance;
    }

    /**
     * @param Flavor $instance
     */
    protected function apply($instance)
    {
        if ($instance->prerequisites) {
            call_user_func($instance->prerequisites);
        }

        if ($instance->prepare()) {
            $instance->apply();
        };
    }

    /**
     * @return null
     */
    public function getActiveFlavor()
    {
        return $this->activeFlavor;
    }

    /**
     * @return ArrayObject
     */
    public function getParams()
    {
        return new ArrayObject(\Yii::$app->params);
    }
}