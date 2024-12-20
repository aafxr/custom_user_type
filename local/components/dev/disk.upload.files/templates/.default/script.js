BX.namespace("BX.Disk");

BX.Disk.FileUploadClass = (function(){
    const FileUploadClass = function(parameters){
        const {file, URL, folderId} = parameters
        if(!file || !URL || !folderId){
            throw new Error('необходимые параметры: file, URL, folderId')
        }
        /** File */
        this.file = parameters.file
        this.onSuccess = parameters.onSuccess
        this.onReject = parameters.onReject
        this.onChange = parameters.onChange
        this.errors = []
        this.printLog = parameters.printLog
        this.folderId = parameters.folderId || 0
        this.URL = parameters.URL + '?folderId=' + this.folderId + '&file=' + this.file.name
        this.sendBytes = 0
        this.progress = 0
        this.chunk = parameters.chunk || 1024*1024
        this.timeout = parameters.timeout || 10000
        this.timeoutId = null
        this.uploadId = FileUploadClass.makeUID(32)
        this.blob = null
        this.status = FileUploadClass.STATUS_INIT
    }

    FileUploadClass.STATUS_INIT = 0
    FileUploadClass.STATUS_DONE = 1
    FileUploadClass.STATUS_LOADING = 2
    FileUploadClass.STATUS_REJECTED = 3
    FileUploadClass.STATUS_ABORTED = 4

    FileUploadClass.makeUID = function (length) {
        let result = '';
        const characters = 'abcdef0123456789';
        const charactersLength = characters.length;
        let counter = 0;
        while (counter < length) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
            counter += 1;
        }
        return result;
    }


    FileUploadClass.prototype.send = function(url){
        if(url) {
            let [path, params] = url.split('?')
            if(!params) params = ''
            const sp = new URLSearchParams(params)
            sp.set('folderId', this.folderId)
            sp.set('file', this.file.name)
            this.URL = path + '?' + sp.toString()
        }
        this.status = FileUploadClass.STATUS_LOADING
        this._UploadPortion()
    }

    FileUploadClass.prototype._UploadPortion = function() {
        var reader = new FileReader();
        var that=this;
        var loadfrom = this.sendBytes;
        that.timeoutId = null;

        /*
        * Событие срабатывающее после чтения части файла в FileReader
        * @param evt Событие
        */
        reader.onloadend = function(evt) {
            if (evt.target.readyState == FileReader.DONE) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', that.URL, true);
                xhr.setRequestHeader("Content-Type", "application/x-binary; charset=x-user-defined");
                // Идентификатор загрузки (чтобы знать на стороне сервера что с чем склеивать)
                xhr.setRequestHeader("Upload-Id", that.uploadId);
                // Позиция начала в файле
                xhr.setRequestHeader("Portion-From", loadfrom);
                // Размер порции
                xhr.setRequestHeader("Portion-Size", that.chunk);

                // Установим таймаут
                that.timeoutId = setTimeout(function() {
                    xhr.abort();
                    that._errorHandle(new Error('timeout limit'))
                    that.status = FileUploadClass.STATUS_ABORTED
                },that.timeout);

                /*
                * Событие XMLHttpRequest.onProcess. Отрисовка ProgressBar.
                * @param evt Событие
                */
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        that.progress += evt.loaded
                        that._handleChange()
                    }
                }, false);



                /*
                * Событие XMLHttpRequest.onLoad. Окончание загрузки порции.
                * @param evt Событие
                */
                xhr.addEventListener("load", function(evt) {
                    // Очистим таймаут
                    clearTimeout(that.timeoutId);

                    // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                    if (evt.target.status!=200) {
                        console.log(evt)
                        that._errorHandle(new Error(`server response code ${evt.target.status}`));
                        return;
                    }

                    // Добавим к текущей позиции размер порции.
                    that.sendBytes += that.chunk;

                    // Закачаем следующую порцию, если файл еще не кончился.
                    if (that.file.size > that.sendBytes) {
                        that._UploadPortion(that.sendBytes);
                    }
                    else {
                        // Если все порции загружены, сообщим об этом серверу. XMLHttpRequest, метод GET,
                        // PHP скрипт тот-же.
                        var gxhr = new XMLHttpRequest();
                        gxhr.open('POST', that.URL + '&action=done', true);
                        // Установим идентификатор загруки.
                        gxhr.setRequestHeader("Upload-Id", that.uploadId);

                        /*
                        * Событие XMLHttpRequest.onLoad. Окончание загрузки сообщения об окончании загрузки файла :).
                        * @param evt Событие
                        */
                        gxhr.addEventListener("load", function(evt) {
                            // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                            if (evt.target.status!=200) {
                                console.log(evt)
                                that._errorHandle(new Error(`server response code ${evt.target.status}`));
                                return;
                            }
                            // Если все нормально, то отправим пользователя дальше. Там может быть сообщение
                            // об успешной загрузке или следующий шаг формы с дополнительным полями.
                            that._handleSuccess()
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
                    clearTimeout(that.timeoutId);

                    // Сообщим серверу об ошибке во время загруке, сервер сможет удалить уже загруженные части.
                    // XMLHttpRequest, метод GET,  PHP скрипт тот-же.
                    var gxhr = new XMLHttpRequest();

                    gxhr.open('POST', that.URL + '?action=abort', true);

                    // Установим идентификатор загруки.
                    gxhr.setRequestHeader("Upload-Id", that.uploadId);

                    /*
                    * Событие XMLHttpRequest.onLoad. Окончание загрузки сообщения об ошибке загрузки :).
                    * @param evt Событие
                    */
                    gxhr.addEventListener("load", function(evt) {

                        // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                        if (evt.target.status!=200) {
                            console.log(evt)
                            that._errorHandle(new Error(`server response code ${evt.target.status}`));
                            return;
                        }
                    }, false);

                    // Отправим HTTP GET запрос
                    gxhr.sendAsBinary('');

                    // Отобразим сообщение об ошибке
                    that._errorHandle(evt)
                }, false);

                /*
                * Событие XMLHttpRequest.onAbort. Если по какой-то причине передача прервана, повторим попытку.
                * @param evt Событие
                */
                xhr.addEventListener("abort", function(evt) {
                    clearTimeout(that.timeoutId);
                    that._UploadPortion(that.sendBytes);
                }, false);

                // Отправим порцию методом POST
                xhr.sendAsBinary(evt.target.result);
            }
        };

        that.blob=null;

        // Считаем порцию в объект Blob. Три условия для трех возможных определений Blob.[.*]slice().
        if (this.file.slice) that.blob=this.file.slice(this.sendBytes ,this.sendBytes + that.chunk);
        else {
            if (this.file.webkitSlice) that.blob=this.file.webkitSlice(this.sendBytes ,this.sendBytes + that.chunk);
            else {
                if (this.file.mozSlice) that.blob=this.file.mozSlice(this.sendBytes ,this.sendBytes + that.chunk);
            }
        }

        // Считаем Blob (часть файла) в FileReader
        reader.readAsBinaryString(that.blob);
    }


    XMLHttpRequest.prototype.sendAsBinary = function(datastr){
        function byteValue(x) {
            return x.charCodeAt(0) & 0xff;
        }
        var ords = Array.prototype.map.call(datastr, byteValue);
        var ui8a = new Uint8Array(ords);
        this.send(ui8a.buffer);
    }


    FileUploadClass.prototype._errorHandle = function(e){
        if(this.printLog) console.error(e)
        this.errors.push(e)
        this.status = FileUploadClass.STATUS_REJECTED
        if (this.onReject) this.onReject(this)
    }

    FileUploadClass.prototype._handleSuccess = function(){
        this.status = FileUploadClass.STATUS_DONE
        if(this.onSuccess) this.onSuccess(this)
    }

    FileUploadClass.prototype._handleChange = function(){
        if(this.onChange) this.onChange(this)
    }

    FileUploadClass.prototype.isCompleted = function (){
        return this.status === FileUploadClass.STATUS_DONE
    }

    FileUploadClass.prototype.isRejected = function (){
        return this.status === FileUploadClass.STATUS_REJECTED
    }

    FileUploadClass.prototype.isAborted = function (){
        return this.status === FileUploadClass.STATUS_ABORTED
    }

    FileUploadClass.prototype.isLoading = function (){
        return this.status === FileUploadClass.STATUS_LOADING
    }


    /**
     * количество закаченного в процентах (с точность до 0.1)
     * @returns {number}
     */
    FileUploadClass.prototype.getProgress = function(){
        return Math.floor((this.progress / this.file.size) * 1000) / 10
    }

    return FileUploadClass
})()