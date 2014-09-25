<?php
/**
 * Created by adwyii.
 * Author: Denis Porplenko <denis.porplenko@pdffiller.com>
 * Date: 9/24/14
 * Time: 6:31 PM
 */

namespace denisog\gah\googleads;
use yii\log\Logger;

/**
 * Description class.
 * Author: Denis Porplenko <denis.porplenko@pdffiller.com>
 */
class ReportUtils  extends \ReportUtils{

    const ATTEMPS = 5;

    const CATEGORY = 'download_report_adwords';

    /**
     * [RUS] Устанавливаю новый  логгер, который будт писать в логи все попытки послать повторно запросы.
     */
    public static function init()
    {
        $dispatcher = \Yii::$app->getLog();

        if (empty($dispatcher->targets[self::CATEGORY])) {

            $logger = \Yii::createObject([
                'class' => '\yii\log\FileTarget',
                'logFile' => \Yii::getAlias("@runtime/logs/" . self::CATEGORY),
                'categories' => [self::CATEGORY],
                'logVars' => [],
            ]);

            $logger->setLevels(['info']);

            $dispatcher->targets[self::CATEGORY] = $logger;
        }
    }

    /**[RUS] Этот метод есть обертка, для методы получения отчета (\ReportUtils::DownloadReportWithAwql).
     * В этой функции, если  метод получил 500 ошибку, происходит пауза(3-15 секунд) и повторный запрос.
     * Количество повторение self::ATTEMPS
     *
     * @param string $reportQuery
     * @param null $path
     * @param \AdWordsUser $user
     * @param string $reportFormat
     * @param array $options
     * @return bool|mixed
     * @throws \yii\console\Exception
     */
    public static function DownloadReportWithAwql($reportQuery, $path = NULL,
                                                  \AdWordsUser $user, $reportFormat, array $options = NULL) {
        self::init();

        $lastIteration = 0;

        do {
            try{
                $throwException = true;
                \ReportUtils::DownloadReportWithAwql($reportQuery, $path,  $user, $reportFormat, $options);

            } catch(\Exception $e){

                if ($e->getCode() >= 500 && $e->getCode() < 600) {
                    $lastIteration++;

                    if ($lastIteration < ReportUtils::ATTEMPS) {
                        $throwException = false;
                        \Yii::info($reportQuery,self::CATEGORY);
                        sleep(mt_rand(10, 35));
                    }
                }

                if ($throwException) {
                    $message = "Error with download report. Query:{$reportQuery} Error:{$e->getMessage()} CodeError:{$e->getCode()}";
                    throw new \yii\console\Exception($message);
                }


            }
        } while($throwException === false);

        return true;
    }
} 