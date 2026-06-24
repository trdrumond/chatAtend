<?php
//echo "<br>func_input";

/**
 * @param string $desc_campo
 * @param string $nome_campo
 * @param string $tipo_campo
 * @param int|string $obg
 * @param int|string $chatId
 * @return void
 */
function inputText($desc_campo, $nome_campo, $tipo_campo, $obg, $chatId){
    if($tipo_campo=='date'){$value=date('Y-m-d');} else {$value='';}
    if($obg==1){$flagObg='<strong>* </strong>';} else {$flagObg='';}
    $maxlength = '';
    echo "<script>";
    $nome_campo = $nome_campo . '_' .$chatId;


            if($tipo_campo=="contato"){
                $tipo_campo='text';
                $maxlength=' maxlength="15"';
                echo "$(document).ready(function () {";
                echo "id('".$nome_campo."').onkeyup = function() {
                    mascara(this, mtel);
                }";
                echo "});";
            }


            if($tipo_campo=="cep"){
                $tipo_campo='text';
                $maxlength=' maxlength="9"';
                echo "$(document).ready(function () {";
                echo "id('".$nome_campo."').onkeyup = function() {
                    mascara(this, cep);
                }";
                echo "});";
            }
            if($tipo_campo=="valor"){
                $tipo_campo='text';
                echo "$(document).ready(function () {";
                echo "id('".$nome_campo."').onkeyup = function() {
                    mascara(this, money);
                }";
                echo "});";
            }
            if($tipo_campo=="numero"){
                echo "$(document).ready(function () {";
                echo "id('".$nome_campo."').onkeyup = function() {
                    mascara(this, mNumero);
                }";
                echo "});";
            }
    echo "</script>";
        if($tipo_campo=="cpf"){
            $tipo_campo='text';
            ?>
            <script>
                $(document).ready(function(){
                    $('#<?=$nome_campo?>').mask('000.000.000-00', {
                        onKeyPress : function(cpfcnpj, e, field, options) {
                            const masks = ['000.000.000-000', '00.000.000/0000-00'];
                            const mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
                            $('#<?=$nome_campo?>').mask(mask, options);
                        }
                    });
                });
            </script>
            <?php

        }


    $req = ($obg == 1) ? ' required' : '';
    echo '<div class="st-field input-container">';
    echo '<label class="st-label" for="'.$nome_campo.'">'.$flagObg.''.htmlspecialchars($desc_campo).'</label>';
    echo '<input type="'.$tipo_campo.'" id="'.$nome_campo.'" name="'.$nome_campo.'" class="input form-control"'
        .$maxlength.$req.' value="'.htmlspecialchars($value).'"/>';
    echo '</div>';
}



/**
 * @param string $desc_campo
 * @param string $nome_campo
 * @param array<int, array<string, mixed>> $options
 * @param int|string $obg
 * @param int|string $chatId
 * @return void
 */
function inputSelect($desc_campo, $nome_campo, $options, $obg, $chatId){
    if($obg==1){$flagObg='<strong>* </strong>';} else {$flagObg='';}
    $nome_campo = $nome_campo . '_' .$chatId;
    $req = ($obg == 1) ? ' required' : '';
    echo '<div class="st-field input-container">';
    echo '<label class="st-label" for="'.$nome_campo.'">'.$flagObg.''.htmlspecialchars($desc_campo).'</label>';
    echo '<select id="'.$nome_campo.'" name="'.$nome_campo.'" class="form-control"'.$req.'>';
    echo '<option value="">Selecione...</option>';
    for ($y = 0; $y < count($options); $y++) {
        echo '<option value="'.htmlspecialchars($options[$y]['desc_option']).'">'
            .htmlspecialchars($options[$y]['desc_option']).'</option>';
    }
    echo '</select>';
    echo '</div>';
}

/**
 * @param string $desc_campo
 * @param string $nome_campo
 * @param string $opc_1
 * @param int|string $id_opc_1
 * @param string $value_1
 * @param string $opc_2
 * @param int|string $id_opc_2
 * @param string $value_2
 * @param int|string $obg
 * @param int|string $chatId
 * @return void
 */
function inputCheckbox($desc_campo, $nome_campo, $opc_1, $id_opc_1, $value_1, $opc_2, $id_opc_2, $value_2, $obg, $chatId){
    if($obg==1){$flagObg='<strong>* </strong>';} else {$flagObg='';}
    $nome_campo = $nome_campo . '_' .$chatId;
    echo '<div class="st-field st-field--choice cnf-field-full">';
    echo '<span class="st-label">'.$flagObg.''.htmlspecialchars($desc_campo).'</span>';
    echo '<div class="st-radio-group st-radio-group--pair">';
    echo '<label class="st-radio-option" for="opt_1_'.$id_opc_1.'_'.$chatId.'">';
    echo '<input type="radio" name="'.$nome_campo.'" id="opt_1_'.$id_opc_1.'_'.$chatId.'" class="form-check-input" value="'.htmlspecialchars($value_1).'">';
    echo '<span>'.htmlspecialchars($opc_1).'</span>';
    echo '</label>';
    echo '<label class="st-radio-option" for="opt_2_'.$id_opc_2.'_'.$chatId.'">';
    echo '<input type="radio" name="'.$nome_campo.'" id="opt_2_'.$id_opc_2.'_'.$chatId.'" class="form-check-input" value="'.htmlspecialchars($value_2).'">';
    echo '<span>'.htmlspecialchars($opc_2).'</span>';
    echo '</label>';
    echo '</div>';
    echo '</div>';
}


/**
 * @param string $desc_campo
 * @param string $nome_campo
 * @param string $tipo_campo
 * @param int|string $obg
 * @param int|string $chatId
 * @return void
 */
function inputTextMon($desc_campo, $nome_campo, $tipo_campo, $obg, $chatId){
    if($tipo_campo=='date'){$value=date('Y-m-d');} else {$value='';}
    if($obg==1){$flagObg='<strong>* </strong>';} else {$flagObg='';}
    $nome_campo = $nome_campo . '_' .$chatId;
    echo "<script>";




            if($tipo_campo=="numero"){
                echo "$(document).ready(function () {";
                echo "id('".$nome_campo."').onkeyup = function() {
                    mascara(this, mNumero);
                }";
                echo "});";
            }


    echo "</script>";
        if($tipo_campo=="cpf"){
            $tipo_campo='text';
            ?>
            <script>
                $(document).ready(function(){
                    $('#<?=$nome_campo?>').mask('000.000.000-00', {
                        onKeyPress : function(cpfcnpj, e, field, options) {
                            const masks = ['000.000.000-000', '00.000.000/0000-00'];
                            const mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
                            $('#<?=$nome_campo?>').mask(mask, options);
                        }
                    });
                });
            </script>
            <?php

        }


    echo '<div class="content-10-input">
                <div class="input-container">
                    <input type="'.$tipo_campo.'" id="'.$nome_campo.'" name="'.$nome_campo.'" class="input"  pattern=".+" value="'.$value.'"/>
                    <label for="'.$nome_campo.'">'.$flagObg.''.$desc_campo.'</label>
                </div>
            </div>';
}



/**
 * @param string $desc_campo
 * @param string $nome_campo
 * @param array<int, array<string, mixed>> $options
 * @param int|string $obg
 * @param int|string $chatId
 * @return void
 */
function inputSelectMon($desc_campo, $nome_campo, $options, $obg, $chatId){
    if($obg==1){$flagObg='<strong>* </strong>';} else {$flagObg='';}
    $nome_campo = $nome_campo . '_' .$chatId;
        echo '<div class="content-10-input">
            <div class="input-container">
                <select id="'.$nome_campo.'">
                    <option value="">'.$flagObg.''.$desc_campo.'</option>';
                    for($y=0;$y<count($options);$y++){
                        echo '<option value="'.$options[$y]['desc_option'].'">'.$options[$y]['desc_option'].'</option>';
                    }
                echo '
                </select>
            </div>
          </div>';
}

/**
 * @param string $desc_campo
 * @param string $nome_campo
 * @param string $opc_1
 * @param int|string $id_opc_1
 * @param string $value_1
 * @param string $opc_2
 * @param int|string $id_opc_2
 * @param string $value_2
 * @param int|string $obg
 * @param int|string $chatId
 * @return void
 */
function inputCheckboxMon($desc_campo, $nome_campo, $opc_1, $id_opc_1, $value_1, $opc_2, $id_opc_2, $value_2, $obg, $chatId){
    if($obg==1){$flagObg='<strong>* </strong>';} else {$flagObg='';}
    $nome_campo = $nome_campo . '_' .$chatId;
    //echo "<br>".$chatId."<br>";
    echo '<div class="content-10-input option">
            <label class="label_option">'.$flagObg.''.$desc_campo.'</label>
            <div class="input-container">
                <div id="div_opt_1_'.$chatId.'" class="opcao">
                    <input type="radio" name="'.$nome_campo.'" id="opt_1_'.$id_opc_1.'_'.$chatId.'" class="radio-btn-default" value="'.$value_1.'">
                    <label for="opt_1_'.$id_opc_1.'_'.$chatId.'"> '.$opc_1.' </label>
                </div>
                <div id="div_opt_2_'.$chatId.'" class="opcao">
                    <input type="radio" name="'.$nome_campo.'" id="opt_2_'.$id_opc_2.'_'.$chatId.'" class="radio-btn-default" value="'.$value_2.'">
                    <label for="opt_2_'.$id_opc_2.'_'.$chatId.'"> '.$opc_2.' </label>
                </div>
            </div>
        </div>';
}

?>
