<template>
    <div>
        <confirm-link v-if="allowRemove" @confirm="onRemove"><span class="button delete-button" v-tooltip="'Usuń'"><i class="icon-delete1"></i></span></confirm-link>
        <h4 class="module-title">
            {{ label }}
        </h4>
        <slot></slot>
    </div>
</template>
<script>

export default {
    props: {
        label: {
            type: String,
            default: null
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
            this.$parent.$emit('remove')
        }
    },
    created(){
    }
}
</script>
<style lang="sass">
.node-module {
    position: relative;
    .delete-button {
        border-radius: 0px;
        position: absolute;
        top: -15px;
        right: -15px;
        padding: 5px 15px;
        font-size: 18px;
        i {
            margin: 0px;
            vertical-align: middle;
        }
    }
    padding: 10px;
    background: white;
    
    >h3 {
        cursor: move;
    }
}
</style>
