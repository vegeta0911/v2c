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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>
<form class="form-horizontal">
    <fieldset>
        <legend><i class="fas fa-charging-station"></i>{{Configuration générale}}</legend>
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Fréquence de rafraîchissement par défaut}}
                <sup><i class="fas fa-question-circle floatright" title="{{Intervalle en secondes utilisé par défaut pour interroger chaque chargeur, sauf si un intervalle spécifique est défini sur l'équipement.}}"></i></sup>
            </label>
            <div class="col-sm-3">
                <div class="input-group">
                    <input class="configKey form-control roundedLeft" type="number" min="10" data-l1key="refreshInterval" placeholder="60" />
                    <a class="input-group-addon roundedRight">{{secondes}}</a>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Commande status batterie}}
			    <sup><i class="fas fa-question-circle floatright" title="{{Mettre la commande de pourcentage de la batterie de votre voiture électrique.}}"></i></sup>
			</label>
			<div class="col-sm-3">
                <div class="input-group">
			        <input class="configKey form-control paramAttr roundedLeft" data-l1key="reply" placeholder="" />
                    <a class="input-group-addon btn btn-default cursor  roundedRight" title="{{Rechercher une commande}}"><i class="fas fa-list-alt "></i></a>
			    </div>
            </div>
		</div>
       
    </fieldset>
</form>
                  
<script>
    document.querySelector('.input-group-addon.btn').addEventListener('click', function() {
        document.querySelector('.configKey[data-l1key="reply"]').value = '';
        jeedom.cmd.getSelectModal({
        cmd: {
            type: 'info'
        }
        }, function(result) {
        document.querySelector('.configKey[data-l1key="reply"]').insertAtCursor(result.human)
        })
    });    
</script>
