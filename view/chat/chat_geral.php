
<link rel='stylesheet' type='text/css' href='chat/assets/css/style.css?<? time() ?>'></style>

    <div class="chat-div">
        <section class="chat-content" id="chat-content"></section>
    </div>


    <div class="form-chat">
        <div id="form">
            <div class="input-container">
                <input type="hidden" name="name" id="name" value="<?=ucwords(strtolower($infoUser['nome_completo']))?>" />
                <input type="hidden" name="id_user_remetente" id="id_user_remetente" value="<?=$infoUser['id_user']?>" />

                <input type="hidden" name="img" id="img" value="<?=$infoUser['img_perfil']?>" />
                <input type="text" placeholder="Digite sua mensagem..." name="message" id="message" />
            </div>

        </div>

        <button id="btn1" class="btn-chat"><i class="far fa-paper-plane"></i></button>

    </div>


    <script>


            const conn_geral = conn;
            //console.log(conn);


            conn_geral.onmessage = function (e) {
                data = JSON.parse(e.data);
                console.log(data);
                showMessages('other', e.data);
            };




            //conn.send('Hello World!');
            ///////////////////////////////////////////////
            var form1 = document.getElementById('form1');
            var inp_message = document.getElementById('message');
            var inp_name = document.getElementById('name');
            var userRemetente = document.getElementById('id_user_remetente');
            var inp_img = document.getElementById('img');
            var btn_env = document.getElementById('btn1');
            var area_content = document.getElementById('chat-content');

            $(document).keypress(function(e) {
                if(e.which == 13) btn_env.click();
            });


            btn_env.addEventListener('click', function () {
                sendMessage();
            });



            function sendMessage(){
                if (inp_message.value != '') {
                    var msg = { 'userRemetente': userRemetente.value, 'name': inp_name.value, 'msg': inp_message.value };
                    //, 'img': inp_img.value
                    msg = JSON.stringify(msg);
                    conn_geral.send(msg);
                    showMessages('me', msg);
                    inp_message.value = '';

                }
            }


            function showMessages(how, data) {
                data = JSON.parse(data);

                console.log(data);


                if (how == 'me') {
                    var img_src = img;
                } else if (how == 'other') {
                    var img_src = "chat/assets/imgs/user.png";
                }


                var div = document.createElement('div');
                div.setAttribute('class', how);

                //var img = document.createElement('img');
                //img.setAttribute('src', data.img);

                var div_txt = document.createElement('div');
                div_txt.setAttribute('class', 'text');

                var h5 = document.createElement('h5');
                h5.textContent = data.name;

                var p = document.createElement('p');
                p.textContent = data.msg;

                div_txt.appendChild(h5);
                div_txt.appendChild(p);

                div.appendChild(img);
                div.appendChild(div_txt);

                area_content.appendChild(div);
                $("#chat-content").animate({scrollTop: 100000}, 'slow');
            }

    </script>
