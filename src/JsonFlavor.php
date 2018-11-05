<?php

namespace vr\environment;

use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class JsonFlavor
 * @package vr\environment
 */
class JsonFlavor extends Flavor
{
    /**
     * @var
     */
    public $filename;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function prepare(): bool
    {
        $filename = $this->getFile();

        if (!file_exists($filename)) {
            throw new \Exception('Missing file named ' . $filename);
        }

        $data = Json::decode(file_get_contents($filename));

        $this->components = ArrayHelper::getValue($data, 'components', []);
        $this->params     = ArrayHelper::getValue($data, 'params', []);

        return true;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return file_exists(\Yii::getAlias($this->path . '/' . $this->name)) && file_exists($this->getFile());
    }

    /**
     * @return bool|string
     */
    private function getFile()
    {
        if (empty($this->filename)) {
            $this->filename = sprintf('@app/' . $this->name . '.flavor.json');
        }

        $filename = \Yii::getAlias($this->filename);

        return $filename;
    }
}