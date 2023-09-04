<?php
namespace Slub\SlubFindExtend\Task;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Alexander Bigga <alexander.bigga@slub-dresden.de>, SLUB Dresden
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
 ***************************************************************/

/**
 *
 *
 * @package slub_find_extend
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

class SendEnrichSolrResultLogTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{

    /**
     * Render additional information fields within the scheduler backend.
     *
     * @param array                                                     $taskInfo        Array information of task to return
     * @param CleanUpTask                                            $task            Task object
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the BE module of the Scheduler
     *
     * @return array Additional fields
     * @see \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface->getAdditionalFields($taskInfo, $task, $schedulerModule)
     */
    public function getAdditionalFields(
        array &$taskInfo,
        $task,
        \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
    ) {
        $additionalFields = [];

        if (empty($taskInfo['emails'])) {
            if ($schedulerModule->getCurrentAction()->equals(Action::ADD)) {
                $taskInfo['emails'] = '';
            } elseif ($schedulerModule->getCurrentAction()->equals(Action::EDIT)) {
                $taskInfo['emails'] = $task->getEmails();
            } else {
                $taskInfo['emails'] = $task->setEmails();
            }
        }

        $fieldId = 'task_emails';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[slub_find_extend][emails]" id="' . $fieldId . '" value="' . htmlspecialchars($taskInfo['emails']) . '"/>';
        $label = $GLOBALS['LANG']->sL('LLL:EXT:slub_find_extend/Resources/Private/Language/locallang_be.xlf:tasks.enricherrorlog.emails');
        $additionalFields[$fieldId] = [
            'code'  => $fieldCode,
            'label' => $label
        ];

        return $additionalFields;
    }

    /**
     * This method checks any additional data that is relevant to the specific task.
     * If the task class is not relevant, the method is expected to return TRUE.
     *
     * @param array                                                     $submittedData   Reference to the array containing the data submitted by the user
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the BE module of the Scheduler
     *
     * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(
        array &$submittedData,
        \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule
    ) {

        if(strlen($submittedData['slub_find_extend']['emails']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches.
     *
     * @param array                                  $submittedData Array containing the data submitted by the user
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task          Reference to the current task object
     *
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        /** @var $task CleanUpTask */
        $task->setEmails($submittedData['slub_find_extend']['emails']);
    }
}
