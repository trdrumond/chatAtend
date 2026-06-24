const Notifyer = {
    async init() {
        const permission = await Notification.requestPermission()
        if ( permission !== "granted") {
            Swal.fire('Você precisa permitir as notificações do Solvetask');
            //throw new Error('Permissão negada')
        }
    },
    notify({ title, body, icon }) {
        new Notification(title, {
            body,
            icon
        })
    }
}


const App = {
    async mostraNotificacao(body) {
        try {
            //await Notifyer.init()
            Notifyer.notify({
                title: 'Solvetask',
                body: body,
                icon: 'img/chat.png'
            })
        } catch (err) {
            console.log(err.message)
        }
    }
}


//App.mostraNotificacao("Solvetask", "Atenção ao Chat foi solicitada!")
