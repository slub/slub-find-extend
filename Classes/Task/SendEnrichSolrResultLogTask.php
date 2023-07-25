<?php
namespace Slub\SlubFindExtend\Task;


/***************************************************************
 *  Copyright notice
 *
 *  (c) 2023 SLUB Dresden
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SendEnrichSolrResultLogTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    /**
     * E-Mails to send log to
     *
     * @var string
     */
    protected $emails;


    public function execute() {

        $successfullyExecuted = true;

        $to = ''; 
        if (strpos($this->getEmails(), ',') !== false) 
        {
            $to = explode(',', $this->getEmails());
        } else {
            $to = $this->getEmails();
        }

        if(file_exists(\TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/EnrichSolrResult.log')) {

            rename(\TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/EnrichSolrResult.log', \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/EnrichSolrResult_process.log');

            $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);
            $mail
                ->setSubject('SLUB Katalog: Enrichment Fehler vom '.date("d.m.Y"))
                ->setFrom(array('noreply@slub-dresden.de' => 'SLUB TYPO3 Server'))
                ->setTo($to)
                ->setBody(file_get_contents(\TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/EnrichSolrResult_process.log'))
                ->send();

            unlink(\TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/EnrichSolrResult_process.log');

        } else {

            $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);
            $mail
                ->setSubject('SLUB Katalog: Enrichment Fehler vom '.date("d.m.Y"))
                ->setFrom(array('noreply@slub-dresden.de' => 'SLUB TYPO3 Server'))
                ->setTo($to)
                ->setBody('Heute keine Fehler .... :)')
                ->send();

        }

        return $successfullyExecuted;
            
    }

    /**
     * Set the value of the emails
     *
     * @param string $emails E-Mails to send log to
     *
     * @return void
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * Get the value of the emails
     *
     * @return string $emails E-Mails to send log to
     */
    public function getEmails()
    {
        return $this->emails;
    }
}