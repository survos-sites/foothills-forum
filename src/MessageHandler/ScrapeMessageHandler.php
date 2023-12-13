<?php

namespace App\MessageHandler;

use App\Message\ScrapeMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ScrapeMessageHandler
{
    public function __invoke(ScrapeMessage $message)
    {

        // do something with your message
    }
}
