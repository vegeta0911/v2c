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

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'
  tr += '</td>'
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  tr.setValues(_cmd, '.cmdAttr')
  jeedom.cmd.changeType(tr, init(_cmd.subType))
}

/**
 * Test de connexion au chargeur V2C Trydan depuis l'écran d'édition de l'équipement.
 */
$('#bt_testConnection').off('click').on('click', function () {
  var ip = $('#in_v2c_ip').val()
  var port = $('#in_v2c_port').val()
  var timeout = $('#in_v2c_timeout').val()

  if (ip == '') {
    $('#div_alert').showAlert({
      message: '{{Veuillez renseigner l\'adresse IP du chargeur}}',
      level: 'warning'
    })
    return
  }

  $('#div_alert').showAlert({
    message: '{{Test de connexion en cours...}}',
    level: 'info'
  })

  $.ajax({
    type: 'POST',
    url: 'plugins/v2c/core/ajax/v2c.ajax.php',
    dataType: 'json',
    data: {
      action: 'testConnection',
      ip: ip,
      port: port,
      timeout: timeout
    },

    success: function (data) {
      if (data.state == 'ok') {
        $('#v2c_test_firmware').text(data.result.firmware_version)
        $('#v2c_test_chargestate').text(data.result.charge_state_label)
        $('#v2c_test_chargepower').text(data.result.charge_power)
        $('#v2c_test_chargemode').text(data.result.charge_mode_label != '' ? data.result.charge_mode_label : '-')
        $('#v2c_test_phases').text(data.result.phases_available == 1 ? '{{Disponibles (firmware >= 2.5.0)}}' : '{{Non disponibles sur ce firmware}}')

        $('#div_alert').showAlert({
          message: '{{Connexion réussie}}',
          level: 'success'
        })
      } else {
        $('#div_alert').showAlert({
          message: data.result,
          level: 'danger'
        })
      }
    },

    error: function (request) {
      $('#div_alert').showAlert({
        message: request.responseText,
        level: 'danger'
      })
    }
  })
})

/**
 * Appelé par le Core de Jeedom à l'ouverture d'un équipement : affiche le dernier
 * statut connu (firmware, état de charge, puissance) sans interroger le chargeur.
 */
/*function printEqLogic(_eqLogic) {
  $('#v2c_test_firmware').text('-')
  $('#v2c_test_chargestate').text('-')
  $('#v2c_test_chargepower').text('-')
  $('#v2c_test_chargemode').text('-')
  $('#v2c_test_phases').text('-')

  if (!is_numeric(_eqLogic.id)) {
    return
  }

  $.ajax({
    type: 'POST',
    url: 'plugins/v2c/core/ajax/v2c.ajax.php',
    dataType: 'json',
    data: {
      action: 'getStatus',
      eqLogic_id: _eqLogic.id
    },

    success: function (data) {
      if (data.state == 'ok') {
        $('#v2c_test_firmware').text(data.result.firmware_version != '' ? data.result.firmware_version : '-')
        $('#v2c_test_chargestate').text(data.result.charge_state_label != '' ? data.result.charge_state_label : '-')
        $('#v2c_test_chargepower').text(data.result.charge_power !== '' ? data.result.charge_power : '-')
        $('#v2c_test_chargemode').text(data.result.charge_mode_label != '' ? data.result.charge_mode_label : '-')
      }
    }
  })
}*/
