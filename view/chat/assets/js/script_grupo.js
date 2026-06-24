

            conn.onmessage = function (e) {
                data = JSON.parse(e.data);
                //console.log(data);
                if(data.count!=''){
                    var ddCount = data.count;
                    //console.log(ddCount);
                    setTimeout(function(){
                        $('#dadosLogados').html(ddCount);
                        //console.log(ddCount);
                    }, 500);
                }

                if(data.flag == 'msg_group'){
                    showMessages_group('other', e.data);
                }

            };

            var form = document.getElementById('form_grupo');
            var inp_message = document.getElementById('message_grupo');
            var inp_name = document.getElementById('name_grupo');
            var userRemetente = document.getElementById('id_user_remetente_grupo');
            var inp_img = document.getElementById('img_grupo');
            var btn_env = document.getElementById('btn_send_grupo');
            var area_content = document.getElementById('chat-content_grupo');
            $(area_content).animate({scrollTop: 100000}, 'slow');


            //$(document).keypress(function(e) {
                //if(e.which == 13) btn_env.click();
            //});




            function saveMsgGroup(msg, rem, com, nome, img, tk){
                //console.log('saveMsgGroup');
                //console.log(msg);

                if (msg != '') {
                    var feed = '#feed';

                    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                    $.post("staff/save_msg_group.php",
                    {
                        msg, rem, com, nome, img, tk
                    },
                    function (valor) {
                        $(feed).html(valor);
                    });
                }
            }



            function sendMessageGroup(msg, rem, com, nome, img, tk){
                if (msg != '') {
                    var data = new Date();
                    var dia     = data.getDate();
                    var mes     = data.getMonth();
                    var ano4    = data.getFullYear();
                    var hora    = data.getHours();
                    var min     = data.getMinutes();
                    var seg     = data.getSeconds();
                    mes =(mes+1);
                    if(mes<10){mes='0'+mes;}
                    var str_hora = hora + ':' + min;
                    var str_hora_sql = hora + ':' + min + ':' + seg;
                    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
                    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;

                    var msg = {
                        'flag' : 'msg_group',
                        'userRemetente': rem,
                        'name': nome,
                        'msg': msg,
                        'img': img,
                        'dataHora': str_data
                    };
                    //, 'img': inp_img.value
                    msg = JSON.stringify(msg);
                    conn.send(msg);
                    showMessages_group('me', msg);
                    $('#message_grupo_' + tk).val('');

                }
            }


            function showMessages_group(how, data) {
                var chatId='grupo';
                if($('#chat-content_' + chatId).length){
                    var div_area = '#chat-content_' + chatId;
                    var chat_content_txt = 'chat-content_' + chatId;
                    var area_content = document.getElementById(chat_content_txt);

                    var elMsg = nome_div();

                    //console.log(elMsg);

                    data = JSON.parse(data);
                    //console.log(data);
                    //console.log(chat_id);

                    var load = '';

                    //$('#dig_' + chatId).html('');

                    //newMessage();

                    if(how !=='sys'){
                        var div = document.createElement('div');
                        div.setAttribute('class', how);

                        var img = document.createElement('img');
                        img.setAttribute('src', data.img);

                        var div_txt = document.createElement('div');
                        div_txt.setAttribute('class', 'text');

                        var h5 = document.createElement('h5');
                        h5.textContent = data.name;

                        var p = document.createElement('div');
                        p.setAttribute('class', 'paragrafo');
                        p.setAttribute('name', elMsg);
                        p.setAttribute('id', elMsg);
                        //p.textContent = data.msg;
                        p.textContent = load;

                        var p_dataHora = document.createElement('div');
                        p_dataHora.setAttribute('class', 'dataHora');
                        p_dataHora.textContent =  data.dataHora;

                        div_txt.appendChild(h5);
                        div_txt.appendChild(p);
                        div_txt.appendChild(p_dataHora);

                        div.appendChild(img);
                        div.appendChild(div_txt);

                        area_content.appendChild(div);


                        var div = document.createElement('div');
                        div.setAttribute('class', how);

                        var msg = data.msg;
                        //<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>
                        $('#'+elMsg).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                        loadText_group(msg, chatId);
                        function loadText_group(msg, chatId){
                            $.post("staff/loadText_group.php",
                            {
                                msg, chatId
                            },
                            function (valor) {
                                if(valor.indexOf("Ratchet")<0){
                                    $('#'+elMsg).html(valor);
                                } else {
                                    location.reload();
                                }
                                if(how === 'other'){
                                    //play_men();
                                }

                            });
                        }

                        area_content.appendChild(div);
                    }


                    $(div_area).animate({scrollTop: 100000}, 'slow');


                }
            }
