<?php
/**
 * FintsHbciConfigurator.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Import\Configuration;

use FireflyIII\Models\ImportJob;
use FireflyIII\Repositories\ImportJob\ImportJobRepositoryInterface;

/**
 * Class FintsHbciConfigurator
 * @package FireflyIII\Import\Configuration
 */
class FintsHbciConfigurator implements ConfiguratorInterface
{
    /** @var ImportJob */
    private $job;

    /** @var ImportJobRepositoryInterface */
    private $repository;

    /** @var string */
    private $warning = '';

    /**
     * @param array $data
     * @return bool
     */
    public function configureJob(array $data): bool
    {
        $this->checkJob();
        $stage = $this->getConfig()['stage'] ?? 'initial';
        Log::debug(sprintf('in configureJob(), for stage "%s".', $stage));
        switch ($stage) {
            default:
                throw new FireflyException(sprintf('Cannot store configuration when job is in state "%s"', $stage));
                break;
        }
    }


    public function getNextData(): array
    {
        $this->checkJob();
        $config = $this->getConfig();
        $stage  = $config['stage'] ?? 'initial';

        Log::debug(sprintf('in getNextData(), for stage "%s".', $stage));
        switch ($stage) {
            case 'has-token':
                // simply redirect to Spectre.
                $config['is-redirected'] = true;
                $config['stage']         = 'user-logged-in';
                $status                  = 'configured';

                // update config and status:
                $this->repository->setConfiguration($this->job, $config);
                $this->repository->setStatus($this->job, $status);

                return $this->repository->getConfiguration($this->job);
            case 'have-accounts':
                /** @var HaveAccounts $class */
                $class = app(HaveAccounts::class);
                $class->setJob($this->job);
                $data = $class->getData();

                return $data;
            default:
                return [];
        }
    }

    public function getNextView(): string
    {
        $this->checkJob();
        $stage = $this->getConfig()['stage'] ?? 'initial';
        Log::debug(sprintf('in getNextView(), for stage "%s".', $stage));
        switch ($stage) {
            case 'have-logins':
                return 'import.fints.logins';
            default:
                return '';

        }
    }

    public function getWarningMessage(): string
    {
        return $this->warning;
    }

    public function isJobConfigured(): bool
    {
        $this->checkJob();
        $stage = $this->getConfig()['stage'] ?? 'initial';
        Log::debug(sprintf('in isJobConfigured(), for stage "%s".', $stage));
        switch ($stage) {
            default:
                Log::debug('isJobConfigured returns true');
                return true;
        }
    }

    public function setJob(ImportJob $job)
    {
        // make repository
        $this->repository = app(ImportJobRepositoryInterface::class);
        $this->repository->setUser($job->user);

        // set default config:
        $defaultConfig = [
            'stage'           => 'initial',
            'bank'            => [],
            'accounts'        => [],
            'accounts-mapped' => '',
            'auto-start'      => true,
            'apply-rules'     => true,
            'match-bills'     => false,
            'has-config-file' => true,
        ];
        $currentConfig = $this->repository->getConfiguration($job);
        $finalConfig   = array_merge($defaultConfig, $currentConfig);

        // set default extended status:
        $extendedStatus          = $this->repository->getExtendedStatus($job);
        $extendedStatus['steps'] = 6;

        // save to job:
        $job       = $this->repository->setConfiguration($job, $finalConfig);
        $job       = $this->repository->setExtendedStatus($job, $extendedStatus);
        $this->job = $job;

        return;
    }

    /** */
    private function checkJob()
    {
        if (is_null($this->job)) {
            throw new FireflyException('Cannot call configureJob() without a job.');
        }
    }

    /**
     * Shorthand method.
     *
     * @return array
     */
    private function getConfig(): array
    {
        return $this->repository->getConfiguration($this->job);
    }

}