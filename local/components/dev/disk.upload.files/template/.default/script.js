BX.namespace("BX.Disk");

BX.Disk.FileUploadClass = (function(){
    const FileUploadClass = function(parameters){
        /** File */
        this.file = parameters.file
        this.folderId = parameters.folderId
        this.onSuccess = parameters.onSuccess
        this.onReject = parameters.onReject
        this.onChange = parameters.onChange
        this.errors = []
        this.printLog = parameters.printLog
        this.URL = parameters.URL
        this.xhr = new XMLHttpRequest
        this.sendBytes = 0
        this.chunk = parameters.chunk || 1024*1024
    }


    FileUploadClass.prototype.send = function(url){
        if(url) this.URL = url
        this.xhr.open('POST', this.URL, true)
    }

    FileUploadClass.prototype._UploadPortion = function() {

        // Объект FileReader, в него будем считывать часть загружаемого файла
        var reader = new FileReader();

        // Текущий объект
        var that=this;

        // Позиция с которой будем загружать файл
        var loadfrom = this.sendBytes;

        // Объект Blob, для частичного считывания файла
        var blob=null;

        // Таймаут для функции setTimeout. С помощью этой функции реализована повторная попытка загрузки
        // по таймауту (что не совсем корректно)
        var xhrHttpTimeout= null;

        /*
        * Событие срабатывающее после чтения части файла в FileReader
        * @param evt Событие
        */
        reader.onloadend = function(evt) {
            if (evt.target.readyState == FileReader.DONE) {

                // Создадим объект XMLHttpRequest, установим адрес скрипта для POST
                // и необходимые заголовки HTTP запроса.
                var xhr = new XMLHttpRequest();
                xhr.open('POST', that.URL, true);
                xhr.setRequestHeader("Content-Type", "application/x-binary; charset=x-user-defined");

                // Идентификатор загрузки (чтобы знать на стороне сервера что с чем склеивать)
                xhr.setRequestHeader("Upload-Id", that.options['uploadid']);
                // Позиция начала в файле
                xhr.setRequestHeader("Portion-From", from);
                // Размер порции
                xhr.setRequestHeader("Portion-Size", that.options['portion']);

                // Установим таймаут
                that.xhrHttpTimeout=setTimeout(function() {
                    xhr.abort();
                },that.options['timeout']);

                /*
                * Событие XMLHttpRequest.onProcess. Отрисовка ProgressBar.
                * @param evt Событие
                */
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {

                        // Посчитаем количество закаченного в процентах (с точность до 0.1)
                        var percentComplete = Math.round((loadfrom+evt.loaded) * 1000 / that.filesize);percentComplete/=10;

                        // Посчитаем ширину синей полоски ProgressBar
                        var width=Math.round((loadfrom+evt.loaded) * 300 / that.filesize);

                        // Изменим свойства элементом ProgressBar'а, добавим к нему текст
                        var div1=document.getElementById('cnuploader_progressbar');
                        var div2=document.getElementById('cnuploader_progresscomplete');

                        div1.style.display='block';
                        div2.style.display='block';
                        div2.style.width=width+'px';
                        if (percentComplete<30) {
                            div2.textContent='';
                            div1.textContent=percentComplete+'%';
                        }
                        else {
                            div2.textContent=percentComplete+'%';
                            div1.textContent='';
                        }
                    }

                }, false);



                /*
                * Событие XMLHttpRequest.onLoad. Окончание загрузки порции.
                * @param evt Событие
                */
                xhr.addEventListener("load", function(evt) {

                    // Очистим таймаут
                    clearTimeout(that.xhrHttpTimeout);

                    // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                    if (evt.target.status!=200) {
                        alert(evt.target.responseText);
                        return;
                    }

                    // Добавим к текущей позиции размер порции.
                    that.position+=that.options['portion'];

                    // Закачаем следующую порцию, если файл еще не кончился.
                    if (that.filesize>that.position) {
                        that.UploadPortion(that.position);
                    }
                    else {
                        // Если все порции загружены, сообщим об этом серверу. XMLHttpRequest, метод GET,
                        // PHP скрипт тот-же.
                        var gxhr = new XMLHttpRequest();
                        gxhr.open('GET', that.options['uploadscript']+'?action=done', true);

                        // Установим идентификатор загруки.
                        gxhr.setRequestHeader("Upload-Id", that.options['uploadid']);

                        /*
                        * Событие XMLHttpRequest.onLoad. Окончание загрузки сообщения об окончании загрузки файла :).
                        * @param evt Событие
                        */
                        gxhr.addEventListener("load", function(evt) {

                            // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                            if (evt.target.status!=200) {
                                alert(evt.target.responseText.toString());
                                return;
                            }
                                // Если все нормально, то отправим пользователя дальше. Там может быть сообщение
                            // об успешной загрузке или следующий шаг формы с дополнительным полями.
                            else window.parent.location=that.options['redirect_success'];
                        }, false);

                        // Отправим HTTP GET запрос
                        gxhr.sendAsBinary('');
                    }
                }, false);

                /*
                * Событие XMLHttpRequest.onError. Ошибка при загрузке
                * @param evt Событие
                */
                xhr.addEventListener("error", function(evt) {

                    // Очистим таймаут
                    clearTimeout(that.xhrHttpTimeout);

                    // Сообщим серверу об ошибке во время загруке, сервер сможет удалить уже загруженные части.
                    // XMLHttpRequest, метод GET,  PHP скрипт тот-же.
                    var gxhr = new XMLHttpRequest();

                    gxhr.open('GET', that.options['uploadscript']+'?action=abort', true);

                    // Установим идентификатор загруки.
                    gxhr.setRequestHeader("Upload-Id", that.options['uploadid']);

                    /*
                    * Событие XMLHttpRequest.onLoad. Окончание загрузки сообщения об ошибке загрузки :).
                    * @param evt Событие
                    */
                    gxhr.addEventListener("load", function(evt) {

                        // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                        if (evt.target.status!=200) {
                            alert(evt.target.responseText);
                            return;
                        }
                    }, false);

                    // Отправим HTTP GET запрос
                    gxhr.sendAsBinary('');

                    // Отобразим сообщение об ошибке
                    if (that.options['message_error']==undefined) alert("There was an error attempting to upload the file."); else alert(that.options['message_error']);
                }, false);

                /*
                * Событие XMLHttpRequest.onAbort. Если по какой-то причине передача прервана, повторим попытку.
                * @param evt Событие
                */
                xhr.addEventListener("abort", function(evt) {
                    clearTimeout(that.xhrHttpTimeout);
                    that.UploadPortion(that.position);
                }, false);

                // Отправим порцию методом POST
                xhr.sendAsBinary(evt.target.result);
            }
        };

        that.blob=null;

        // Считаем порцию в объект Blob. Три условия для трех возможных определений Blob.[.*]slice().
        if (this.file.slice) that.blob=this.file.slice(from,from+that.options['portion']);
        else {
            if (this.file.webkitSlice) that.blob=this.file.webkitSlice(from,from+that.options['portion']);
            else {
                if (this.file.mozSlice) that.blob=this.file.mozSlice(from,from+that.options['portion']);
            }
        }

        // Считаем Blob (часть файла) в FileReader
        reader.readAsBinaryString(that.blob);
    }


    FileUploadClass.prototype.sendAsBinary = function(datastr){
        function byteValue(x) {
            return x.charCodeAt(0) & 0xff;
        }
        var ords = Array.prototype.map.call(datastr, byteValue);
        var ui8a = new Uint8Array(ords);
        XML.send(ui8a.buffer);
    }


    FileUploadClass.prototype.errorHandle = function(e){
        if(this.printLog) console.error(e)
        this.errors.push(e)
    }

    return FileUploadClass
})()