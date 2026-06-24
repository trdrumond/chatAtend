<?php
include("../cnf/session.php");


                            $day = 5;

                            $sql="SELECT id_user, nome_completo, fila_id, nome_fila, qtd, tma, star from rank_atual";
                            if($_POST['fila']!=0){
                                $sql .=" where fila_id=".$_POST['fila'];
                            }
                            $sql .= " order by qtd desc";

                            //echo $sql;
                            $stmt = $PDO->prepare( $sql );
                            $result = $stmt->execute();
                            $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

                        ?>
<center><button id="btn_atualiza_rank_<?=$_POST['fila']?>" title="Atualizar Ranking" class="btn_atualizar"
        onclick="loadRank(<?=$_POST['fila']?>);"> <i class="fas fa-sync-alt"></i> </button></center>

<table class="table table-hover" id="table_rank_<?=$_POST['fila'];?>_<?=time();?>">
    <thead>
        <tr>
            <th>BACKOFFICE</th>
            <th>
                <center>FILA</center>
            </th>
            <th>
                <center>QTD</center>
            </th>
            <th>
                <center>TMA</center>
            </th>
            <th>
                <center><i class="fas fa-star" style="color: #D2D200"></i></center>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
                                    for($x=0;$x<count($dados);$x++){
                                        $ex = explode('.', $dados[$x]['tma']);
                                        $dados[$x]['tma'] = $ex[0];
                                        $dados[$x]['star'] = ($dados[$x]['star']=='' || $dados[$x]['star']=='0.0') ? '-.-' : $dados[$x]['star'];
                                        $dados[$x]['tma'] = ($dados[$x]['tma']=='') ? '--:--:--' : $dados[$x]['tma'];
                                        $dados[$x]['qtd'] = ($dados[$x]['qtd'] == '') ? '--' : $dados[$x]['qtd'];
                                        echo '<tr>';
                                            echo '<td>'.ucwords(strtolower($dados[$x]['nome_completo'])).'</td>';
                                            echo '<td><center>'.ucwords(strtolower($dados[$x]['nome_fila'])).'</center></td>';
                                            echo '<td><center>'.$dados[$x]['qtd'].'</center></td>';
                                            echo '<td><center>'.$dados[$x]['tma'].'</center></td>';
                                            echo '<td><center>'.$dados[$x]['star'].'</center></td>';
                                        echo '</tr>';
                                    }
                                ?>
    </tbody>
</table>
<script>
$(document).ready(function() {
    $('#table_rank_<?=$_POST['fila'];?>_<?=time();?>').DataTable({
        "order": [
            [2, "desc"]
        ],
        //dom: 'Bfrtip',
        //"scrollY": "300px",
        //"scrollX": true,
        //"scrollCollapse": true,
        "searching": false,
        "paging": false,
        //"ordering":         false,
        //language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json' },
        //buttons: { buttons: [ 'excel' ] }
    });


});
</script>
</div>
