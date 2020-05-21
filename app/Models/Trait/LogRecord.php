<?php

namespace App\Models;
trait LogRecord
{
    //注意,必须以 boot 开头
    public static function bootLogRecord()
    {
        foreach (static::getModelEvents() as $event) {
            static::$event(function ($model) {
                $model->setRemind();
            });
        }
    }


    public static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }
        return ['updated'];
    }

    public function setRemind()
    {
    }
}