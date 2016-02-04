<?php
namespace vm\environment;

use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ExternalFlavor
 * @package yii2vm\flavors
 */
class ExternalFlavor extends Flavor
{
    /**
     * @var
     */
    public $filename;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function prepare()
    {
        $filename = \Yii::getAlias($this->filename);
        if (!file_exists($filename)) {
            throw new \Exception('Missing file named ' . $filename);
        }

        $data             = Json::decode(file_get_contents($filename));
        $this->components = ArrayHelper::getValue($data, 'components', []);
        $this->params     = ArrayHelper::getValue($data, 'params', []);
    }
}