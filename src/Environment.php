<?php
namespace vr\environment;

use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Environment
 *
 * Complete new way to describe environment and modifications. Example of configuration
 *
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
 *
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
     * @var string
     */
    protected $activeFlavor = null;

    /**
     *
     */
    public function init()
    {
        $found = false;

        foreach ($this->flavors as $name => $flavor) {
            if ($found = (!$found && $this->isActive($name))) {
                $this->createInstance($flavor, $name);
                break;
            }
        }

        if (!$found && $this->default) {
            $this->createInstance(ArrayHelper::getValue($this->flavors, $this->default), $this->default);
        }
    }

    /**
     * @return null
     */
    public function getActiveFlavor()
    {
        return $this->activeFlavor;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function isActive($name)
    {
        return file_exists(\Yii::getAlias('@app/' . $name));
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
        $instance = \Yii::createObject($flavor);

        $instance->name = $name;
        $this->apply($name, $instance);

        return $instance;
    }

    /**
     * @param        $name
     * @param Flavor $instance
     */
    protected function apply($name, $instance)
    {
        $this->activeFlavor = $name;

        if ($instance->prerequisites) {
            call_user_func($instance->prerequisites);
        }

        if ($instance->prepare()) {
            $instance->apply();
        };
    }
}