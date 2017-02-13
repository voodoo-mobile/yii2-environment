<?php
namespace vr\environment;

use vr\core\ArrayObject;
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

    /** @var null  */
    public $additionalAlias = null;

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
     * @param string $name
     *
     * @return bool
     */
    protected function isActive($name)
    {
        if (!$this->additionalAlias) {
            return file_exists(\Yii::getAlias('@app/' . $name));
        }

        return file_exists(\Yii::getAlias($this->additionalAlias . '/' . $name));
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