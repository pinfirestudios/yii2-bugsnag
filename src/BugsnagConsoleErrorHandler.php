<?php
namespace jcherniak\yii2bugsnag;

use Yii;

/**
 * Basic error handler to deal with console commands
 */
class BugsnagConsoleErrorHandler extends \yii\console\ErrorHandler
{
    public function logException($exception)
    {
        Yii::$app->bugsnag->notifyException($exception);
        parent::logException($exception);
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
