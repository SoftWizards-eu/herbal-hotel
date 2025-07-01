<template>
    <div class="gallery-item">
        <confirm-link v-if="allowRemove" @confirm="onRemove"><span class="button delete-button" v-tooltip="'Usuń'"><i class="icon-delete1"></i></span></confirm-link>
        <slot></slot>
    </div>
</template>
<script>

export default {
    props: {
        label: {
            type: String,
            default: null,
        },
        allowRemove: {
            type: Boolean,
            default: true
        },
        replacement: {
            type: String
        }
    },
    data() {
        return {
            
        }
    },
    methods: {
        onRemove() {
            console.log(this.$parent)
            this.$parent.$emit('remove')
        }
    },
    created(){
    }
}
</script>
<style lang="sass">
.node-module {
    
    padding: 10px;
    background: white;
  
    >h3 {
        cursor: move;
    }
}
</style>
