<?php

namespace Tedy\GridFieldCustom;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use SilverStripe\Dev\BuildTask;
use Monolog\Handler\StreamHandler;
use SilverStripe\Control\Director;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Environment;

/**
 * Class GridFieldDeleteAllTask
 * The dev/tasks interface (when logged in with ADMIN permissions) queues the job by default.
 * This runs via queued jobs, which already has a cron task configured for it.
 */
class GridFieldDeleteAllTask extends BuildTask
{
    protected $title = "GridField Delete All Task";

    protected $description =  'This is a run once task to delete all records in a gridfield';

    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class,
    ];

    /**
     * This will be set automatically, as long as MyController is instantiated via Injector
     *
     * @var Logger
     */
    private $logger;

    /**
     * @param HTTPRequest $request
     * @throws \Exception
     */
    public function run($request)
    {
        $this->addLogHandlers();

        $params = $request->requestVars();
        $this->logger->info($this->title, $params);

        if (empty($params['class'])) {
            return 0;
        }

        $class = $params['class'];
        $records = DataObject::get($class);
        $cleaned = $records->count();

        $this->logger->info($class, [$cleaned]);

        foreach ($records as $index => $record) {
            if ($record->hasExtension(Versioned::class)) {
                $record->deleteFromStage('Stage');
                $record->deleteFromStage('Live');
            } else {
                $record->delete();
            }
        }

        $this->logger->info("Cleaned {$cleaned} of {$class}");

        if ($email = $params['email']) {
            $this->handleCompletion($cleaned, $email);
        }
    }

    /**
     *
     * @param LoggerInterface $logger
     * @return GridFieldDeleteAllTask
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function addLogHandlers()
    {
        if (Director::is_cli()) {
            $this->logger->pushHandler(new StreamHandler('php://stdout'));
            $this->logger->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));
        }
    }

    /**
    * Send an email to the supplied user
    * upon completion
    *
    */
    public function handleCompletion($total, $email, $name = '')
    {
        $recipient = ($email === 'admin') ? Environment::getEnv('SS_SEND_ALL_EMAILS_TO') : $email;
       
        $email = new Email();
        $email->setTo($recipient);
        $email->setSubject('A deletion task requested by you has completed.');

        $message = sprintf('<p>Hi, %s</p>', $name);
        $message .= sprintf('<p>Task %s has completed.</p>', $this->title);
        $message .= sprintf('<p>%s records have been deleted.</p>', $total);
        $email->setBody($message);

        return $email->send();
    }
}
