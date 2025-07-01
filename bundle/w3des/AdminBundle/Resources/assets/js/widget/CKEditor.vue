<template>
    <div>
        <basic-editor ref="ck" @namespaceloaded="onNamespaceLoaded" tag-name="div" v-model="html" :id="id" :config="editorConfig" @ready="ckeditorReady"></basic-editor>
        <input type="hidden" :value="html" :name="name" />
        <modal v-if="fileBrowser" @close="fileBrowser = false" container-class="elfinder-modal">
            <template v-slot>
                <iframe :src="fileBrowser">
                
                </iframe>
            </template>
        </modal>
    </div>
</template>
<script>

import BasicEditor from 'ckeditor4-vue'
import Modal from '~/js/widget/Modal'
export default {
    components: {'basic-editor' : BasicEditor.component, Modal},
    props: ['config', 'name', 'id', 'value'],
    data() {
        return {
            html : this.value,
            editorConfig: {
                ...this.config,
                ...{extraPlugins: 'youtube,oembed'}
            },
            fileBrowser : false
        }
    },
    methods: {
        ckeditorReady(editor)
        {
            this.instance = editor
            let me = this
            /*editor.commands.get('ckfinder').execute = () => { 
                this.fileBrowser = editor.config._config.ckfinder.uploadUrl
                window.currentEditor = this
            }*/
        },
        uploadedFile(file, mime)
        {
            if (file && mime.match(/^image\//i)) {
                this.insertImages([file]);
                this.fileBrowser = false;
            } else if(file) {
                this.instance.execute('link', file);
                this.fileBrowser = false;
            }
            
        },
        insertImages(urls) {
            const imgCmd = this.instance.commands.get('imageUpload'),
                i18 = this.instance.locale.t,
                ntf = this.instance.plugins.get('Notification')
            ;
            if (!imgCmd.isEnabled) {
                ntf.showWarning(i18('Could not insert image at the current position.'), {
                    title: i18('Inserting image failed'),
                    namespace: 'ckfinder'
                });
                return;
            }
            this.instance.execute('imageInsert', { source: urls });
        },
        onNamespaceLoaded( CKEDITOR ) {
            // Add external `placeholder` plugin which will be available for each
            // editor instance on the page.
            CKEDITOR.plugins.addExternal( 'youtube', base_url + '/bundles/w3desadmin/js/ckeditor/youtube/', 'plugin.js' );
            CKEDITOR.plugins.addExternal( 'oembed', base_url + '/bundles/w3desadmin/js/ckeditor/oembed/', 'plugin.js' );
        }
    }
}
</script>
<style lang="scss">
.elfinder-modal {
    width: 80vw;
    iframe {
        width: 100%;
        border: 0px solid;
        height: 80vh;
    }
}
</style>