
export class FileHelper {

    /**
     *
     * @param format
     * @returns {Array|*}
     */
    static _acceptFormats(format) {
        let arr = format ? String(format).split('-') : [];
        arr = String(_.first(arr) || '').split(',');
        return _.filter(arr);
    }

    /**
     *
     * @param format
     * @returns {number}
     */
    static _acceptSizeMb(format) {
        let arr = format ? String(format).split('-') : [];
        return parseFloat(_.last(arr) || 0);
    }

    /**
     *
     * @param format
     * @returns {number}
     */
    static _acceptSize(format) {
        return this._acceptSizeMb(format) * 1024 * 1024;
    }

    /**
     *
     * @param file
     * @param format
     * @returns {boolean}
     */
    static checkFile(file, format) {
        let fileext = file ? _.last(String(file.name).split('.')) : '';
        let filesize = file ? Number(file.size) : 0;
        if (fileext && this._acceptFormats(format).length && this._acceptFormats(format).indexOf(fileext) === -1) {
            Swal('', 'The file format '+fileext+' is not allowed for uploading!', 'info');
            return false;
        }
        if (filesize && this._acceptSize(format) && filesize > this._acceptSize(format)) {
            Swal('', 'The size of the file exceeds the set limit '+this._acceptSizeMb(format)+'Mb!', 'info');
            return false;
        }
        return true;
    }
}