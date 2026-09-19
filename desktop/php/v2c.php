<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('v2c');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="add">
                <i class="fas fa-plus-circle"></i>
                <br>
                <span>{{Ajouter un chargeur}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>
                <span>{{Configuration}}</span>
            </div>
        </div>

        <legend><i class="fas fa-charging-station"></i> {{Mes chargeurs V2C Trydan}}</legend>
        <?php
            if (count($eqLogics) == 0) {
                echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun chargeur V2C Trydan trouvé, cliquez sur "Ajouter" pour commencer}}</div>';
            } else {
                // Champ de recherche
                echo '<div class="input-group" style="margin:5px;">';
                echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
                echo '<div class="input-group-btn">';
                echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
                echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
                echo '</div>';
                echo '</div>';

                // Liste des équipements du plugin
                echo '<div class="eqLogicThumbnailContainer">';
                foreach ($eqLogics as $eqLogic) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                    echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                    echo '<img src="/plugins/v2c/data/images/v2c-trydan.png"/>';
                    echo '<br>';
                    echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                    echo '<span class="hiddenAsCard displayTableRight hidden">';
                    echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
                    echo '</span>';
                    echo '</div>';
                }
                echo '</div>';
            }
        ?>
    </div>

    <div class="col-xs-12 eqLogic" style="display: none;">
        <div class="input-group pull-right" style="display:inline-flex;">
            <span class="input-group-btn">
                <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span></a>
                <a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span></a>
                <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
                <a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
            </span>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-charging-station"></i> {{Chargeur}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="col-lg-6">
                            <legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Nom du chargeur}}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Ex: Chargeur garage}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Objet parent}}</label>
                                <div class="col-sm-6">
                                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                        <option value="">{{Aucun}}</option>
                                        <?php
                                            $options = '';
                                            foreach ((jeeObject::buildTree(null, false)) as $object) {
                                                $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                            }
                                            echo $options;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Catégorie}}</label>
                                <div class="col-sm-6">
                                    <?php
                                        foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                            echo '<label class="checkbox-inline">';
                                            echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
                                            echo '</label>';
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Options}}</label>
                                <div class="col-sm-6">
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
                                </div>
                            </div>

                            <legend><i class="fas fa-network-wired"></i> {{Connexion au chargeur}}</legend>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> {{Renseignez l'adresse IP locale de votre chargeur V2C Trydan (voir votre routeur ou l'application V2C). Aucun compte cloud n'est nécessaire, la communication se fait directement en réseau local.}}
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Adresse IP}}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control" id="in_v2c_ip" data-l1key="configuration" data-l2key="ip" placeholder="{{Ex: 192.168.1.50}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Port}}</label>
                                <div class="col-sm-6">
                                    <input type="number" class="eqLogicAttr form-control" id="in_v2c_port" data-l1key="configuration" data-l2key="port" placeholder="80">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Timeout}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{Délai maximum d'attente de la réponse du chargeur, en secondes}}"></i></sup>
                                </label>
                                <div class="col-sm-6">
                                    <input type="number" class="eqLogicAttr form-control" id="in_v2c_timeout" data-l1key="configuration" data-l2key="timeout" placeholder="5">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Intervalle de rafraîchissement}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{En secondes. Laisser vide ou 0 pour utiliser la valeur par défaut définie dans la configuration du plugin}}"></i></sup>
                                </label>
                                <div class="col-sm-6">
                                    <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="refresh_interval" placeholder="{{Défaut}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-6">
                                    <a class="btn btn-info" id="bt_testConnection"><i class="fas fa-rss"></i> {{Tester la connexion}}</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <legend><i class="fas fa-info"></i> {{Informations}}</legend>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Description / Notes}}</label>
                                <div class="col-sm-6">
                                    <textarea class="form-control eqLogicAttr autogrow" data-l1key="comment" placeholder="{{Notes personnelles sur le chargeur}}"></textarea>
                                </div>
                            </div>
                            <!--div class="form-group" id="div_v2c_test_result">
                                <label class="col-sm-4 control-label">{{Statut du chargeur}}</label>
                                <div class="col-sm-6">
                                    <div class="alert alert-info" style="margin-bottom:0;">
                                        <div><strong>{{Version firmware}}</strong> : <span id="v2c_test_firmware">-</span></div>
                                        <div><strong>{{État recharge}}</strong> : <span id="v2c_test_chargestate">-</span></div>
                                        <div><strong>{{Puissance de charge}}</strong> : <span id="v2c_test_chargepower">-</span> W</div>
                                        <div><strong>{{Mode de charge}}</strong> : <span id="v2c_test_chargemode">-</span></div>
                                        <div><strong>{{Mesures par phase}}</strong> : <span id="v2c_test_phases">-</span></div>
                                    </div>
                                </div>
                            </div-->
                        </div>
                    </fieldset>
                </form>
            </div>
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <br>
                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
                                <th style="min-width:200px;width:350px;">{{Nom}}</th>
                                <th>{{Type}}</th>
                                <th style="min-width:260px;">{{Options}}</th>
                                <th>{{Etat}}</th>
                                <th style="min-width:80px;width:200px;">{{Actions}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_file('desktop', 'v2c', 'js', 'v2c'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
