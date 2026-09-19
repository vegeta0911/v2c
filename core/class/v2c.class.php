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

require_once __DIR__ . '/../../../../core/php/core.inc.php';

class v2c extends eqLogic {

    public static $_widgetPossibility = array('custom' => true);

    /*
     * Appelé toutes les minutes par le cron du plugin (voir plugin_info/install.php).
     * Chaque équipement n'est réellement interrogé que si son propre intervalle de
     * rafraîchissement est écoulé, afin de limiter la charge sur le chargeur.
     */
    public static function cron() {
        foreach (self::byType('v2c', true) as $eqLogic) {
            if ($eqLogic->getIsEnable() != 1) {
                continue;
            }

            $interval = (int) $eqLogic->getConfiguration('refresh_interval', 0);
            if ($interval <= 0) {
                $interval = (int) config::byKey('refreshInterval', 'v2c', 60);
            }
            if ($interval < 10) {
                $interval = 10;
            }

            $cacheKey = 'v2cLastRefresh' . $eqLogic->getId();
            $last = (int) cache::byKey($cacheKey)->getValue(0);

            if ((time() - $last) < $interval) {
                continue;
            }

            try {
                $eqLogic->refreshInfo();

            } catch (Exception $e) {
                log::add('v2c', 'warning', $eqLogic->getHumanName() . ' : ' . $e->getMessage());
            }

            cache::set($cacheKey, time());
        }
    }

    public static function cron5() {
        foreach (self::byType('v2c', true) as $eqLogic) {
            if ($eqLogic->getIsEnable() != 1) {
                continue;
            }

            $statecable = $eqLogic->getCmd('info', 'charge_state_label')->execCmd();
            $batterieId = config::byKey('reply', 'v2c', '');
            $batterieId = preg_replace('/[^0-9]/', '', $batterieId);
        
            if (!empty($batterieId)) {
                $cmd = cmd::byId($batterieId);

                if (is_object($cmd) && ($cmd instanceof cmd)) {
        
                    $valeur = $cmd->execCmd();
                        
                } else {
                    log::add('v2c', 'warning', 'Le plugin v2c cherche l ID ' . $batterieId . ' mais cette commande n existe pas dans Jeedom.');
                }
            } else {
                log::add('v2c', 'warning', 'Le plugin v2c n a pas de commande de porcentage batterie (ID vide).');
            }

            if ($statecable == 'Branché - en charge' && $valeur != '') {
                if ($valeur >= $eqLogic->getConfiguration('charge_limited_power', 0)) {
                    $eqLogic->getCmd('action', 'pause_charge')->execCmd();
                }
                log::add('v2c', 'info', 'Le plugin v2c a détecté que le chargeur est branché et en charge, mais la batterie est pleine. La charge a été mise en pause.');
            }
        }
    }

    /*
     * Interroge le chargeur et met à jour les commandes de cet équipement.
     */
    public function refreshInfo() {
    
        $ip = trim((string) $this->getConfiguration('ip'));

        if ($ip == '') {
            $this->checkAndUpdateCmd('connectivity', 0);
            throw new Exception(__('Adresse IP du chargeur non configurée', __FILE__));
        }

        $port = (int) $this->getConfiguration('port', 80);
        $timeout = (int) $this->getConfiguration('timeout', 5);

        try {
            $data = self::fetchRealTimeData($ip, $port, $timeout);
        } catch (Exception $e) {
            $this->checkAndUpdateCmd('connectivity', 0);
            throw $e;
        }

        $this->checkAndUpdateCmd('connectivity', 1);
        $this->updateFromData($data);
    }

    /*
     * Appelle l'API HTTP locale du chargeur V2C Trydan (GET /RealTimeData) et retourne
     * les données décodées. Ne modifie aucun équipement : utilisée aussi bien par
     * refreshInfo() que par le test de connexion depuis l'interface d'administration.
     */
    public static function fetchRealTimeData($ip, $port = 80, $timeout = 5) {
        $port = (int) $port;
        if ($port <= 0) {
            $port = 80;
        }
        $timeout = (int) $timeout;
        if ($timeout <= 0) {
            $timeout = 5;
        }
        if ($timeout > 10) {
            $timeout = 10;
        }

        $url = 'http://' . $ip . ':' . $port . '/RealTimeData';

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPGET => true,
            CURLOPT_NOSIGNAL => true,
        ));
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $raw === false) {
            throw new Exception(__('Impossible de contacter le chargeur V2C Trydan : ', __FILE__) . $error);
        }

        if ($httpCode != 200) {
            throw new Exception(__('Le chargeur V2C Trydan a répondu avec le code HTTP ', __FILE__) . $httpCode);
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $data = self::repairJson($raw);
        }

        if (!is_array($data)) {
            throw new Exception(__('Réponse JSON invalide reçue du chargeur V2C Trydan', __FILE__));
        }
        
        log::add('v2c', 'info', 'DATA: ' .print_r($raw,true));
        return $data;
    }

    /*
     * Certaines versions de firmware du Trydan renvoient un JSON légèrement
     * malformé (virgule manquante entre deux champs). On tente une réparation
     * générique avant d'abandonner.
     */
    private static function repairJson($json) {
        if (!is_string($json) || $json == '') {
            return null;
        }

        $fixed = preg_replace('/("|\d|true|false)(\s*)("(?:[A-Za-z_]+)"\s*:)/', '$1,$3', $json);
        $fixed = preg_replace('/,\s*,/', ',', $fixed);

        $data = json_decode($fixed, true);

        return is_array($data) ? $data : null;
    }

    private function updateFromData($data) {
      
      $cmd = $this->getCmd('info');
      
        $chargeState = (int) ($data['ChargeState'] ?? 0);
        $chargeTime = (int) ($data['ChargeTime'] ?? 0);

        $this->checkAndUpdateCmd('charge_state', $chargeState);
        $this->checkAndUpdateCmd('charge_state_label', self::chargeStateLabel($chargeState));
        $this->checkAndUpdateCmd('ready_state', (int) ($data['ReadyState'] ?? 0));
        $this->checkAndUpdateCmd('paused', !empty($data['Paused']) ? 1 : 0);
        $this->checkAndUpdateCmd('locked', !empty($data['Locked']) ? 1 : 0);

        $this->checkAndUpdateCmd('charge_power', round((float) ($data['ChargePower'] ?? 0)));
        $this->checkAndUpdateCmd('charge_energy', round((float) ($data['ChargeEnergy'] ?? 0), 2));
        $this->checkAndUpdateCmd('charge_time', self::formatDuration($chargeTime));
        $this->checkAndUpdateCmd('intensity', (float) ($data['Intensity'] ?? 0));
        //$this->checkAndUpdateCmd('charge_limited_power', (float) ('set_charge_limited_power' ?? 0));
        $this->checkAndUpdateCmd('min_intensity', (float) ($data['MinIntensity'] ?? 0));
        $this->checkAndUpdateCmd('max_intensity', (float) ($data['MaxIntensity'] ?? 0));
        $this->checkAndUpdateCmd('voltage_installation', round((float) ($data['VoltageInstallation'] ?? 0)));
        $this->checkAndUpdateCmd('contracted_power', round((float) ($data['ContractedPower'] ?? 0)));
        $this->checkAndUpdateCmd('charge_limited_power', (float) ($data['ChargeLimitedPower'] ?? $this->getConfiguration('charge_limited_power', 0)));

        // Mesures par phase : présentes uniquement à partir du firmware 2.5.0.
        // On ne met à jour les commandes que si le chargeur renvoie réellement ces
        // champs, afin que la valeur reste vide (et non 0) sur les firmwares plus anciens.
        foreach (array('L1' => 'l1', 'L2' => 'l2', 'L3' => 'l3') as $suffix => $id) {
            if (array_key_exists('IntensityMeasure_' . $suffix, $data)) {
                $this->checkAndUpdateCmd('intensity_' . $id, round((float) $data['IntensityMeasure_' . $suffix], 2));
            }
            if (array_key_exists('VoltageMeasure_' . $suffix, $data)) {
                $this->checkAndUpdateCmd('voltage_' . $id, round((float) $data['VoltageMeasure_' . $suffix]));
            }
        }

        if (array_key_exists('ChargeMode', $data)) {
            $chargeMode = (int) $data['ChargeMode'];
            $this->checkAndUpdateCmd('charge_mode', $chargeMode);
            $this->checkAndUpdateCmd('charge_mode_label', self::chargeModeLabel($chargeMode));
        }

        $this->checkAndUpdateCmd('house_power', round((float) ($data['HousePower'] ?? 0)));
        $this->checkAndUpdateCmd('fv_power', round((float) ($data['FVPower'] ?? 0)));
        $this->checkAndUpdateCmd('battery_power', round((float) ($data['BatteryPower'] ?? 0)));

        $this->checkAndUpdateCmd('dynamic', !empty($data['Dynamic']) ? 1 : 0);
        $this->checkAndUpdateCmd('dynamic_power_mode', (string) ($data['DynamicPowerMode'] ?? ''));
        $this->checkAndUpdateCmd('pause_dynamic', !empty($data['PauseDynamic']) ? 1 : 0);
        $this->checkAndUpdateCmd('timer', (string) ($data['Timer'] ?? ''));
        
        $this->checkAndUpdateCmd('slave_error', (string) ($data['SlaveError'] ?? ''));
        $this->checkAndUpdateCmd('firmware_version', (string) ($data['FirmwareVersion'] ?? ''));
        $this->checkAndUpdateCmd('signal_status', (string) ($data['SignalStatus'] ?? ''));
        $this->checkAndUpdateCmd('ssid', (string) ($data['SSID'] ?? ''));
        $this->checkAndUpdateCmd('device_ip', (string) ($data['IP'] ?? ''));
        $this->checkAndUpdateCmd('device_id', (string) ($data['ID'] ?? ''));
    }

    public static function chargeStateLabel($state) {
        switch ($state) {
            case 0:
                return __('Câble non branché', __FILE__);
            case 1:
                return __('Branché - en attente', __FILE__);
            case 2:
                return __('Branché - en charge', __FILE__);
            default:
                return __('Inconnu', __FILE__);
        }
    }

    /*
     * Construit les <option> d'un <select> HTML à partir de la configuration
     * 'listValue' d'une commande action/select (format "valeur|libellé;valeur|libellé"),
     * en marquant comme sélectionnée l'option correspondant à $selected.
     */
    private static function buildSelectOptions($cmd, $selected) {
        if (!is_object($cmd)) {
            return '';
        }

        $listValue = (string) $cmd->getConfiguration('listValue', '');
        $html = '';

        foreach (explode(';', $listValue) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }

            list($value, $label) = array_pad(explode('|', $pair, 2), 2, '');
            $selectedAttr = ((string) $value === (string) $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>' . htmlspecialchars($label) . '</option>';
        }

        return $html;
    }

    public static function chargeModeLabel($mode) {
        switch ((int) $mode) {
            case 0:
                return __('Monophasé', __FILE__);
            case 1:
                return __('Triphasé', __FILE__);
            case 2:
                return __('Mixte (triphasé + monophasé)', __FILE__);
            default:
                return __('Inconnu', __FILE__);
        }
    }

    private static function formatDuration($seconds) {
        $seconds = max(0, (int) $seconds);
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    public function postInsert() {
        $this->createCommands();
    }

    public function postSave() {
        $this->createCommands();
    }

    public function preRemove() {
        cache::delete('v2cLastRefresh' . $this->getId());
    }

    public function createCommands() {
        $definitions = require __DIR__ . '/../config/commands.php';

        foreach ($definitions as $definition) {
            $cmd = $this->getCmd(null, $definition['logicalId']);

            if (!is_object($cmd)) {
                $cmd = new v2cCmd();
                $cmd->setEqLogic_id($this->getId());
                $cmd->setLogicalId($definition['logicalId']);
            }

            $cmd->setName(__($definition['name'], __FILE__));
            $cmd->setType($definition['type']);
            $cmd->setSubType($definition['subType']);

            if (isset($definition['unit'])) {
                $cmd->setUnite($definition['unit']);
            }

            if (isset($definition['configuration']) && is_array($definition['configuration'])) {
                foreach ($definition['configuration'] as $confKey => $confValue) {
                    $cmd->setConfiguration($confKey, $confValue);
                }
            }

            $cmd->save();
        }
    }

    public function toHtml($_version = 'dashboard') {
        $this->emptyCacheWidget();

        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }

        $version = jeedom::versionAlias($_version);
        $replace['#version#'] = $_version;

        foreach ($this->getCmd('info') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            $replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
            $replace['#' . $cmd->getLogicalId() . '_unite#'] = $cmd->getUnite();
            $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        }

        foreach ($this->getCmd('action') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        }

        // Génère les <option> du sélecteur "Mode de charge" directement à partir de la
        // configuration (listValue) de la commande set_charge_mode, plutôt que de les
        // dupliquer en dur dans le template HTML.
        $cmdSetChargeMode = $this->getCmd(null, 'set_charge_mode');
        $replace['#charge_mode_options#'] = self::buildSelectOptions($cmdSetChargeMode, $replace['#charge_mode#'] ?? '');

        $template = 'v2c_dashboard';
        $replace['#template#'] = $template;
        $replace['#css_version#'] = (int) @filemtime(__DIR__ . '/../template/dashboard/v2c_dashboard.css');

        return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $template, 'v2c')));
    }

    /*
     * Envoie une valeur au chargeur (GET /write/<Clé>=<valeur>) puis rafraîchit
     * immédiatement l'état de l'équipement pour refléter le résultat.
     */
    public function writeValue($key, $value) {
        $ip = trim((string) $this->getConfiguration('ip'));

        if ($ip == '') {
            throw new Exception(__('Adresse IP du chargeur non configurée', __FILE__));
        }
        
        // Si la clé est "ChargeLimitedPower", stocker la valeur localement
    if ($key === 'ChargeLimitedPower') {
        $this->setConfiguration('charge_limited_power', $value);
        $this->save();
    }

        $port = (int) $this->getConfiguration('port', 80);
        $timeout = (int) $this->getConfiguration('timeout', 5);

        $result = self::writeRealTimeData($ip, $key, $value, $port, $timeout);

        try {
            $this->refreshInfo();
        } catch (Exception $e) {
            log::add('v2c', 'warning', $e->getMessage());
        }

        return $result;
    }

    /*
     * Appelle l'API HTTP locale du chargeur V2C Trydan (GET /write/<Clé>=<valeur>).
     */
    public static function writeRealTimeData($ip, $key, $value, $port = 80, $timeout = 5) {
        $port = (int) $port;
        if ($port <= 0) {
            $port = 80;
        }
        $timeout = (int) $timeout;
        if ($timeout <= 0) {
            $timeout = 5;
        }
        if ($timeout > 10) {
            $timeout = 10;
        }

        $url = 'http://' . $ip . ':' . $port . '/write/' . rawurlencode($key) . '=' . rawurlencode((string) $value);

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPGET => true,
            CURLOPT_NOSIGNAL => true,
        ));
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $raw === false) {
            throw new Exception(__('Impossible de contacter le chargeur V2C Trydan : ', __FILE__) . $error);
        }

        if ($httpCode != 200) {
            throw new Exception(__('Le chargeur V2C Trydan a répondu avec le code HTTP ', __FILE__) . $httpCode);
        }

        if (trim(strtoupper((string) $raw)) === 'ERROR') {
            throw new Exception(__('Le chargeur V2C Trydan a refusé la valeur envoyée pour ', __FILE__) . $key);
        }

        return trim((string) $raw);
    }
}

class v2cCmd extends cmd {
    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();
        //log::add('v2c', 'debug', 'Exécution de la commande ' . print_r($eqLogic->getCmd(), true));
        
        switch ($this->getLogicalId()) {
            case 'refresh':
                $eqLogic->refreshInfo();
                return __('Données actualisées', __FILE__);

            case 'pause_charge':
                return $eqLogic->writeValue('Paused', 1);

            case 'resume_charge':
                return $eqLogic->writeValue('Paused', 0);

            case 'lock_charger':
                return $eqLogic->writeValue('Locked', 1);

            case 'unlock_charger':
                return $eqLogic->writeValue('Locked', 0);

            case 'enable_dynamic':
                return $eqLogic->writeValue('Dynamic', 1);

            case 'disable_dynamic':
                return $eqLogic->writeValue('Dynamic', 0);

            case 'set_intensity':
                return $eqLogic->writeValue('Intensity', (int) ($_options['slider'] ?? 0));

            case 'set_charge_limited_power':
                return $eqLogic->writeValue('ChargeLimitedPower', (int) ($_options['slider'] ?? 0));

            case 'set_min_intensity':
                return $eqLogic->writeValue('MinIntensity', (int) ($_options['slider'] ?? 0));

            case 'set_max_intensity':
                return $eqLogic->writeValue('MaxIntensity', (int) ($_options['slider'] ?? 0));

            case 'set_dynamic_power_mode':
                return $eqLogic->writeValue('DynamicPowerMode', (int) ($_options['select'] ?? 0));

            case 'set_charge_mode':
                return $eqLogic->writeValue('ChargeMode', (int) ($_options['select'] ?? 0));
        }

        return;
    }
}