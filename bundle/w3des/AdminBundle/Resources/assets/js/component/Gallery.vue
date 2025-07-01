<template>
    <div>
        <input type="file" multiple="true" ref="file" @change="handleFileChange" style="display: none;" />
        <slot></slot>
        <keep-alive>
            <draggable v-model="list" direction="horizontal" class="gallery-drag" handle=".ph">
                <div v-for="m in list" :key="m.id" >
                    <component :is="m.component" @remove="onRemove(m)">
                        
                    </component>
                </div> 
            </draggable>
        </keep-alive>
        <div class="clear-fix"></div>
    </div>
</template>
<script>
import Vue from 'vue'
import draggable from 'vuedraggable'
export default {
    props: ['thmb', 'upload', 'max', 'dir', 'initial', 'item'],
    components: {draggable},
    data() {
        let list = []
        this.initial.forEach(x => {
            x.component = Vue.extend({
                template : x.template
            })
            list.push(x)
        })
        return {
            counter : this.max + 1,
            workLoop : [],
            uploadPercentage: 0,
            list: list
        }
    },
    mounted() {
        this.$el.querySelector('.upload-button').addEventListener('click', this.onUploadButton)
    },
    methods: {
        onUploadButton(e)
        {
            e.stopPropagation()
            e.preventDefault()
            this.$refs.file.click()
        },
        handleFileChange()
        {
            if (this.$refs.file.files.length == 0) {
                return
            } 
            let i =0, data = new FormData()
            data.append('dir', this.dir)
            Array.from(this.$refs.file.files).forEach(f => {
                data.append('files[' + i++ + ']', f)
            })
            this.$http.post(this.upload, data, {
              headers: {
                  'Content-Type': 'multipart/form-data'
              },
              onUploadProgress: function( progressEvent ) {
                this.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ));
              }.bind(this)
            }).then(resp => {
                Array.from(resp.data.files).forEach(f => {
                
                    let c = this.counter++
                    
                    this.list.push({
                        id  : c,
                        name: name,
                        component : Vue.extend({
                            template : this.item.replaceAll(/__name__/g, c).replaceAll(/__thmb__/g, f.thmb).replaceAll(/__path__/g, f.path)
                        })
                    })
                
                })
            })
            
        },
        onRemove(row) {
            this.list = this.list.filter(a => a != row)
        }
        
    }
}
</script>