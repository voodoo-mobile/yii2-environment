<?php
namespace vm\environment;

use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Environment
 *
 * Complete new way to describe environment and modifications. Example of configuration
 *
 * 'environment'  => [
 *           'class'   => '\yii2vm\flavors\Environment',
 *          'default' => 'production',
 *          'flavors' => [
 *              'develop'    => [
 *                  'class'        => '\yii2vm\flavors\InlineFlavor',
 *                  'components' => [
 *                      'db' => [
 *                          'username' => 'myusername'
 *                      ]
 *                  ]
 *              ],
 *              'production' => [
 *                  'class'         => '\yii2vm\flavors\ExternalFlavor',
 *                  'filename'      => '@app/production.json',
 *                  'prerequisites' => function () {
 *                  }
 *              ]
 *          ]
 *      ],
 *
 * @package yii2vm\config
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
            /** @var Flavor $instance */
            $instance = \Yii::createObject($flavor);

            if ($found = (!$found && $this->isActive($name))) {
                $this->apply($name, $instance);
            }
        }

        if (!$found && $this->default) {
            $instance = \Yii::createObject(ArrayHelper::getValue($this->flavors, $this->default));
            $this->apply($this->default, $instance);
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