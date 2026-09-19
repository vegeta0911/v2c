<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function v2c_install() {
    // Création de la tâche planifiée de rafraîchissement des données
    $cron = cron::byClassAndFunction('v2c', 'cron');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('v2c');
        $cron->setFunction('cron');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->setComment('Rafraîchissement des données des chargeurs V2C Trydan');
    }
    $cron->setEnable(1);
    $cron->save();

    message::add('v2c', 'Installation du plugin V2C Trydan');
}

function v2c_update() {
    message::add('v2c', 'Mise à jour du plugin V2C Trydan');
}

function v2c_remove() {
    $cron = cron::byClassAndFunction('v2c', 'cron');
    if (is_object($cron)) {
        $cron->remove();
    }

    message::add('v2c', 'Suppression du plugin V2C Trydan');
}
