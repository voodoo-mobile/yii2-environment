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
            if ($found = (!$found && $this->isActive($name))) {
                $this->createInstance($flavor, $name);

                return;
            }
        }

        if (!$found && $this->default) {
            $this->createInstance(ArrayHelper::getValue($this->flavors, $this->default), $this->default);
        } else {
            throw new \Exception('Could not find appropriate flavor to load. Is it even legal?');
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

        $this->activeFlavor = $instance->name = $name;
        $this->apply($instance);

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
}