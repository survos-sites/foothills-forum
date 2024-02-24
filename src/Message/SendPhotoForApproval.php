<?php

namespace App\Message;

use App\Entity\Submission;
use App\Repository\SubmissionRepository;

final class SendPhotoForApproval
{
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
