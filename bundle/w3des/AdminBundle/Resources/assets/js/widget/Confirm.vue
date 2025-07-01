<template>
    <modal container-class="confirm-modal" @close="$emit('close')">
        <template slot="header">
            <template v-if="title">{{title}}</template>
            <template v-else>Czy na pewno chcesz to zrobić?</template>
        </template>
        <template slot="body">
            <slot></slot>
        </template>
        <template slot="footer">
            <span class="button button-red" @click="$emit('confirm')">TAK</span>
            <span class="button" @click="$emit('close')">NIE</span>
        </template>
    </modal>
</template>
<script> 
    export default {
        props: {
            title: {
                default: null
            }
        }
    }
</script>
<style lang="scss">
.confirm-modal {
    width: 400px;
    .modal-footer {
        text-align: right;
    }
}
</style>