//console.log('script_com_group');

function stSafeChatHtml(html) {
    if (typeof window.stSafeChatHtml === 'function' && window.stSafeChatHtml !== stSafeChatHtml) {
        return window.stSafeChatHtml(html);
    }
    var tmp = document.createElement('div');
    tmp.innerHTML = String(html || '');
    tmp.querySelectorAll('script,iframe,object,embed,link,meta,style').forEach(function (n) {
        n.remove();
    });
    tmp.querySelectorAll('*').forEach(function (el) {
        Array.from(el.attributes).forEach(function (attr) {
            var name = String(attr.name || '');
            var val = String(attr.value || '');
            if (/^on/i.test(name) || ((name === 'href' || name === 'src') && /^\s*javascript:/i.test(val))) {
                el.removeAttribute(name);
            }
        });
    });
    return tmp.innerHTML;
}

if( typeof com !== 'undefined'){
    //console.log('com: ' + com);
}


            function saveMsgCom(msg, rem, com, nome, img, tk){

                if (msg != '') {
                    var feed = '#feed_' + com;

                    //$(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                    $.post("staff/save_msg_com.php",
                    {
                        msg, rem, com, nome, img, tk
                    },
                    function (valor) {
                        $(feed).html(valor);
                    });
                }
            }



            function sendMessageCom(msg, rem, com, nome, img, tk){
                if (msg != '') {
                    //console.log('stage 3');
                    //console.log('Com ' + com);
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
                        'idCom': com,
                        'name': nome,
                        'msg': msg,
                        'img': img,
                        'dataHora': str_data
                    };
                    //, 'img': inp_img.value
                    msg = JSON.stringify(msg);
                    conn.send(msg);
                    showMessagesCom('me', msg, com);
                    $('#message_com_' + tk).val('');

                }
            }


            function showMessagesCom(how, data, com) {
                var chatId=com;
                //console.log(com);
                //console.log('stage 4');

                if($('#chat-content_com_' + chatId).length){
                    //console.log(chatId);
                    var div_area = '#chat-content_com_' + chatId;
                    var chat_content_txt = 'chat-content_com_' + chatId;
                    var area_content = document.getElementById(chat_content_txt);

                    var elMsg = nome_div();

                    //console.log(elMsg);

                    data = JSON.parse(data);
                    //console.log(data);
                    //console.log(chatId);

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
                        loadTextCom(msg, chatId);
                        function loadTextCom(msg, chatId){
                            $.post("staff/loadText_com.php",
                            {
                                msg, chatId
                            },
                            function (valor) {
                                if(valor.indexOf("Ratchet")<0){
                                    $('#'+elMsg).html(stSafeChatHtml(valor));
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
                    if( typeof loadComList !== 'undefined'){
                        loadComList(indice, com);
                    }



                }
            }

            function sendFileCom(comId){

                var msg = {
                        "flagFile": "true",
                        "comId" : comId
                        };
                msg = JSON.stringify(msg);
                //console.log(msg);
                conn.send(msg);
                if ( typeof loadFileDiv === 'function' ) {
                    //loadFileDiv(chatId);
                }
            }

            function laodComCount(not=true){
                $.post("staff/load_com_count.php",
                {not},
                function (valor) {
                    $('#countCom').html(valor);
                });
            }


