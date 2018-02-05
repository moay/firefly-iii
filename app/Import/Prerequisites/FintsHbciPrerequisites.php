<?php
/**
 * FintsHbciPrerequisites.php
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

namespace FireflyIII\Import\Prerequisites;

use FireflyIII\Models\Preference;
use FireflyIII\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Log;
use Preferences;

/**
 * Class FintsHbciPrerequisites
 * @package FireflyIII\Import\Prerequisites
 */
class FintsHbciPrerequisites implements PrerequisitesInterface
{
    /** @var User */
    private $user;

    /**
     * Returns the prerequisites view which currently shows a security warning for the user.
     *
     * @return string
     */
    public function getView(): string
    {
        return 'import.fints.prerequisites';
    }

    /**
     * Returns some parameters for the prerequisites view
     *
     * @return array
     */
    public function getViewParameters(): array
    {
        $subTitle     = strval(trans('import.fints_title'));
        $subTitleIcon = 'fa-archive';
        $hideWarning = !Preferences::getForUser($this->user, 'confirmed-warnings', false);
        $storedBanks = Preferences::getForUser($this->user, 'banks', []);

        return compact('subTitle', 'subTitleIcon', 'hideWarning', 'storedBanks');
    }

    /**
     * Checks for warnings confirmation by user
     *
     * @return bool
     */
    public function hasPrerequisites(): bool
    {
        if($this->user->hasRole('demo')) {
            throw new FireflyException('FinTS/HBCI is not available for demo users.');
        }
        $values = [
            Preferences::getForUser($this->user, 'confirmed-warnings', false),
            Preferences::getForUser($this->user, 'selected-bank', false),
        ];
        /** @var Preference $value */
        foreach ($values as $value) {
            if (false === $value->data || null === $value->data) {
                Log::info(sprintf('Config var "%s" is missing.', $value->name));

                return true;
            }
        }
        Log::debug('All prerequisites are here!');

        return false;
    }

    /**
     * Set the user for this Prerequisites-routine. Class is expected to implement and save this.
     *
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;

        return;
    }

    /**
     * This method responds to the user's submission of an API key. It tries to register this instance as a new Firefly III device.
     * If this fails, the error is returned in a message bag and the user is notified (this is fairly friendly).
     *
     * @param Request $request
     *
     * @return MessageBag
     */
    public function storePrerequisites(Request $request): MessageBag
    {
        return new MessageBag;
    }

}