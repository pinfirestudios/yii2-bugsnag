<?php
namespace jcherniak\yii2bugsnag;

use Yii;

class BugsnagErrorHandler extends \yii\web\ErrorHandler
{
    public function logException($exception)
    {
        Yii::$app->bugsnag->notifyException($exception);
    }

    /**
     * Handles fatal PHP errors
     */
    public function handleFatalError()
    {
        // Call into Bugsnag client's errorhandler since this will potentially kill the script below 
        Yii::$app->bugsnag->runShutdownHandler();

        parent::handleFatalError();
    }
}
