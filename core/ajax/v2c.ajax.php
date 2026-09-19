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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init('action') == 'testConnection') {
        $ip = init('ip');
        $port = init('port', 80);
        $timeout = init('timeout', 5);

        if ($ip == '') {
            throw new Exception(__('Adresse IP requise', __FILE__));
        }

        $data = v2c::fetchRealTimeData($ip, $port, $timeout);
        $chargeState = (int) ($data['ChargeState'] ?? 0);

        ajax::success(array(
            'id' => $data['ID'] ?? '',
            'firmware_version' => $data['FirmwareVersion'] ?? '',
            'charge_state' => $chargeState,
            'charge_state_label' => v2c::chargeStateLabel($chargeState),
            'charge_power' => round((float) ($data['ChargePower'] ?? 0)),
            'charge_mode_label' => array_key_exists('ChargeMode', $data) ? v2c::chargeModeLabel((int) $data['ChargeMode']) : '',
            'phases_available' => array_key_exists('VoltageMeasure_L1', $data) ? 1 : 0,
        ));
    }

    if (init('action') == 'getStatus') {
        $eqLogic = v2c::byId(init('eqLogic_id'));

        if (!is_object($eqLogic)) {
            throw new Exception(__('Equipement introuvable', __FILE__));
        }

        $cmdFirmware = $eqLogic->getCmd(null, 'firmware_version');
        $cmdChargeStateLabel = $eqLogic->getCmd(null, 'charge_state_label');
        $cmdChargePower = $eqLogic->getCmd(null, 'charge_power');
        $cmdChargeModeLabel = $eqLogic->getCmd(null, 'charge_mode_label');

        ajax::success(array(
            'firmware_version' => is_object($cmdFirmware) ? $cmdFirmware->execCmd() : '',
            'charge_state_label' => is_object($cmdChargeStateLabel) ? $cmdChargeStateLabel->execCmd() : '',
            'charge_power' => is_object($cmdChargePower) ? $cmdChargePower->execCmd() : '',
            'charge_mode_label' => is_object($cmdChargeModeLabel) ? $cmdChargeModeLabel->execCmd() : '',
        ));
    }

    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
