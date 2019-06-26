<?php

namespace iutbay\yii2\mm\models;

use Yii;
use yii\helpers\FileHelper;

class UploadForm extends \yii\base\Model
{

    const SCENARIO_IMAGE = 'image';
    const SCENARIO_AUDIO = 'audio';
    const SCENARIO_VIDEO = 'video';

    /**
     * @var string
     */
    public $path;

    /**
     * @var \yii\web\UploadedFile
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['path', 'file'], 'required', 'strict' => true],
            ['path', 'string'],
            ['path', 'validatePath'],
            [['file'],'file','skipOnEmpty'=>false,'mimeTypes'=>['image/*'],'maxSize' => 2 * 1000 * 1000,'maxFiles' => 1, 'on' => self::SCENARIO_IMAGE],
            [['file'],'file','skipOnEmpty'=>false,'mimeTypes'=>['video/*'],'maxSize' => 512 * 1000 * 1000,'maxFiles' => 1, 'on' => self::SCENARIO_VIDEO],
            [['file'],'file','skipOnEmpty'=>false,'mimeTypes'=>['audio/*'],'maxSize' => 512 * 1000 * 1000,'maxFiles' => 1, 'on' => self::SCENARIO_AUDIO],
            [['file'],'file','skipOnEmpty'=>false,'mimeTypes'=>['audio/*','video/*','image/*'],'maxSize' => 512 * 1000 * 1000,'maxFiles' => 1, 'on' => self::SCENARIO_DEFAULT],
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * Validate path
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePath($attribute, $params)
    {
        $fs = Yii::$app->getModule('mm')->fs;
        $this->$attribute = $fs->normalizePath($this->$attribute);

        if (!empty($this->$attribute) && !$fs->has($this->$attribute)) {
            $this->addError($attribute, 'Invalid path.');
        }
    }

    /**
     * Upload files
     */
    public function upload()
    {
        if ($this->validate()) {
            $file = $this->file;
            $fs = Yii::$app->getModule('mm')->fs;
            $path = "{$this->path}/{$file->baseName}.{$file->extension}";

            $counter = 1;
            while ($fs->has($path)) {
                $path = "{$this->path}/{$file->baseName}_{$counter}.{$file->extension}";
                $counter++;
            }

            if ($stream = fopen($file->tempName, 'r+')) {
                $write = $fs->writeStream($path, $stream);
                fclose($stream);                
                if ($write) {
                    return true;
                } else {
                    $this->addError('path', 'Failed to write file.');
                }
            } else {
                $this->addError('file', 'Failed to get file.');
            }
        }
        return false;
    }

}
