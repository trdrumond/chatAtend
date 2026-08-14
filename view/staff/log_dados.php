<style>
    #table_1, #table_2 {
        width: 49%;
        float: left;
        margin-left: 10px;
    }

    #table_atendimento {
        font-size: 10px !important;
    }
</style>
<?php
    include("../cnf/session.php");

    //depurador($_POST);
    $diaFiltro = (string) ($_POST['dia'] ?? date('Y-m-d'));
    $userFiltro = (int) ($_POST['user'] ?? 0);

    $queryUser = '';
    $paramsTbl1 = [$diaFiltro];
    if ($userFiltro > 0) {
        $queryUser = ' and user_id=?';
        $paramsTbl1[] = $userFiltro;
    }
    $sql="SELECT a.user_id, concat(b.nome, ' ', b.sobrenome) as nome_usuario, c.nome_fila, date_format(a.data_hora, '%d/%m/%Y %H:%i:%s') as info_hora, a.data_hora, a.acao from tbl_log_atendimento a, tbl_user b, tbl_config_fila c where a.user_id<>1 and a.user_id=b.id_user and a.fila_id=c.id_fila $queryUser and date_format(a.data_hora, '%Y-%m-%d')=? order by a.data_hora asc";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute($paramsTbl1);
    $infoTbl1 = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<div id="bloco_indicadores">

        <div id="table_1">
            <?php if(count($infoTbl1)>0){ ?>
                <center><h3>LOG DE AÇÃO BACKOFFICE</h3></center>
                <table id="table_atendimento" class="table table-hover">
                    <thead>
                        <tr>
                            <th>USUÁRIO</th>
                            <th><center>FILA</center></th>
                            <th><center>DATA<BR>HORA</center></th>
                            <th><center>ACAO</center></th>
                            <th><center>PROTOCOLO</center></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            for($x=0;$x<count($infoTbl1);$x++){
                                $infoTbl1[$x]['nome_usuario'] = ucwords(strtolower($infoTbl1[$x]['nome_usuario']));
                                $infoTbl1[$x]['nome_fila'] = ucwords(strtolower($infoTbl1[$x]['nome_fila']));

                                $infoTrat['protocolo']='';

                                if($infoTbl1[$x]['acao']=='Tratamento' || $infoTbl1[$x]['acao']=='Pos'){
                                    if($infoTbl1[$x]['acao']=='Tratamento'){
                                        $qryCol = 'hora_inicio';
                                    } else {
                                        $qryCol = 'hora_fim';
                                    }
                                    $sql="SELECT protocolo from tbl_chat_fila where {$qryCol}=? and bko_resp=?";
                                    $stmt = $PDO->prepare($sql);
                                    $result = $stmt->execute([$infoTbl1[$x]['data_hora'], (int) $infoTbl1[$x]['user_id']]);
                                    $infoTrat = $stmt->fetch(PDO::FETCH_ASSOC);
                                }


                                echo '<tr>
                                        <td>'.$infoTbl1[$x]['nome_usuario'].'</td>
                                        <td align="center">'.$infoTbl1[$x]['nome_fila'].'</td>
                                        <td align="center">'.$infoTbl1[$x]['info_hora'].'</td>
                                        <td align="center">'.$infoTbl1[$x]['acao'].'</td>
                                        <td align="center">'.$infoTrat['protocolo'].'</td>
                                    </tr>';
                            }

                        ?>
                    </tbody>
                </table>
                <script>
                    $('#table_atendimento').DataTable({
                        dom: 'Bfrtip',
                        "scrollY": "280px",
                        "paging": false,
                        "ordering": false,
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
                        },
                        buttons: {
                            buttons: ['copy', 'excel',
                            ]
                        },
                    });
                </script>
            <?php } else {echo '<BR><BR><BR><BR><BR><BR><BR><BR><center><h3>SEM DADOS PARA O FILTRO ATUAL</h3></center>';} ?>
        </div>



<?php
//$queryUser and date_format(a.data_hora, '%Y-%m-%d')='".$_POST['dia']."'";

    $paramsTbl2 = [$diaFiltro];
    if ($userFiltro > 0) {
        $paramsTbl2[] = $userFiltro;
    }
    $sql="SELECT a.user_id, concat(b.nome, ' ', b.sobrenome) as nome_completo, date_format(a.data_log, '%d/%m/%Y') as data_log, date_format(a.date_in, '%H:%i:%s') as date_in, date_format(a.date_out, '%H:%i:%s') as date_out, ip, date_format(a.date_up, '%H:%i:%s') as date_up, c.nome_nivel from tbl_log_diario a, tbl_user b, tbl_nivel c where a.user_id<>1 and a.user_id=b.id_user and a.nivel_id=c.id_nivel $queryUser and a.data_log=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute($paramsTbl2);
    $infoTbl2 = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

    <div id="table_2">
        <?php if(count($infoTbl2)>0){ ?>
            <center><h3>LOG DE ENTRADA / SAÍDA</h3></center>
            <table id="table_horarios" class="table table-hover">
                <thead>
                    <tr>
                        <th>USUÁRIO</th>
                        <th><center>PERFIL</center></th>
                        <th><center>DATA</center></th>
                        <th><center>HORA IN</center></th>
                        <th><center>HORA OUT<BR>ULT. UP</center></th>
                        <th><center>TEMPO</center></th>
                        <th><center>IP</center></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        for($x=0;$x<count($infoTbl2);$x++){
                            $infoTbl2[$x]['nome_completo'] = ucwords(strtolower($infoTbl2[$x]['nome_completo']));
                            if($infoTbl2[$x]['date_out']==''){
                                $infoTbl2[$x]['date_out']=$infoTbl2[$x]['date_up'];
                            }

                            $time = sec_to_time((time_to_sec($infoTbl2[$x]['date_out']))-(time_to_sec($infoTbl2[$x]['date_in'])));


                            echo '<tr>
                                    <td>'.$infoTbl2[$x]['nome_completo'].'</td>
                                    <td align="center">'.$infoTbl2[$x]['nome_nivel'].'</td>
                                    <td align="center">'.$infoTbl2[$x]['data_log'].'</td>
                                    <td align="center">'.$infoTbl2[$x]['date_in'].'</td>
                                    <td align="center">'.$infoTbl2[$x]['date_out'].'</td>
                                    <td align="center">'.$time.'</td>
                                    <td align="center">'.$infoTbl2[$x]['ip'].'</td>
                                </tr>';
                        }

                    ?>
                </tbody>
            </table>
            <script>
                $('#table_horarios').DataTable({
                    dom: 'Bfrtip',
                    "scrollY": "280px",
                    "paging": false,
                    "ordering": false,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
                    },
                    buttons: {
                        buttons: ['copy', 'excel',
                        ]
                    },
                });
            </script>
        <?php } else {echo '<BR><BR><BR><BR><BR><BR><BR><BR><center><h3>SEM DADOS PARA O FILTRO ATUAL</h3></center>';} ?>
    </div>



</div>



