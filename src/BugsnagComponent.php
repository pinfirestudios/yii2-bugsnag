<?php
namespace jcherniak\yii2bugsnag;

use Yii;
use \yii\web\View;

class BugsnagComponent extends \yii\base\Component
{
    public $bugsnag_api_key;

    public $releaseStage = null;
    public $notifyReleaseStages;

    public $filters = ['password'];

    protected $client;

    /**
     * True if we are in BugsnagLogTarget::export(), then don't trigger a flush, causing an
     * infinite loop
     * @var boolean
     */
    public $exportingLog = false;

    public function init()
    {
        if (empty($this->bugsnag_api_key))
        {
            throw new \yii\base\InvalidConfigException("bugsnag_api_key must be set");
        }

        $this->client = new \Bugsnag_Client($this->bugsnag_api_key);

        if (!empty($this->notifyReleaseStages))
        {
            $this->client->setNotifyReleaseStages($this->notifyReleaseStages);
        }

        $this->client->setFilters($this->filters);

        $this->client->setBatchSending(true);
        $this->client->setBeforeNotifyFunction([$this, 'beforeBugsnagNotify']);

        if (empty($this->releaseStage))
        {
            $this->releaseStage = defined('YII_ENV') ? YII_ENV : 'production';
        }

        Yii::trace("Setting release stage to {$this->releaseStage}.", __CLASS__);
        $this->client->setReleaseStage($this->releaseStage);
    }

    /**
     * Returns user information
     *
     * @return array
     */
    public function getUserData()
    {
        // Don't crash if not using yii\web\User
        if (empty(Yii::$app->components['user']) || empty(Yii::$app->components['user']['identityClass']))
        {
            return null;
        }

        $ret = [];
        if (isset(Yii::$app->user->id))
        {
            $ret['id'] = Yii::$app->user->id;
        }
    }

    public function getClient() 
    {        
        $clientUserData = $this->getUserData();
        if (!empty($clientUserData))
        {
            $this->client->setUser($clientUserData);
        }
        
        return $this->client;
    }

    public function beforeBugsnagNotify(\Bugsnag_Error $error)
    {
        if (!$this->exportingLog)
        {
            Yii::getLogger()->flush(true);
        }

        if (isset($error->metaData['trace']))
        {
            $trace = $error->metaData['trace'];
            unset($error->metaData['trace']);
            
            if (!empty($trace))
            {
                $firstFrame = array_shift($trace);
                $error->setStacktrace(\Bugsnag_Stacktrace::fromBacktrace($error->config, $trace, $firstFrame['file'], $firstFrame['line']));
            }
        }
    
        $error->setMetaData([
            'logs' => BugsnagLogTarget::getMessages(),
        ]);
    }

    public function notifyError($category, $message, $trace = null)
    {
        $this->getClient()->notifyError($category, $message, ['trace' => $trace], 'error');
    }

    public function notifyWarning($category, $message, $trace = null)
    {
        $this->getClient()->notifyError($category, $message, ['trace' => $trace], 'warning');
    }

    public function notifyInfo($category, $message, $trace = null)
    {
        $this->getClient()->notifyError($category, $message, ['trace' => $trace], 'info');
    }

    public function notifyException(\Exception $exception, $severity = null)
    {
        $metadata = null;
        if ($exception instanceof BugsnagCustomMetadataInterface)
        {
            $metadata = $exception->getMetadata();
        }

        if ($exception instanceof BugsnagCustomContextInterface)
        {
            $this->getClient()->setContext($exception->getContext());
        }
        
        $this->getClient()->notifyException($exception, $metadata, $severity);
    }

    public function runShutdownHandler()
    {
        if (!$this->exportingLog)
        {
            Yii::getLogger()->flush(true);
        }

        $this->getClient()->shutdownHandler();
    }

    public function getJavascript()
    {
        $ret = <<<JS
            <script src='//d2wy8f7a9ursnm.cloudfront.net/bugsnag-2.min.js'
                data-apikey='{$this->bugsnag_api_key}'></script>
JS;

        $userObj = $this->getUserData();
        $userJson = json_encode($userObj);

        $ret .= <<<JS
        <script type="text/javascript">
            Bugsnag.user = {$userJson};
            Bugsnag.releaseStage = '{$this->releaseStage}';
        </script>
JS;

        return $ret;
    }
}
