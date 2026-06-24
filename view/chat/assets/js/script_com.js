if( typeof com !== 'undefined'){
    //console.log('com: ' + com);
}
if(typeof conn !== "undefined"){
            conn.onmessage = function (e) {
                data = JSON.parse(e.data);

                //console.log(data);
                if(data.count!=''){
                    var ddCount = data.count;
                    //console.log(ddCount);1
                    setTimeout(function(){
                        $('#dadosLogados').html(ddCount);
                        //console.log(ddCount);
                    }, 500);
                }

                if( typeof loadComList !== 'undefined'){
                    loadComList();
                }
                /*
                if( typeof laodComCount !== 'undefined'){
                    laodComCount();
                }
                */
                //laodComCount();
                if( typeof laodComCount !== 'undefined'){
                    laodComCount();
                }




            };
}

