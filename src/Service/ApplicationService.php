<?php 

namespace App\Service;

use App\Entity\Company;
use App\Entity\Job;
use Psr\Log\LoggerInterface;

final class ApplicationService
{
    public function __construct(
        private LoggerInterface $logger
        )
    {
    }


}
