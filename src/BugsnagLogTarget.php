<?php
namespace jcherniak\yii2bugsnag;

use Yii;
use \yii\log\Logger;

class BugsnagLogTarget extends \yii\log\Target
{
    protected static $instance = null;
    protected static $exportedMessages = [];

    public function __construct()
    {
        self::$instance = $this;
    }

    public function export()
    {
        self::$exportedMessages = array_merge(self::$exportedMessages, $this->messages);

        Yii::$app->bugsnag->exportingLog = true;
        try
        {
            foreach ($this->messages as $message)
            {
                list($message, $level, $category, $timestamp, $trace) = $message; 
                
                if ($level == Logger::LEVEL_ERROR)
                {
                    Yii::$app->bugsnag->notifyError($category, $message . " ($timestamp)", $trace);
                }
                elseif ($level == Logger::LEVEL_WARNING)
                {
                    Yii::$app->bugsnag->notifyWarning($category, $message . " ($timestamp)", $trace);
                }
            }

            Yii::$app->bugsnag->exportingLog = false;
        }
        catch (\Exception $e)
        {
            Yii::$app->bugsnag->exportingLog = false;
            throw $e;
        }
    }

    public static function getMessages()
    {
        $messages = self::$exportedMessages;

        if (isset(self::$instance))
        {
            $messages = array_merge($messages, self::$instance->messages);
        }

        static $levelMap = [
            Logger::LEVEL_ERROR => 'error',
            Logger::LEVEL_WARNING => 'warning',
            Logger::LEVEL_INFO => 'info', 
            Logger::LEVEL_TRACE => 'trace',
            Logger::LEVEL_PROFILE => 'profile',
        ];

        return array_map(
            function($message) use ($levelMap)
            {
                list($message, $level, $category, $timestamp) = $message; 

                $date = date('Y-m-d H:i:s', $timestamp) . '.' . substr(fmod($timestamp, 1), 2, 4);
                return "{$levelMap[$level]} - ({$category}) @ {$date} - {$message}";
            }, 
            self::$exportedMessages, 
            isset(self::$instance) ? self::$instance->messages : [] 
        );
    }
}
