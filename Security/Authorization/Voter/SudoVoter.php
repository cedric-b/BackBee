<?php

/*
 * Copyright (c) 2011-2015 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 */

namespace BackBee\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use BackBee\BBApplication;

/**
 * @category    BackBee
 *
 * @copyright   Lp digital system
 * @author      Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>, Eric Chau <eric.chau@lp-digital.fr>
 */
class SudoVoter implements VoterInterface
{
    private $application;
    private $sudoers;

    /**
     * @codeCoverageIgnore
     *
     * @param \BackBee\BBApplication $application
     */
    public function __construct(BBApplication $application)
    {
        $this->application = $application;
        $this->sudoers = $this->application->getConfig()->getSecurityConfig('sudoers') ?: array();
    }

    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 0 === preg_match('#^ROLE#', $attribute);
    }

    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'BackBee\Security\Token\BBUserToken'
            || $class === 'BackBee\Security\Token\PublicKeyToken'
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (true === $this->supportsClass(get_class($token))) {
            foreach ($attributes as $attribute) {
                if (false === $this->supportsAttribute($attribute)) {
                    continue;
                }

                if (
                    true === array_key_exists($token->getUser()->getUsername(), $this->sudoers)
                    && $token->getUser()->getId() === $this->sudoers[$token->getUser()->getUsername()]
                ) {
                    $result = VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }
}
