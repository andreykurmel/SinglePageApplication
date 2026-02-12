<template>
    <div>
        <div class="flex flex--center-v no-wrap form-group">
            <label>Select an API:&nbsp;</label>
            <select class="form-control w w-long" v-model="ocr_api_key" @change="checkKey()">
                <option v-for="ocr in $root.user._extracttable_api_keys" :value="ocr.key">{{ ocr.name }}</option>
            </select>
        </div>
        <div v-if="!ocr_file" class="form-group" :style="{opacity: !ocr_api_key ? 0.5 : 1, position: 'relative'}">
            <div v-if="!ocr_api_key" class="notavail absolute-frame"></div>
            <label>Upload an image or PDF file:</label>
            <file-uploader-block
                :clear_before="true"
                :table_id="'temp'"
                :field_id="'ocr_source'"
                :row_id="''"
                class="full-width"
                @uploaded-file="ocrFileSaved"
            ></file-uploader-block>
        </div>
        <div v-if="ocr_file" class="form-group">
            <label>Uploaded file:</label>
            <div class="flex flex--center-v">
                <input class="form-control flex__elem-remain" :value="justName(ocr_file)">
                <button class="btn btn-danger btn-cross" @click="ocrFileRemoved()">&times;</button>
            </div>
        </div>
        <div>
            <button class="btn btn-success"
                    :disabled="!ocr_file || ocr_processing"
                    :style="$root.themeButtonStyle"
                    @click="startOcrParsing()"
            >
                <img v-if="ocr_processing" height="25" src="/assets/img/Loading_icon.gif">
                <span v-else="" >Run OCR</span>
            </button>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import {ImportHelper} from "../../classes/helpers/ImportHelper";

    import FileUploaderBlock from "./FileUploaderBlock";

    export default {
        name: 'OcrImportBlock',
        mixins: [
        ],
        components: {
            FileUploaderBlock,
        },
        data() {
            return {
                ocr_uuid: this.$root.user.id ? ('user_ocr_'+this.$root.user.id) : uuidv4(),
                ocr_file: null,
                ocr_api_key: null,
                ocr_processing: false,
                preparsed_datas: null,
            }
        },
        props: {
            just_one: Boolean,
            import_action: String,
            some_presets: Object,
        },
        methods: {
            justName(filepath) {
                return _.last( String(filepath).split('/') );
            },
            checkKey() {
                this.ocr_file = null;
                this.preparsed_datas = null;
                if (!this.import_action) {
                    Swal('Info','Please select an option.');
                    return;
                }
                axios.post('/ajax/import/ocr/check-key', {
                    key: this.ocr_api_key,
                }).then(({ data }) => {
                    if (data.usage && data.usage.credits <= data.usage.used) {
                        Swal('Info','All credits are used for this Key.');
                        this.ocr_api_key = null;
                    }
                    this.emitProps();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                    this.ocr_api_key = null;
                });
            },

            ocrFileSaved(unused, filename) {
                this.preparsed_datas = null;
                this.ocr_file = 'ocr_source/'+filename;
                this.emitProps();
            },
            ocrFileRemoved() {
                this.preparsed_datas = null;
                this.ocr_file = null;
                this.emitProps();
            },

            startOcrParsing() {
                if (!this.import_action) {
                    Swal('Info','Please select an option.');
                    return;
                }
                this.ocr_processing = true;
                axios.post('/ajax/import/ocr/parse-image', {
                    key: this.ocr_api_key,
                    file_name: this.ocr_file,
                }).then(({ data }) => {
                    this.preparsed_datas = data;
                    this.emitProps();
                    this.fillResultsAndEmit(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.ocr_processing = false;
                });
            },

            fillResultsAndEmit(data) {
                let results = [];
                _.each(data, (folder_file, i) => {
                    let item = ImportHelper.importTemplate();
                    item.name = 'Table ' + (i+1);
                    item.source_file = folder_file;
                    results.push(item);
                });
                this.$emit('ocr-image-parsed', this.just_one ? _.first(results) : results);
            },
            emitProps() {
                this.$emit('props-changed', {
                    ocr_api_key: this.ocr_api_key,
                    ocr_file: this.ocr_file,
                    preparsed_datas: this.preparsed_datas,
                });
            },
        },
        mounted() {
            if (this.some_presets) {
                if (this.some_presets.ocr_api_key) {
                    this.ocr_api_key = this.some_presets.ocr_api_key;
                }
                if (this.some_presets.ocr_file) {
                    this.ocr_file = this.some_presets.ocr_file;
                }
                if (this.some_presets.preparsed_datas) {
                    this.preparsed_datas = this.some_presets.preparsed_datas;
                    this.fillResultsAndEmit(this.preparsed_datas);
                }
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    label {
        margin: 0;
    }
    .notavail {
        cursor: not-allowed !important;
        z-index: 100;
    }
    .btn-cross {
        font-size: 2rem;
        line-height: 2rem;
        padding: 7px 10px;
        margin-left: 10px;
    }
</style>