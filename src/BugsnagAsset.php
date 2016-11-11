<?php

namespace pinfirestudios\yii2bugsnag;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\AssetBundle;

/**
 * If you would like to use Bugsnag's javascript on your site, simply depend on BugsnagAsset in your AppAsset.
 * This will automatically register Bugsnag's javascript to the page.  Default version is 3.
 * <pre>
 * class AppAsset extends AssetBundle
 * {
 *     public $depends = [
 *         'pinfirestudios\yii2bugsnag\BugsnagAsset',
 *     ];
 * }
 * </pre>
 */
class BugsnagAsset extends AssetBundle
{
    /**
     * @var integer Bugsnag javascript version
     */
    public $version = 3;

    /**
     * @type boolean Use the Cloudfront CDN (which will have CORS issues @see https://github.com/bugsnag/bugsnag-js/issues/155
     */
    public $useCdn = false;

    /**
     * Initiates Bugsnag javascript registration
     */
    public function init()
    {
        if (!Yii::$app->has('bugsnag'))
        {
            throw new InvalidConfigException('BugsnagAsset requires Bugsnag component to be enabled');
        }

        if (!in_array($this->version, [2, 3]))
        {
            throw new InvalidConfigException('Bugsnag javascript only supports version 2 or 3');
        }

        $this->registerJavascript();

        parent::init();
    }


    /**
     * Registers Bugsnag JavaScript to page
     */
    private function registerJavascript()
    {
        $filePath = '//d2wy8f7a9ursnm.cloudfront.net/bugsnag-' . $this->version . '.js';
        if (!$this->useCdn)
        {
            $this->sourcePath = '@bower/bugsnag/src';
            $filePath = 'bugsnag.js';
        }

        $this->js[] = [
            $filePath, 
            'data-apikey' => Yii::$app->bugsnag->bugsnag_api_key,
            'data-releasestage' => Yii::$app->bugsnag->releaseStage,
            'data-appversion' => Yii::$app->version,
            'position' => \yii\web\View::POS_HEAD,
        ];

        // Include this wrapper since bugsnag.js might be blocked by adblockers.  We don't want to completely die if so.
        $js = 'var Bugsnag = Bugsnag || {};';

        if (!Yii::$app->user->isGuest)
        {
            $userId = Json::htmlEncode(Yii::$app->user->id);
            $js .= "Bugsnag.user = { id: $userId };";
        }

        if (!empty(Yii::$app->bugsnag->notifyReleaseStages))
        {
            $releaseStages = Json::htmlEncode(Yii::$app->bugsnag->notifyReleaseStages);
            $js .= "Bugsnag.notifyReleaseStages = $releaseStages;";
        }

        Yii::$app->view->registerJs($js, \yii\web\View::POS_BEGIN);
    }
}
