<?php

namespace App\Message;

use App\Entity\Submission;
use App\Repository\SubmissionRepository;

final class SendPhotoForApproval
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

    //     private $name;

         public function __construct(
             private int $submissionId
         )
         {
         }

    public function getSubmissionId(): int
    {
        return $this->submissionId;
    }
}
